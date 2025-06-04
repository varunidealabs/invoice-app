<!-- Basic Information Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="md:col-span-2">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Basic Information
        </h4>
    </div>

    <!-- Company Name -->
    <div>
        <x-input-label for="company_name" :value="__('Company Name')" />
        <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" 
            :value="old('company_name', $company->company_name ?? '')" required autofocus placeholder="Acme Solutions Pvt Ltd" />
        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
    </div>

    <!-- Company Email -->
    <div>
        <x-input-label for="company_email" :value="__('Company Email')" />
        <x-text-input id="company_email" class="block mt-1 w-full" type="email" name="company_email" 
            :value="old('company_email', $company->company_email ?? '')" placeholder="hello@acmesolutions.com" />
        <x-input-error :messages="$errors->get('company_email')" class="mt-2" />
    </div>

    <!-- Company Phone -->
    <div>
        <x-input-label for="company_phone" :value="__('Phone Number')" />
        <x-text-input id="company_phone" class="block mt-1 w-full" type="tel" name="company_phone" 
            :value="old('company_phone', $company->company_phone ?? '')" placeholder="+91 98765 43210" />
        <x-input-error :messages="$errors->get('company_phone')" class="mt-2" />
    </div>

    <!-- Website -->
    <div>
        <x-input-label for="website" :value="__('Website (Optional)')" />
        <x-text-input id="website" class="block mt-1 w-full" type="url" name="website" 
            :value="old('website', $company->website ?? '')" placeholder="https://acmesolutions.com" />
        <x-input-error :messages="$errors->get('website')" class="mt-2" />
    </div>
</div>

