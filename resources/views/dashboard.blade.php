<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Welcome back, {{ auth()->user()->name }}! Here's what's happening with {{ auth()->user()->company->company_name }}.
                </p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    New Invoice
                </a>
                <a href="{{ route('clients.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Add Client
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Clients -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_clients'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total Clients</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('clients.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View all clients →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Invoices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_invoices'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total Invoices</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('invoices.index') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                View all invoices →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ auth()->user()->company->currency_symbol }}{{ number_format($stats['total_revenue'], 2) }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('invoices.index', ['status' => 'paid']) }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                View paid invoices →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pending Amount -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ auth()->user()->company->currency_symbol }}{{ number_format($stats['pending_amount'], 2) }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Pending Amount</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('invoices.index', ['status' => 'sent']) }}" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                                View pending invoices →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Invoice Status Breakdown -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Invoice Status</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Pending Invoices</span>
                                <span class="text-sm font-medium text-orange-600">{{ $stats['pending_invoices'] }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Overdue Invoices</span>
                                <span class="text-sm font-medium text-red-600">{{ $stats['overdue_invoices'] }}</span>
                            </div>
                            @if($stats['overdue_invoices'] > 0)
                                <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-md">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="ml-3">
                                            <p class="text-sm text-red-800 dark:text-red-200">
                                                You have overdue invoices that need attention.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('invoices.create') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create New Invoice
                                </div>
                            </a>
                            <a href="{{ route('clients.create') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Add New Client
                                </div>
                            </a>
                            <a href="{{ route('company.edit') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Company Settings
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Company Info -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Company Info</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                @if(auth()->user()->company->logo)
                                    <img src="{{ Storage::url(auth()->user()->company->logo) }}" alt="Logo" class="w-10 h-10 rounded-lg mr-3">
                                @else
                                    <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-lg flex items-center justify-center mr-3">
                                        <span class="text-sm font-bold text-gray-600 dark:text-gray-400">
                                            {{ substr(auth()->user()->company->company_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ auth()->user()->company->company_name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Next Invoice: {{ auth()->user()->company->formatted_next_invoice_number }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <p>{{ auth()->user()->company->city }}, {{ auth()->user()->company->state }}</p>
                                <p>Currency: {{ auth()->user()->company->currency }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Invoices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Invoices</h3>
                            <a href="{{ route('invoices.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View All
                            </a>
                        </div>
                        
                        @if($recentInvoices->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentInvoices as $invoice)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $invoice->invoice_number }}
                                                </span>
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ auth()->user()->company->currency_symbol }}{{ number_format($invoice->total, 2) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $invoice->client->name }}
                                                </span>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $invoice->is_overdue ? 'bg-red-100 text-red-800' : '' }}
                                                ">
                                                    {{ $invoice->is_overdue ? 'Overdue' : $invoice->status_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-gray-500 dark:text-gray-400 mb-4">No invoices yet.</div>
                                <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-block">
                                    Create Your First Invoice
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Clients -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Clients</h3>
                            <a href="{{ route('clients.index') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                View All
                            </a>
                        </div>
                        
                        @if($recentClients->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentClients as $client)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $client->name }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $client->invoices->count() }} invoice{{ $client->invoices->count() !== 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                            @if($client->email)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $client->email }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-gray-500 dark:text-gray-400 mb-4">No clients yet.</div>
                                <a href="{{ route('clients.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-block">
                                    Add Your First Client
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>