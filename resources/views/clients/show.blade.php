<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $client->name }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('invoices.create', ['client' => $client->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Create Invoice
                </a>
                <a href="{{ route('clients.edit', $client) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit Client
                </a>
                <a href="{{ route('clients.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Back to Clients
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Client Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Client Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Info -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Client Name</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->name }}</p>
                            </div>

                            @if($client->contact_person)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Person</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->contact_person }}</p>
                                </div>
                            @endif

                            @if($client->email)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <a href="mailto:{{ $client->email }}" class="text-blue-600 hover:text-blue-800">{{ $client->email }}</a>
                                    </p>
                                </div>
                            @endif

                            @if($client->phone)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <a href="tel:{{ $client->phone }}" class="text-blue-600 hover:text-blue-800">{{ $client->phone }}</a>
                                    </p>
                                </div>
                            @endif

                            @if($client->tax_id)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tax ID</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->tax_id }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Address Info -->
                        <div class="space-y-4">
                            @if($client->address_line_1 || $client->city || $client->state)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</h4>
                                    <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @if($client->address_line_1)
                                            <p>{{ $client->address_line_1 }}</p>
                                        @endif
                                        @if($client->address_line_2)
                                            <p>{{ $client->address_line_2 }}</p>
                                        @endif
                                        @if($client->city || $client->state || $client->postal_code)
                                            <p>
                                                {{ $client->city }}@if($client->city && ($client->state || $client->postal_code)), @endif
                                                {{ $client->state }} {{ $client->postal_code }}
                                            </p>
                                        @endif
                                        @if($client->country)
                                            <p>{{ $client->country }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Client Since</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->created_at->format('F d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Statistics -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Invoice Summary</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $client->invoices->count() }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Invoices</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">
                                {{ auth()->user()->company->currency_symbol }}{{ number_format($client->invoices->where('status', 'paid')->sum('total'), 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Paid</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">
                                {{ auth()->user()->company->currency_symbol }}{{ number_format($client->invoices->whereIn('status', ['sent', 'viewed'])->sum('total'), 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Outstanding</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">
                                {{ $client->invoices->filter(function($invoice) { return $invoice->is_overdue; })->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Overdue</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Invoices</h3>
                        <a href="{{ route('invoices.index', ['search' => $client->name]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All Invoices
                        </a>
                    </div>

                    @if($recentInvoices->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Invoice</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recentInvoices as $invoice)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->issue_date->format('M d, Y') }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->total, 2) }}
                                                </div>
                                                @if($invoice->payments->sum('amount') > 0)
                                                    <div class="text-xs text-green-600 dark:text-green-400">
                                                        Paid: {{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->payments->sum('amount'), 2) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $invoice->is_overdue ? 'bg-red-100 text-red-800' : '' }}
                                                ">
                                                    {{ $invoice->is_overdue ? 'Overdue' : $invoice->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $invoice->due_date->format('M d, Y') }}
                                                @if($invoice->is_overdue)
                                                    <div class="text-xs text-red-600">
                                                        {{ $invoice->due_date->diffForHumans() }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 dark:text-gray-400 mb-4">No invoices found for this client.</div>
                            <a href="{{ route('invoices.create', ['client' => $client->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-block">
                                Create First Invoice
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-red-600 mb-4">Danger Zone</h3>
                    
                    @if($client->invoices->count() > 0)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        Cannot Delete Client
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>This client has {{ $client->invoices->count() }} invoice(s) associated with them. You must delete all invoices before deleting this client.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Once you delete this client, there is no going back. Please be certain.
                        </p>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this client? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Delete Client
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>