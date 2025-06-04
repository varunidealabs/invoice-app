<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Invoices') }}
            </h2>
            <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                Create Invoice
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Filter Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        <a href="{{ route('invoices.index') }}" 
                           class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('status') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            All ({{ $statusCounts['all'] }})
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'draft']) }}" 
                           class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'draft' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Draft ({{ $statusCounts['draft'] }})
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'sent']) }}" 
                           class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'sent' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Sent ({{ $statusCounts['sent'] }})
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'paid']) }}" 
                           class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'paid' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Paid ({{ $statusCounts['paid'] }})
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" 
                           class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'overdue' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Overdue ({{ $statusCounts['overdue'] }})
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <form method="GET" action="{{ route('invoices.index') }}" class="flex gap-4">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Search invoices or clients..." 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('invoices.index', request()->only('status')) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if($invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Invoice
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Client
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Due Date
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $invoice->invoice_number }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $invoice->issue_date->format('M d, Y') }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $invoice->client->name }}
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
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                                View
                                            </a>
                                            @if($invoice->status !== 'paid')
                                                <a href="{{ route('invoices.edit', $invoice) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                                                    Edit
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4">
                        {{ $invoices->withQueryString()->links() }}
                    </div>
                @else
                    <div class="p-6 text-center">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            @if($search)
                                No invoices found matching "{{ $search }}".
                            @elseif(request('status'))
                                No {{ request('status') }} invoices found.
                            @else
                                No invoices found. Create your first invoice to get started.
                            @endif
                        </div>
                        @if(!$search && !request('status'))
                            <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-block">
                                Create Your First Invoice
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>