<!-- Address Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="md:col-span-2">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Address Information
        </h4>
    </div>

    <!-- Address Line 1 -->
    <div class="md:col-span-2">
        <x-input-label for="address_line_1" :value="__('Address Line 1')" />
        <x-text-input id="address_line_1" class="block mt-1 w-full" type="text" name="address_line_1" 
            :value="old('address_line_1', $company->address_line_1 ?? '')" required placeholder="Building/Plot No, Street Name" />
        <x-input-error :messages="$errors->get('address_line_1')" class="mt-2" />
    </div>

    <!-- Address Line 2 -->
    <div class="md:col-span-2">
        <x-input-label for="address_line_2" :value="__('Address Line 2 (Optional)')" />
        <x-text-input id="address_line_2" class="block mt-1 w-full" type="text" name="address_line_2" 
            :value="old('address_line_2', $company->address_line_2 ?? '')" placeholder="Area, Landmark" />
        <x-input-error :messages="$errors->get('address_line_2')" class="mt-2" />
    </div>

    <!-- City -->
    <div>
        <x-input-label for="city" :value="__('City')" />
        <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" 
            :value="old('city', $company->city ?? '')" required placeholder="Mumbai" />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>

    <!-- State -->
    <div>
        <x-input-label for="state" :value="__('State')" />
        <select id="state" name="state" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
            <option value="">Select State</option>
            @php
                $states = [
                    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana',
                    'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
                    'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu',
                    'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'
                ];
            @endphp
            @foreach($states as $state)
                <option value="{{ $state }}" {{ old('state', $company->state ?? '') == $state ? 'selected' : '' }}>
                    {{ $state }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('state')" class="mt-2" />
    </div>

    <!-- Postal Code -->
    <div>
        <x-input-label for="postal_code" :value="__('Postal Code')" />
        <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" 
            :value="old('postal_code', $company->postal_code ?? '')" required placeholder="400001" />
        <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
    </div>

    <!-- Country -->
    <div>
        <x-input-label for="country" :value="__('Country')" />
        <select id="country" name="country" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
            <option value="India" {{ old('country', $company->country ?? 'India') == 'India' ? 'selected' : '' }}>India</option>
            <option value="United States" {{ old('country', $company->country ?? '') == 'United States' ? 'selected' : '' }}>United States</option>
            <option value="United Kingdom" {{ old('country', $company->country ?? '') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
            <option value="Canada" {{ old('country', $company->country ?? '') == 'Canada' ? 'selected' : '' }}>Canada</option>
            <option value="Australia" {{ old('country', $company->country ?? '') == 'Australia' ? 'selected' : '' }}>Australia</option>
        </select>
        <x-input-error :messages="$errors->get('country')" class="mt-2" />
    </div>
</div>

<!-- Business Details Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="md:col-span-2">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Business Details
        </h4>
    </div>

    <!-- Tax ID/GST -->
    <div>
        <x-input-label for="tax_id" :value="__('GST/Tax ID (Optional)')" />
        <x-text-input id="tax_id" class="block mt-1 w-full" type="text" name="tax_id" 
            :value="old('tax_id', $company->tax_id ?? '')" placeholder="27AAPFU0939F1ZV" />
        <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
    </div>

    <!-- Business Type -->
    <div>
        <x-input-label for="business_type" :value="__('Business Type')" />
        <select id="business_type" name="business_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="">Select Business Type</option>
            @php
                $businessTypes = [
                    'Sole Proprietorship', 'Partnership', 'Private Limited Company', 'Limited Liability Partnership',
                    'Public Limited Company', 'One Person Company', 'Section 8 Company', 'Producer Company', 'Freelancer'
                ];
            @endphp
            @foreach($businessTypes as $type)
                <option value="{{ $type }}" {{ old('business_type', $company->business_type ?? '') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('business_type')" class="mt-2" />
    </div>

    <!-- Logo Upload -->
    <div class="md:col-span-2">
        <x-input-label for="logo" :value="__('Company Logo (Optional)')" />
        @if(isset($company) && $company->logo)
            <div class="mt-2 mb-3">
                <img src="{{ Storage::url($company->logo) }}" alt="Current logo" class="w-20 h-20 rounded-lg object-cover">
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Current logo</p>
            </div>
        @endif
        <input id="logo" name="logo" type="file" accept="image/*" 
            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300" />
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">PNG, JPG, GIF up to 2MB. Square images work best.</p>
        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
    </div>
</div>

<!-- Invoice Settings Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="md:col-span-2">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Invoice Settings
        </h4>
    </div>

    <!-- Invoice Prefix -->
    <div>
        <x-input-label for="invoice_prefix" :value="__('Invoice Prefix')" />
        <x-text-input id="invoice_prefix" class="block mt-1 w-full" type="text" name="invoice_prefix" 
            :value="old('invoice_prefix', $company->invoice_prefix ?? 'INV')" required placeholder="INV" maxlength="10" />
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Your invoices will be numbered like: {{ old('invoice_prefix', $company->invoice_prefix ?? 'INV') }}-001</p>
        <x-input-error :messages="$errors->get('invoice_prefix')" class="mt-2" />
    </div>

    <!-- Next Invoice Number -->
    <div>
        <x-input-label for="next_invoice_number" :value="__('Starting Invoice Number')" />
        <x-text-input id="next_invoice_number" class="block mt-1 w-full" type="number" name="next_invoice_number" 
            :value="old('next_invoice_number', $company->next_invoice_number ?? '1')" required min="1" />
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Next invoice will be: {{ old('invoice_prefix', $company->invoice_prefix ?? 'INV') }}-{{ str_pad(old('next_invoice_number', $company->next_invoice_number ?? '1'), 3, '0', STR_PAD_LEFT) }}</p>
        <x-input-error :messages="$errors->get('next_invoice_number')" class="mt-2" />
    </div>

    <!-- Default Payment Terms -->
    <div>
        <x-input-label for="default_payment_terms" :value="__('Default Payment Terms')" />
        <select id="default_payment_terms" name="default_payment_terms" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
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
                <option value="{{ $value }}" {{ old('default_payment_terms', $company->default_payment_terms ?? 'Net 30') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('default_payment_terms')" class="mt-2" />
    </div>

    <!-- Currency -->
    <div>
        <x-input-label for="currency" :value="__('Currency')" />
        <select id="currency" name="currency" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
            @php
                $currencies = [
                    'INR' => '₹ Indian Rupee (INR)',
                    'USD' => '$ US Dollar (USD)',
                    'EUR' => '€ Euro (EUR)',
                    'GBP' => '£ British Pound (GBP)',
                    'AUD' => 'A$ Australian Dollar (AUD)',
                    'CAD' => 'C$ Canadian Dollar (CAD)'
                ];
            @endphp
            @foreach($currencies as $code => $label)
                <option value="{{ $code }}" {{ old('currency', $company->currency ?? 'INR') == $code ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('currency')" class="mt-2" />
    </div>
</div>

<!-- Submit Button -->
<div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
    <x-primary-button class="ml-4">
        {{ $submitText ?? 'Save Company Details' }}
    </x-primary-button>
</div>