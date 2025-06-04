<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Set Up Your Company Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-center">
                    <div class="flex items-center text-blue-600 dark:text-blue-400">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-full text-white text-sm font-semibold">
                            1
                        </div>
                        <span class="ml-2 text-sm font-medium">Company Setup</span>
                    </div>
                    <div class="flex-1 border-t-2 border-gray-300 dark:border-gray-600 mx-4"></div>
                    <div class="flex items-center text-gray-400">
                        <div class="flex items-center justify-center w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full text-gray-600 dark:text-gray-400 text-sm font-semibold">
                            2
                        </div>
                        <span class="ml-2 text-sm font-medium">Start Invoicing</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Welcome Message -->
                    <div class="mb-6 text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            Welcome to InvoiceApp! 🎉
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Let's set up your company profile so you can start creating professional invoices in under 45 seconds.
                        </p>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('company.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('company.partials.form', ['submitText' => 'Create Company & Continue'])
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            Don't worry, you can change these details anytime!
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>Just fill in the basics now. You can always update your company information, add your logo, and adjust invoice settings later.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>