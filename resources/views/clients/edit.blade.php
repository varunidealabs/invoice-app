<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Client') }}: {{ $client->name }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('clients.show', $client) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    View Client
                </a>
                <a href="{{ route('clients.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Back to Clients
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('clients.update', $client) }}">
                        @csrf
                        @method('PUT')
                        @include('clients.partials.form', ['submitText' => 'Update Client', 'client' => $client])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>