<!-- Basic Information Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="md:col-span-2">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Basic Information
        </h4>
    </div>

    <!-- Client Name -->
    <div>
        <x-input-label for="name" :value="__('Client Name *')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" 
            :value="old('name', $client->name ?? '')" required autofocus placeholder="Acme Corporation" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <!-- Contact Person -->
    <div>
        <x-input-label for="contact_person" :value="__('Contact Person')" />
        <x-text-input id="contact_person" class="block mt-1 w-full" type="text" name="contact_person" 
            :value="old('contact_person', $client->contact_person ?? '')" placeholder="John Smith" />
        <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
    </div>

    <!-- Email -->
    <div>
        <x-input-label for="email" :value="__('Email Address')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" 
            :value="old('email', $client->email ?? '')" placeholder="contact@acmecorp.com" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Phone -->
    <div>
        <x-input-label for="phone" :value="__('Phone Number')" />
        <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" 
            :value="old('phone', $client->phone ?? '')" placeholder="+91 98765 43210" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <!-- Tax ID -->
    <div>
        <x-input-label for="tax_id" :value="__('Tax ID / GST Number')" />
        <x-text-input id="tax_id" class="block mt-1 w-full" type="text" name="tax_id" 
            :value="old('tax_id', $client->tax_id ?? '')" placeholder="27AAPFU0939F1ZV" />
        <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
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
            :value="old('address_line_1', $client->address_line_1 ?? '')" placeholder="Building/Plot No, Street Name" />
        <x-input-error :messages="$errors->get('address_line_1')" class="mt-2" />
    </div>

    <!-- Address Line 2 -->
    <div class="md:col-span-2">
        <x-input-label for="address_line_2" :value="__('Address Line 2 (Optional)')" />
        <x-text-input id="address_line_2" class="block mt-1 w-full" type="text" name="address_line_2" 
            :value="old('address_line_2', $client->address_line_2 ?? '')" placeholder="Area, Landmark" />
        <x-input-error :messages="$errors->get('address_line_2')" class="mt-2" />
    </div>

    <!-- City -->
    <div>
        <x-input-label for="city" :value="__('City')" />
        <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" 
            :value="old('city', $client->city ?? '')" placeholder="Mumbai" />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>

    <!-- State -->
    <div>
        <x-input-label for="state" :value="__('State')" />
        <select id="state" name="state" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
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
                <option value="{{ $state }}" {{ old('state', $client->state ?? '') == $state ? 'selected' : '' }}>
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
            :value="old('postal_code', $client->postal_code ?? '')" placeholder="400001" />
        <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
    </div>

    <!-- Country -->
    <div>
        <x-input-label for="country" :value="__('Country')" />
        <select id="country" name="country" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="India" {{ old('country', $client->country ?? 'India') == 'India' ? 'selected' : '' }}>India</option>
            <option value="United States" {{ old('country', $client->country ?? '') == 'United States' ? 'selected' : '' }}>United States</option>
            <option value="United Kingdom" {{ old('country', $client->country ?? '') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
            <option value="Canada" {{ old('country', $client->country ?? '') == 'Canada' ? 'selected' : '' }}>Canada</option>
            <option value="Australia" {{ old('country', $client->country ?? '') == 'Australia' ? 'selected' : '' }}>Australia</option>
        </select>
        <x-input-error :messages="$errors->get('country')" class="mt-2" />
    </div>
</div>

<!-- Additional Information -->
<div class="mb-8">
    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Client Information
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>Only the client name is required. You can add other details now or update them later. This information will be used on invoices sent to this client.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Button -->
<div class="flex items-center justify-end space-x-4">
    <a href="{{ route('clients.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
        Cancel
    </a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
        {{ $submitText ?? 'Save Client' }}
    </button>
</div>