<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Invoice {{ $invoice->invoice_number }}
            </h2>
            <div class="flex space-x-4">
                <!-- PDF Buttons -->
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View PDF
                </a>
                
                <a href="{{ route('invoices.download', $invoice) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m-4-4H6a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-6z"></path>
                    </svg>
                    Download PDF
                </a>
                
                <button onclick="shareInvoice()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                    Share
                </button>

                @if($invoice->status !== 'paid')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Edit Invoice
                    </a>
                @endif
                <a href="{{ route('invoices.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Back to Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Status Actions -->
            @if($invoice->status !== 'paid')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                        <div class="flex space-x-4">
                            @if($invoice->status === 'draft')
                                <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Mark as Sent
                                    </button>
                                </form>
                            @endif
                            
                            @if(in_array($invoice->status, ['sent', 'viewed']))
                                <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Mark as Paid
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Invoice Preview -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            @if(auth()->user()->company->logo)
                                <img src="{{ Storage::url(auth()->user()->company->logo) }}" alt="Company Logo" class="h-16 mb-4">
                            @endif
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">INVOICE</h1>
                            <p class="text-gray-600 dark:text-gray-400">{{ $invoice->invoice_number }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $invoice->is_overdue ? 'bg-red-100 text-red-800' : '' }}
                            ">
                                {{ $invoice->is_overdue ? 'Overdue' : $invoice->status_label }}
                            </span>
                        </div>
                    </div>

                    <!-- Company and Client Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <!-- From -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">From:</h3>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ auth()->user()->company->company_name }}</p>
                                @if(auth()->user()->company->company_email)
                                    <p>{{ auth()->user()->company->company_email }}</p>
                                @endif
                                @if(auth()->user()->company->company_phone)
                                    <p>{{ auth()->user()->company->company_phone }}</p>
                                @endif
                                <div class="mt-2">
                                    <p>{{ auth()->user()->company->address_line_1 }}</p>
                                    @if(auth()->user()->company->address_line_2)
                                        <p>{{ auth()->user()->company->address_line_2 }}</p>
                                    @endif
                                    <p>{{ auth()->user()->company->city }}, {{ auth()->user()->company->state }} {{ auth()->user()->company->postal_code }}</p>
                                    <p>{{ auth()->user()->company->country }}</p>
                                </div>
                                @if(auth()->user()->company->tax_id)
                                    <p class="mt-2">Tax ID: {{ auth()->user()->company->tax_id }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- To -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Bill To:</h3>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->client->name }}</p>
                                @if($invoice->client->contact_person)
                                    <p>{{ $invoice->client->contact_person }}</p>
                                @endif
                                @if($invoice->client->email)
                                    <p>{{ $invoice->client->email }}</p>
                                @endif
                                @if($invoice->client->phone)
                                    <p>{{ $invoice->client->phone }}</p>
                                @endif
                                @if($invoice->client->address_line_1)
                                    <div class="mt-2">
                                        <p>{{ $invoice->client->address_line_1 }}</p>
                                        @if($invoice->client->address_line_2)
                                            <p>{{ $invoice->client->address_line_2 }}</p>
                                        @endif
                                        @if($invoice->client->city)
                                            <p>{{ $invoice->client->city }}@if($invoice->client->state), {{ $invoice->client->state }}@endif @if($invoice->client->postal_code){{ $invoice->client->postal_code }}@endif</p>
                                        @endif
                                        @if($invoice->client->country)
                                            <p>{{ $invoice->client->country }}</p>
                                        @endif
                                    </div>
                                @endif
                                @if($invoice->client->tax_id)
                                    <p class="mt-2">Tax ID: {{ $invoice->client->tax_id }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Issue Date</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->issue_date->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Due Date</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 {{ $invoice->is_overdue ? 'text-red-600' : '' }}">
                                {{ $invoice->due_date->format('F d, Y') }}
                                @if($invoice->is_overdue)
                                    <span class="block text-xs text-red-600">({{ $invoice->due_date->diffForHumans() }})</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Payment Terms</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->payment_terms }}</p>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="mb-8">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $item->description }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-400">{{ auth()->user()->company->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900 dark:text-gray-100">{{ auth()->user()->company->currency_symbol }}{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="flex justify-end mb-8">
                        <div class="w-64 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="font-medium">{{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->subtotal, 2) }}</span>
                            </div>
                            @if($invoice->tax_rate > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Tax ({{ number_format($invoice->tax_rate, 2) }}%):</span>
                                    <span class="font-medium">{{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total:</span>
                                <span>{{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->total, 2) }}</span>
                            </div>
                            @if($invoice->payments->sum('amount') > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Paid:</span>
                                    <span>{{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->payments->sum('amount'), 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm font-medium">
                                    <span>Balance Due:</span>
                                    <span>{{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->balance, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes and Terms -->
                    @if($invoice->notes || $invoice->terms)
                        <div class="border-t pt-6 space-y-4">
                            @if($invoice->notes)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Notes</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $invoice->notes }}</p>
                                </div>
                            @endif
                            @if($invoice->terms)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Terms & Conditions</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $invoice->terms }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payments History -->
            @if($invoice->payments->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Payment History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($invoice->payments as $payment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $payment->payment_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                                {{ auth()->user()->company->currency_symbol }}{{ number_format($payment->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                {{ $payment->method_label }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                {{ $payment->reference ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Delete Invoice (for non-paid invoices) -->
            @if($invoice->status !== 'paid')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-red-600 mb-4">Danger Zone</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Once you delete this invoice, there is no going back. Please be certain.
                        </p>
                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this invoice? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Delete Invoice
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Share Invoice</h3>
                    <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="shareContent" class="space-y-3">
                    <!-- Share options will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        async function shareInvoice() {
            try {
                const response = await fetch(`{{ route('invoices.share', $invoice) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showShareModal(data);
                } else {
                    alert('Error generating shareable link');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error sharing invoice');
            }
        }

        function showShareModal(data) {
            const shareContent = document.getElementById('shareContent');
            shareContent.innerHTML = `
                <div class="text-sm text-gray-600 mb-4">
                    Share this invoice with your client:
                </div>
                
                <!-- Copy Link -->
                <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                    <input type="text" value="${data.pdf_url}" readonly 
                           class="flex-1 text-sm bg-transparent border-none focus:ring-0" id="shareUrl">
                    <button onclick="copyToClipboard('${data.pdf_url}')" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                        Copy
                    </button>
                </div>
                
                <!-- Share Options -->
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <a href="${data.whatsapp_url}" target="_blank" 
                       class="flex items-center justify-center p-3 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                        WhatsApp
                    </a>
                    
                    <a href="mailto:?subject=${encodeURIComponent(data.email_subject)}&body=${encodeURIComponent(data.email_body)}" 
                       class="flex items-center justify-center p-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.733a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Email
                    </a>
                </div>
            `;
            
            document.getElementById('shareModal').classList.remove('hidden');
        }

        function closeShareModal() {
            document.getElementById('shareModal').classList.add('hidden');
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Link copied to clipboard!');
            });
        }

        // Close modal when clicking outside
        document.getElementById('shareModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeShareModal();
            }
        });
    </script>
</x-app-layout>