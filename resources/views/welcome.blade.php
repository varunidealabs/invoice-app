<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Pay It Dammit - Professional Invoicing Made Simple</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .gradient-bg { 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            }
            .invoice-card { 
                transform: perspective(1000px) rotateY(-15deg) rotateX(5deg); 
            }
            .feature-card { 
                transition: all 0.3s ease; 
            }
            .feature-card:hover { 
                transform: translateY(-8px); 
            }
            /* Ensure buttons are clickable */
            .hero-buttons a {
                position: relative;
                z-index: 10;
                pointer-events: auto;
            }
        </style>
    </head>

    <body class="font-sans antialiased bg-gray-50 min-h-screen">
        <!-- Navigation -->
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-gray-900">Pay It Dammit</span>
                    </div>
                    
                    <!-- Auth Links -->
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-gray-900 px-4 py-2 text-sm font-medium transition-colors">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-4 py-2 text-sm font-medium transition-colors">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="relative py-20 gradient-bg overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="text-white">
                        <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                            Professional
                            <span class="block text-yellow-300">Invoicing</span>
                            Made Simple
                        </h1>
                        <p class="text-xl mb-8 text-indigo-100">
                            Create beautiful invoices, manage clients, and get paid faster. 
                            Built for freelancers and small businesses who want to focus on what they do best.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 hero-buttons">
                            @auth
                                <a href="{{ route('dashboard') }}" class="bg-white text-indigo-600 hover:bg-gray-50 px-8 py-3 rounded-lg font-semibold inline-flex items-center justify-center transition-colors">
                                    Go to Dashboard
                                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="bg-white text-indigo-600 hover:bg-gray-50 px-8 py-3 rounded-lg font-semibold inline-flex items-center justify-center transition-colors">
                                    Get Started Free
                                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('login') }}" class="border-2 border-white text-white hover:bg-white hover:text-indigo-600 px-8 py-3 rounded-lg font-semibold inline-flex items-center justify-center transition-colors">
                                    Sign In
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Right - Invoice Preview -->
                    <div class="relative">
                        <div class="invoice-card bg-white rounded-xl shadow-2xl p-8 max-w-md mx-auto">
                            <!-- Invoice Header -->
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">INVOICE</h3>
                                    <p class="text-gray-600">#INV-001</p>
                                </div>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                    Paid
                                </span>
                            </div>

                            <!-- Invoice Details -->
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">From:</span>
                                    <span class="font-medium">Your Company</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">To:</span>
                                    <span class="font-medium">Client Name</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date:</span>
                                    <span class="font-medium">{{ now()->format('M d, Y') }}</span>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <div class="border-t border-gray-200 pt-4 space-y-3">
                                <div class="flex justify-between">
                                    <span>Web Development</span>
                                    <span class="font-medium">₹50,000</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>UI/UX Design</span>
                                    <span class="font-medium">₹25,000</span>
                                </div>
                                <div class="border-t border-gray-200 pt-3 mt-3">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total:</span>
                                        <span class="text-indigo-600">₹75,000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Background Decoration -->
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-10 rounded-full"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white opacity-10 rounded-full"></div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                        Everything you need to manage invoices
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        From creating your first invoice to managing a growing client base, we've got you covered.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="feature-card bg-gray-50 p-8 rounded-xl text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Create Invoices</h3>
                        <p class="text-gray-600">Professional invoice templates with your branding. Add items, set taxes, and customize payment terms.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-card bg-gray-50 p-8 rounded-xl text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Manage Clients</h3>
                        <p class="text-gray-600">Keep all your client information organized. Track payment history and invoice status at a glance.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-card bg-gray-50 p-8 rounded-xl text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Track Payments</h3>
                        <p class="text-gray-600">Monitor invoice status, track overdue payments, and get insights into your business performance.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-3xl lg:text-4xl font-bold text-indigo-600 mb-2">10k+</div>
                        <div class="text-gray-600">Invoices Created</div>
                    </div>
                    <div>
                        <div class="text-3xl lg:text-4xl font-bold text-indigo-600 mb-2">500+</div>
                        <div class="text-gray-600">Happy Users</div>
                    </div>
                    <div>
                        <div class="text-3xl lg:text-4xl font-bold text-indigo-600 mb-2">99.9%</div>
                        <div class="text-gray-600">Uptime</div>
                    </div>
                    <div>
                        <div class="text-3xl lg:text-4xl font-bold text-indigo-600 mb-2">24/7</div>
                        <div class="text-gray-600">Support</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-indigo-600">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                    Ready to streamline your invoicing?
                </h2>
                <p class="text-xl text-indigo-100 mb-8">
                    Join thousands of professionals who trust Pay It Dammit for their billing needs.
                </p>
                @guest
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="bg-white text-indigo-600 hover:bg-gray-50 px-8 py-3 rounded-lg font-semibold text-lg inline-flex items-center justify-center transition-colors">
                            Start Your Free Trial
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                        <a href="{{ route('login') }}" class="border-2 border-white text-white hover:bg-white hover:text-indigo-600 px-8 py-3 rounded-lg font-semibold text-lg inline-flex items-center justify-center transition-colors">
                            Sign In
                        </a>
                    </div>
                @else
                    <a href="{{ url('/dashboard') }}" class="bg-white text-indigo-600 hover:bg-gray-50 px-8 py-3 rounded-lg font-semibold text-lg inline-flex items-center justify-center transition-colors">
                        Go to Dashboard
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @endguest
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="flex items-center justify-center space-x-3 mb-4">
                        <div class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">Pay It Dammit</span>
                    </div>
                    <p class="text-gray-400 mb-6">
                        Professional invoicing made simple for businesses of all sizes.
                    </p>
                    <div class="text-gray-500 text-sm">
                        © {{ date('Y') }} Pay It Dammit. All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>