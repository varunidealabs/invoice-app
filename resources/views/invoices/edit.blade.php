<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Invoice') }} {{ $invoice->invoice_number }}
            </h2>
            <a href="{{ route('invoices.show', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                Back to Invoice
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('invoices.update', $invoice) }}" x-data="invoiceForm()">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Client Selection -->
                            <div>
                                <x-input-label for="client_id" :value="__('Client')" />
                                <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Select a client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>

                            <!-- Payment Terms -->
                            <div>
                                <x-input-label for="payment_terms" :value="__('Payment Terms')" />
                                <select id="payment_terms" name="payment_terms" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    @php
                                        $paymentTerms = [
                                            'Due on receipt' => 'Due on receipt',
                                            'Net 15' => 'Net 15 days',
                                            'Net 30' => 'Net 30 days',
                                            'Net 45' => 'Net 45 days',
                                            'Net 60' => 'Net 60 days'
                                        ];
                                    @endphp
                                    @foreach($paymentTerms as $value => $label)
                                        <option value="{{ $value }}" {{ old('payment_terms', $invoice->payment_terms) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('payment_terms')" class="mt-2" />
                            </div>

                            <!-- Issue Date -->
                            <div>
                                <x-input-label for="issue_date" :value="__('Issue Date')" />
                                <x-text-input id="issue_date" class="block mt-1 w-full" type="date" name="issue_date" :value="old('issue_date', $invoice->issue_date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('issue_date')" class="mt-2" />
                            </div>

                            <!-- Due Date -->
                            <div>
                                <x-input-label for="due_date" :value="__('Due Date')" />
                                <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date', $invoice->due_date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                            </div>

                            <!-- Tax Rate -->
                            <div>
                                <x-input-label for="tax_rate" :value="__('Tax Rate (%)')" />
                                <x-text-input id="tax_rate" class="block mt-1 w-full" type="number" name="tax_rate" :value="old('tax_rate', $invoice->tax_rate)" min="0" max="100" step="0.01" x-model="taxRate" />
                                <x-input-error :messages="$errors->get('tax_rate')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Invoice Items</h3>
                                <button type="button" @click="addItem()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Add Item
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-4 items-end p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <!-- Description -->
                                        <div class="col-span-12 md:col-span-5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" 
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                                   placeholder="Enter item description" required>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-span-4 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Qty</label>
                                            <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" 
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                                   min="0.01" step="0.01" required @input="calculateTotals()">
                                        </div>

                                        <!-- Unit Price -->
                                        <div class="col-span-4 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Price</label>
                                            <input type="number" :name="`items[${index}][unit_price]`" x-model="item.unit_price" 
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                                   min="0" step="0.01" required @input="calculateTotals()">
                                        </div>

                                        <!-- Total -->
                                        <div class="col-span-3 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total</label>
                                            <div class="mt-1 p-2 bg-gray-100 dark:bg-gray-600 rounded-md text-sm font-medium" x-text="formatCurrency(item.quantity * item.unit_price)"></div>
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="col-span-1">
                                            <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800" x-show="items.length > 1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Invoice Totals -->
                            <div class="mt-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="max-w-sm ml-auto space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Subtotal:</span>
                                        <span class="text-sm font-medium" x-text="formatCurrency(subtotal)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Tax (<span x-text="taxRate"></span>%):</span>
                                        <span class="text-sm font-medium" x-text="formatCurrency(taxAmount)"></span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                                        <span>Total:</span>
                                        <span x-text="formatCurrency(total)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notes (Optional)')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Additional notes for the client...">{{ old('notes', $invoice->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Terms -->
                        <div class="mb-6">
                            <x-input-label for="terms" :value="__('Terms & Conditions (Optional)')" />
                            <textarea id="terms" name="terms" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Terms and conditions...">{{ old('terms', $invoice->terms) }}</textarea>
                            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('invoices.show', $invoice) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                                Update Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoiceForm() {
            return {
                items: @json($invoice->items->map(function($item) {
                    return [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price
                    ];
                })->toArray()),
                taxRate: {{ old('tax_rate', $invoice->tax_rate) }},
                
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)), 0);
                },
                
                get taxAmount() {
                    return this.subtotal * (parseFloat(this.taxRate || 0) / 100);
                },
                
                get total() {
                    return this.subtotal + this.taxAmount;
                },
                
                addItem() {
                    this.items.push({ description: '', quantity: 1, unit_price: 0 });
                },
                
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.calculateTotals();
                    }
                },
                
                calculateTotals() {
                    // Trigger reactivity
                    this.$nextTick(() => {});
                },
                
                formatCurrency(amount) {
                    return '{{ auth()->user()->company->currency_symbol }}' + parseFloat(amount || 0).toFixed(2);
                }
            }
        }
    </script>
</x-app-layout>