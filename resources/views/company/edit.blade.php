<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Company Profile') }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded-full">
                    ✓ Company Set Up
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Current Company Header -->
                    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-4">
                            @if($company->logo)
                                <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->company_name }}" class="w-16 h-16 rounded-lg object-cover">
                            @else
                                <div class="w-16 h-16 bg-gray-300 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                    <span class="text-2xl font-bold text-gray-600 dark:text-gray-400">
                                        {{ substr($company->company_name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-xl font-bold">{{ $company->company_name }}</h3>
                                <p class="text-gray-600 dark:text-gray-400">{{ $company->company_email }}</p>
                                @if($company->website)
                                    <a href="{{ $company->website }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                        {{ $company->website }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('company.partials.form', ['submitText' => 'Update Company Details', 'company' => $company])
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="mt-6 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            Invoice Settings
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <p>Be careful when changing invoice prefix or numbering - this affects all future invoices.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>