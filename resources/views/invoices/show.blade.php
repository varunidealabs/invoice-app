<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $invoice->is_quotation ? 'Quotation' : 'Invoice' }} {{ $invoice->invoice_number }}
            </h2>
            <div class="flex space-x-4">
                <!-- PDF Buttons -->
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View PDF
                </a>
                
                <a href="{{ route('invoices.download', $invoice) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m-4-4H6a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-6z"></path>
                    </svg>
                    Download PDF
                </a>
                
                <!-- FIXED: Share button with proper styling -->
                <button onclick="shareInvoice()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                    Share
                </button>

                @if($invoice->status !== 'paid' && !$invoice->is_quotation)
                    <a href="{{ route('invoices.edit', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Edit Invoice
                    </a>
                @elseif($invoice->is_quotation && $invoice->status !== 'accepted')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Edit Quotation
                    </a>
                @endif
                
                <a href="{{ route('invoices.index', ['type' => $invoice->is_quotation ? 'quotation' : 'invoice']) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Back to {{ $invoice->is_quotation ? 'Quotations' : 'Invoices' }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Status Actions -->
            @if($invoice->is_quotation && $invoice->status !== 'accepted')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quotation Actions</h3>
                        <div class="flex space-x-4">
                            @if($invoice->status === 'draft')
                                <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Send Quotation
                                    </button>
                                </form>
                            @endif
                            
                            @if(in_array($invoice->status, ['sent', 'viewed']))
                                <form method="POST" action="{{ route('quotations.accept', $invoice) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Mark as Accepted
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('quotations.convert', $invoice) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Convert to Invoice
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif(!$invoice->is_quotation && $invoice->status !== 'paid')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Invoice Actions</h3>
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

            <!-- Document Preview -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            @if(auth()->user()->company->logo)
                                <img src="{{ Storage::url(auth()->user()->company->logo) }}" alt="Company Logo" class="h-16 mb-4">
                            @endif
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $invoice->is_quotation ? 'QUOTATION' : 'INVOICE' }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">{{ $invoice->invoice_number }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $invoice->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $invoice->is_overdue ? 'bg-red-100 text-red-800' : '' }}
                                {{ $invoice->is_expired ? 'bg-orange-100 text-orange-800' : '' }}
                            ">
                                @if($invoice->is_overdue)
                                    Overdue
                                @elseif($invoice->is_expired)
                                    Expired
                                @else
                                    {{ $invoice->status_label }}
                                @endif
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
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                {{ $invoice->is_quotation ? 'Quote To:' : 'Bill To:' }}
                            </h3>
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
                                            <p>{{ $invoice->client->city }}@if($invoice->client->state), {{ $invoice->client->state }}@endif @if($invoice->client->postal_code) {{ $invoice->client->postal_code }}@endif</p>
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

                    <!-- Document Details - FIXED -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Issue Date</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->issue_date->format('F d, Y') }}</p>
                        </div>
                        
                        @if($invoice->is_quotation)
                            <!-- For Quotations: Show Valid Until -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Valid Until</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 {{ $invoice->is_expired ? 'text-red-600' : '' }}">
                                    {{ $invoice->valid_until ? $invoice->valid_until->format('F d, Y') : 'N/A' }}
                                    @if($invoice->is_expired)
                                        <span class="block text-xs text-red-600">(Expired {{ $invoice->valid_until->diffForHumans() }})</span>
                                    @endif
                                </p>
                            </div>
                        @else
                            <!-- For Invoices: Show Due Date -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Due Date</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 {{ $invoice->is_overdue ? 'text-red-600' : '' }}">
                                    {{ $invoice->due_date ? $invoice->due_date->format('F d, Y') : 'N/A' }}
                                    @if($invoice->is_overdue)
                                        <span class="block text-xs text-red-600">({{ $invoice->due_date->diffForHumans() }})</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Payment Terms</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->payment_terms }}</p>
                        </div>
                    </div>

                    <!-- Document Items -->
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
                            @if(!$invoice->is_quotation && $invoice->payments->sum('amount') > 0)
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

            <!-- Payments History (Only for Invoices) -->
            @if(!$invoice->is_quotation && $invoice->payments->count() > 0)
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

            <!-- Delete Document (for non-paid invoices and non-accepted quotations) -->
            @if(($invoice->is_quotation && $invoice->status !== 'accepted') || (!$invoice->is_quotation && $invoice->status !== 'paid'))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-red-600 mb-4">Danger Zone</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Once you delete this {{ $invoice->is_quotation ? 'quotation' : 'invoice' }}, there is no going back. Please be certain.
                        </p>
                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this {{ $invoice->is_quotation ? 'quotation' : 'invoice' }}? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Delete {{ $invoice->is_quotation ? 'Quotation' : 'Invoice' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

</x-app-layout>
    



    <script>
        async function shareInvoice() {
            const shareBtn = event.target;
            const originalText = shareBtn.innerHTML;
            
            // Show loading state
            shareBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Preparing...';
            shareBtn.disabled = true;
        
            try {
                // Get PDF as blob
                const response = await fetch('{{ route("invoices.pdf", $invoice) }}');
                
                if (!response.ok) {
                    throw new Error('Failed to load PDF');
                }
                
                const blob = await response.blob();
                const documentType = '{{ $invoice->is_quotation ? "quotation" : "invoice" }}';
                const filename = '{{ $invoice->getPdfFilename() }}';
                
                // Check if Web Share API is supported and can share files
                if (navigator.share && navigator.canShare) {
                    const shareData = {
                        title: `${documentType.charAt(0).toUpperCase() + documentType.slice(1)} {{ $invoice->invoice_number }}`,
                        text: `{{ $invoice->is_quotation ? "Quotation" : "Invoice" }} {{ $invoice->invoice_number }} from {{ $invoice->company->company_name }}`,
                        files: [new File([blob], filename, { type: 'application/pdf' })]
                    };
                    
                    // Check if the browser can share this content
                    if (navigator.canShare(shareData)) {
                        await navigator.share(shareData);
                    } else {
                        // Fallback: download the file
                        downloadPDF(blob, filename);
                    }
                } else {
                    // Fallback: download the file
                    downloadPDF(blob, filename);
                }
                
            } catch (error) {
                console.error('Share failed:', error);
                alert('Unable to share. The PDF will be downloaded instead.');
                
                // Fallback: direct download
                window.open('{{ route("invoices.download", $invoice) }}', '_blank');
            } finally {
                // Restore button state
                shareBtn.innerHTML = originalText;
                shareBtn.disabled = false;
            }
        }
    
        function downloadPDF(blob, filename) {
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Show message
            showMessage('PDF downloaded successfully! You can now share it from your downloads folder.');
        }
    
        function showMessage(message) {
            // Create a toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm';
            toast.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-sm">${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }
    </script>