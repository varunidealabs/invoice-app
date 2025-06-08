<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChatGptController extends Controller
{
    public function index()
    {
        return view('chatgpt.index');
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $conversationHistory = $request->input('history', []);
        
        try {
            // Get user context for intelligent responses
            $userContext = $this->getUserContext();
            
            $systemPrompt = $this->buildSystemPrompt($userContext);
            
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];

            // Add conversation history (keep last 10 messages for context)
            foreach (array_slice($conversationHistory, -10) as $msg) {
                $messages[] = $msg;
            }

            // Add current user message
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];
            
            // UPDATED: Use tools instead of functions for newer API
            $tools = $this->getAvailableTools();
            
            $requestBody = [
                'model' => config('chatgpt.model'),
                'messages' => $messages,
                'max_tokens' => 1500,
                'temperature' => 0.3,
            ];

            // Add tools if available
            if (!empty($tools)) {
                $requestBody['tools'] = $tools;
                $requestBody['tool_choice'] = 'auto';
            }

            \Log::info('ChatGPT Request:', $requestBody);

            // FIXED: Add SSL certificate handling for Azure OpenAI
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('chatgpt.api_key'),
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => false, // Disable SSL verification (temporary fix)
                'timeout' => 30,
                'connect_timeout' => 10,
                // Alternative: specify certificate bundle path
                // 'verify' => storage_path('certificates/cacert.pem'),
            ])
            ->post(config('chatgpt.endpoint'), $requestBody);

            if (!$response->successful()) {
                \Log::error('ChatGPT API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['reply' => 'Sorry, the AI service is currently unavailable. Please try again.']);
            }

            $data = $response->json();
            \Log::info('ChatGPT Response:', $data);
            
            $message = $data['choices'][0]['message'] ?? null;
            
            if (!$message) {
                return response()->json(['reply' => 'No response received from AI service.']);
            }

            // Check for tool calls (new format)
            if (isset($message['tool_calls']) && !empty($message['tool_calls'])) {
                return $this->handleToolCall($message['tool_calls'][0], $userContext);
            }
            // Check for function calls (legacy format)
            elseif (isset($message['function_call'])) {
                return $this->handleFunctionCall($message['function_call'], $userContext);
            }
            else {
                // Regular text response
                $reply = $message['content'] ?? 'No response received';
                return response()->json(['reply' => $reply]);
            }

        } catch (\Exception $e) {
            \Log::error('ChatGPT Controller Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'reply' => 'Sorry, I encountered an error: ' . $e->getMessage() . '. Please try again.'
            ]);
        }
    }

    private function getUserContext()
    {
        try {
            $user = auth()->user();
            $company = $user->company;
            
            if (!$company) {
                throw new \Exception('No company found for user');
            }
            
            // Get recent clients
            $recentClients = $company->clients()
                ->select('id', 'name', 'email', 'contact_person')
                ->latest()
                ->take(20)
                ->get();
            
            // Get recent invoices for pattern recognition
            $recentInvoices = $company->allDocuments()
                ->with('client:id,name')
                ->select('id', 'invoice_number', 'client_id', 'is_quotation', 'total', 'payment_terms', 'tax_rate')
                ->latest()
                ->take(10)
                ->get();
            
            // Get common payment terms and tax rates
            $commonPaymentTerms = $company->allDocuments()
                ->select('payment_terms')
                ->groupBy('payment_terms')
                ->orderByRaw('COUNT(*) DESC')
                ->take(5)
                ->pluck('payment_terms');
                
            $commonTaxRate = $company->allDocuments()
                ->select('tax_rate')
                ->groupBy('tax_rate')
                ->orderByRaw('COUNT(*) DESC')
                ->value('tax_rate') ?? 0;

            return [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->company_name,
                    'currency' => $company->currency,
                    'currency_symbol' => $company->currency_symbol,
                    'default_payment_terms' => $company->default_payment_terms,
                    'country' => $company->country,
                ],
                'clients' => $recentClients,
                'recent_invoices' => $recentInvoices,
                'common_payment_terms' => $commonPaymentTerms,
                'common_tax_rate' => $commonTaxRate,
                'current_date' => now()->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting user context: ' . $e->getMessage());
            throw $e;
        }
    }

    private function buildSystemPrompt($context)
    {
        $clientsList = $context['clients']->map(function($client) {
            return "- {$client->name} (ID: {$client->id})";
        })->implode("\n");

        $currencySymbol = $context['company']['currency_symbol'];
        $recentInvoicesList = $context['recent_invoices']->map(function($invoice) use ($currencySymbol) {
            $type = $invoice->is_quotation ? 'Quotation' : 'Invoice';
            return "- {$type} {$invoice->invoice_number} for {$invoice->client->name} - {$currencySymbol}{$invoice->total}";
        })->implode("\n");

        return "You are an intelligent invoice assistant for {$context['company']['name']}. 

COMPANY CONTEXT:
- Company: {$context['company']['name']}
- Currency: {$context['company']['currency']} ({$context['company']['currency_symbol']})
- Default Payment Terms: {$context['company']['default_payment_terms']}
- Common Tax Rate: {$context['common_tax_rate']}%
- Current Date: {$context['current_date']}

AVAILABLE CLIENTS:
{$clientsList}

RECENT INVOICES/QUOTATIONS:
{$recentInvoicesList}

INTELLIGENCE RULES:
1. When user mentions a client name, automatically find the client_id from the list above
2. Use smart defaults: tax rate ({$context['common_tax_rate']}%), payment terms ({$context['company']['default_payment_terms']})
3. Auto-set dates: issue_date (today), due_date (30 days from today for invoices)
4. For quotations: set valid_until (30 days from today)
5. If client doesn't exist, ask if they want to create a new one
6. Be conversational but efficient - don't ask for optional details unless specifically needed
7. Use function calling to create invoices/quotations when you have enough information

REQUIRED MINIMUM INFO:
- Client (name or ID)
- Document type (invoice or quotation)
- At least one item with description, quantity, and price

CONVERSATION FLOW:
1. Understand what user wants to create
2. Identify client (or ask to create new one)
3. Get item details
4. Use smart defaults for everything else
5. Call create_invoice_quotation function immediately when you have minimum required info

Be helpful, smart, and efficient. Don't ask for optional fields unless the user specifically wants to customize them.";
    }

    private function getAvailableTools()
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_invoice_quotation',
                    'description' => 'Create an invoice or quotation with intelligent defaults',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'client_identifier' => [
                                'type' => 'string',
                                'description' => 'Client name or ID from the context'
                            ],
                            'document_type' => [
                                'type' => 'string',
                                'enum' => ['invoice', 'quotation'],
                                'description' => 'Type of document to create'
                            ],
                            'items' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'description' => ['type' => 'string'],
                                        'quantity' => ['type' => 'number'],
                                        'unit_price' => ['type' => 'number']
                                    ],
                                    'required' => ['description', 'quantity', 'unit_price']
                                ]
                            ],
                            'custom_settings' => [
                                'type' => 'object',
                                'properties' => [
                                    'tax_rate' => ['type' => 'number'],
                                    'payment_terms' => ['type' => 'string'],
                                    'due_date' => ['type' => 'string'],
                                    'notes' => ['type' => 'string'],
                                    'terms' => ['type' => 'string']
                                ]
                            ]
                        ],
                        'required' => ['client_identifier', 'document_type', 'items']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_client',
                    'description' => 'Create a new client',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                            'phone' => ['type' => 'string'],
                            'contact_person' => ['type' => 'string']
                        ],
                        'required' => ['name']
                    ]
                ]
            ]
        ];
    }

    // Handle new tool call format
    private function handleToolCall($toolCall, $context)
    {
        $functionName = $toolCall['function']['name'];
        $arguments = json_decode($toolCall['function']['arguments'], true);

        return $this->executeFunction($functionName, $arguments, $context);
    }

    // Handle legacy function call format
    private function handleFunctionCall($functionCall, $context)
    {
        $functionName = $functionCall['name'];
        $arguments = json_decode($functionCall['arguments'], true);

        return $this->executeFunction($functionName, $arguments, $context);
    }

    private function executeFunction($functionName, $arguments, $context)
    {
        try {
            \Log::info('Executing function:', [
                'function' => $functionName,
                'arguments' => $arguments
            ]);

            switch ($functionName) {
                case 'create_invoice_quotation':
                    return $this->createInvoiceQuotation($arguments, $context);
                
                case 'create_client':
                    return $this->createClient($arguments);
                
                default:
                    return response()->json(['reply' => 'Unknown function requested: ' . $functionName]);
            }
        } catch (\Exception $e) {
            \Log::error('Function execution error: ' . $e->getMessage(), [
                'function' => $functionName,
                'arguments' => $arguments,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'reply' => 'Sorry, there was an error processing your request: ' . $e->getMessage()
            ]);
        }
    }

    private function createInvoiceQuotation($arguments, $context)
    {
        try {
            // Find client
            $client = $this->findClient($arguments['client_identifier'], $context);
            
            if (!$client) {
                return response()->json([
                    'reply' => "I couldn't find a client named '{$arguments['client_identifier']}'. Would you like me to create a new client with this name?"
                ]);
            }

            $isQuotation = $arguments['document_type'] === 'quotation';
            $customSettings = $arguments['custom_settings'] ?? [];

            // Apply intelligent defaults
            $invoiceData = [
                'client_id' => $client->id,
                'is_quotation' => $isQuotation,
                'issue_date' => $customSettings['issue_date'] ?? now()->format('Y-m-d'),
                'payment_terms' => $customSettings['payment_terms'] ?? $context['company']['default_payment_terms'],
                'tax_rate' => $customSettings['tax_rate'] ?? $context['common_tax_rate'],
                'notes' => $customSettings['notes'] ?? '',
                'terms' => $customSettings['terms'] ?? '',
                'items' => $arguments['items']
            ];

            // Set appropriate date field
            if ($isQuotation) {
                $invoiceData['valid_until'] = $customSettings['valid_until'] ?? now()->addDays(30)->format('Y-m-d');
            } else {
                $invoiceData['due_date'] = $customSettings['due_date'] ?? now()->addDays(30)->format('Y-m-d');
            }

            // Create the invoice/quotation
            $invoice = DB::transaction(function () use ($invoiceData, $context) {
                $company = auth()->user()->company;
                $isQuotation = $invoiceData['is_quotation'];
                
                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'client_id' => $invoiceData['client_id'],
                    'invoice_number' => $isQuotation ? 
                        $company->getNextQuotationNumber() : 
                        $company->getNextInvoiceNumber(),
                    'is_quotation' => $isQuotation,
                    'issue_date' => $invoiceData['issue_date'],
                    'due_date' => $invoiceData['due_date'] ?? null,
                    'valid_until' => $invoiceData['valid_until'] ?? null,
                    'payment_terms' => $invoiceData['payment_terms'],
                    'notes' => $invoiceData['notes'],
                    'terms' => $invoiceData['terms'],
                    'tax_rate' => $invoiceData['tax_rate'],
                ]);

                foreach ($invoiceData['items'] as $index => $item) {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'sort_order' => $index,
                    ]);
                }

                $invoice->calculateTotals();
                return $invoice->load(['client', 'items', 'company']);
            });

            $documentType = $isQuotation ? 'Quotation' : 'Invoice';
            $currencySymbol = $context['company']['currency_symbol'];

            return response()->json([
                'reply' => "✅ {$documentType} created successfully!\n\n" .
                          "📄 {$documentType} Number: {$invoice->invoice_number}\n" .
                          "👤 Client: {$invoice->client->name}\n" .
                          "💰 Total: {$currencySymbol}" . number_format($invoice->total, 2) . "\n" .
                          "📅 Issue Date: " . $invoice->issue_date->format('M d, Y') . "\n" .
                          ($isQuotation ? 
                              "⏰ Valid Until: " . $invoice->valid_until->format('M d, Y') : 
                              "📆 Due Date: " . $invoice->due_date->format('M d, Y')),
                'invoice_created' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'view_url' => route('invoices.show', $invoice),
                'pdf_url' => route('invoices.pdf', $invoice)
            ]);

        } catch (\Exception $e) {
            \Log::error('Invoice creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function findClient($identifier, $context)
    {
        try {
            // Try to find by ID first (if numeric)
            if (is_numeric($identifier)) {
                $client = $context['clients']->firstWhere('id', $identifier);
                if ($client) {
                    return $client;
                }
            }

            // Search by name (case insensitive, partial match)
            $client = $context['clients']->first(function($client) use ($identifier) {
                return stripos($client->name, $identifier) !== false;
            });

            if ($client) {
                return $client;
            }

            // If not found in recent clients, search all clients
            return auth()->user()->company->clients()
                ->where('name', 'LIKE', "%{$identifier}%")
                ->orWhere('email', 'LIKE', "%{$identifier}%")
                ->first();

        } catch (\Exception $e) {
            \Log::error('Error finding client: ' . $e->getMessage());
            return null;
        }
    }

    private function createClient($arguments)
    {
        try {
            $client = auth()->user()->company->clients()->create([
                'name' => $arguments['name'],
                'email' => $arguments['email'] ?? null,
                'phone' => $arguments['phone'] ?? null,
                'contact_person' => $arguments['contact_person'] ?? null,
                'country' => 'India' // Default
            ]);

            return response()->json([
                'reply' => "✅ New client '{$client->name}' created successfully! You can now create invoices for them.",
                'client_created' => true,
                'client_id' => $client->id,
                'client_name' => $client->name
            ]);
        } catch (\Exception $e) {
            \Log::error('Client creation error: ' . $e->getMessage());
            return response()->json([
                'reply' => "Sorry, I couldn't create the client: " . $e->getMessage()
            ]);
        }
    }

    public function transcribe(Request $request)
    {
        if (!$request->hasFile('audio')) {
            return response()->json(['error' => 'No audio file provided'], 400);
        }

        $audioFile = $request->file('audio');
        
        try {
            // FIXED: Add SSL certificate handling for Whisper API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('chatgpt.api_key'),
            ])
            ->withOptions([
                'verify' => false, // Disable SSL verification (temporary fix)
                'timeout' => 30,
                'connect_timeout' => 10,
            ])
            ->attach('file', $audioFile->getContent(), $audioFile->getClientOriginalName())
            ->post(config('chatgpt.whisper_endpoint'));

            if (!$response->successful()) {
                \Log::error('Whisper API Error: ' . $response->body());
                return response()->json(['error' => 'Transcription service error'], 500);
            }

            $data = $response->json();
            $transcription = $data['text'] ?? 'Transcription failed';

            return response()->json(['transcription' => $transcription]);
        } catch (\Exception $e) {
            \Log::error('Transcription failed: ' . $e->getMessage());
            return response()->json(['error' => 'Transcription failed'], 500);
        }
    }
}