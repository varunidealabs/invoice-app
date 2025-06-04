<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $clients = auth()->user()->company->clients()
            ->when($search, function ($query, $search) {
                $query->search($search);
            })
            ->latest()
            ->paginate(15);

        return view('clients.index', compact('clients', 'search'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);
        
        $recentInvoices = $client->invoices()
            ->with('payments')
            ->latest()
            ->take(5)
            ->get();

        return view('clients.show', compact('client', 'recentInvoices'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateClient($request);
        $validated['company_id'] = auth()->user()->company->id;
        
        $client = Client::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully!');
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);
        
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);
        
        $validated = $this->validateClient($request, $client->id);
        
        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully!');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        
        if ($client->invoices()->exists()) {
            return back()->with('error', 'Cannot delete client with existing invoices.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully!');
    }

    private function validateClient(Request $request, $clientId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 
                'email', 
                'max:255',
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company->id);
                })->ignore($clientId)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ];

        return $request->validate($rules, [
            'name.required' => 'Client name is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'A client with this email already exists.',
        ]);
    }
}