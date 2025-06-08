<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('AI Invoice Assistant') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Create invoices and quotations through natural conversation</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5"></span>
                    AI Assistant Active
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Smart Defaults Enabled
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Context Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900">Quick Context</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Recent Clients:</span>
                            <div class="mt-1 text-gray-600" id="recent-clients">
                                @if(auth()->user()->company->clients->count() > 0)
                                    {{ auth()->user()->company->clients->take(3)->pluck('name')->implode(', ') }}
                                @else
                                    No clients yet
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Default Settings:</span>
                            <div class="mt-1 text-gray-600">
                                Terms: {{ auth()->user()->company->default_payment_terms }}<br>
                                Currency: {{ auth()->user()->company->currency_symbol }}{{ auth()->user()->company->currency }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Quick Stats:</span>
                            <div class="mt-1 text-gray-600">
                                {{ auth()->user()->company->clients->count() }} clients<br>
                                {{ auth()->user()->company->invoices->count() }} invoices<br>
                                {{ auth()->user()->company->quotations->count() }} quotations
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Interface -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Chat Container -->
                    <div id="chat-container" class="mb-6 h-96 md:h-[500px] lg:h-[600px] overflow-y-auto border border-gray-300 rounded-lg p-4 bg-gray-50 space-y-4">
                        <!-- Welcome Message -->
                        <div class="flex justify-center">
                            <div class="max-w-2xl text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">AI Invoice Assistant Ready</h3>
                                <p class="text-gray-600 mb-4">I can help you create invoices and quotations through natural conversation. Just tell me what you need!</p>
                                
                                <!-- Example Prompts -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-left">
                                    <button class="example-prompt p-3 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors text-sm">
                                        <div class="font-medium text-gray-900">Create Invoice</div>
                                        <div class="text-gray-600">"Create invoice for ABC Corp for web development work, 5000 rupees"</div>
                                    </button>
                                    <button class="example-prompt p-3 bg-white border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors text-sm">
                                        <div class="font-medium text-gray-900">Create Quotation</div>
                                        <div class="text-gray-600">"Quote for XYZ Ltd: design work 2 hours at 150/hour"</div>
                                    </button>
                                    <button class="example-prompt p-3 bg-white border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors text-sm">
                                        <div class="font-medium text-gray-900">Multiple Items</div>
                                        <div class="text-gray-600">"Invoice for consulting 2 days and report writing for TechCorp"</div>
                                    </button>
                                    <button class="example-prompt p-3 bg-white border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors text-sm">
                                        <div class="font-medium text-gray-900">New Client</div>
                                        <div class="text-gray-600">"Create quotation for new client StartupXYZ for mobile app"</div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Input Section -->
                    <div class="space-y-4">
                        <!-- Input Bar -->
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <input type="text" id="message-input" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="Describe what you want to create..." 
                                       maxlength="500">
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <span id="char-count" class="text-xs text-gray-400">0/500</span>
                                </div>
                            </div>
                            <button id="mic-btn" 
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m14 0a7 7 0 00-14 0m14 0v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14 0V9a2 2 0 00-2-2M5 7a2 2 0 012-2h10a2 2 0 012 2v2m-14 0V9"></path>
                                </svg>
                            </button>
                            <button id="send-btn" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Tips -->
                        <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                            <strong>Tips:</strong> Mention client name instead of ID • I'll use smart defaults for dates and terms • Say "invoice" or "quotation" to specify type • Use voice input for hands-free operation
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-container');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const micBtn = document.getElementById('mic-btn');
        const charCount = document.getElementById('char-count');
        let conversationHistory = [];
        let mediaRecorder = null;
        let audioChunks = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
        });

        function setupEventListeners() {
            // Send button
            sendBtn.addEventListener('click', sendMessage);
            
            // Enter key
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Character counter
            messageInput.addEventListener('input', function() {
                charCount.textContent = `${this.value.length}/500`;
            });
            
            // Example prompts
            document.addEventListener('click', function(e) {
                if (e.target.closest('.example-prompt')) {
                    const prompt = e.target.closest('.example-prompt');
                    const text = prompt.querySelector('.text-gray-600').textContent;
                    messageInput.value = text.replace(/"/g, '');
                    removeWelcomeMessage();
                    sendMessage();
                }
            });
            
            // Voice recording
            micBtn.addEventListener('click', handleVoiceRecording);
        }

        function removeWelcomeMessage() {
            const welcome = chatContainer.querySelector('.flex.justify-center');
            if (welcome) {
                welcome.remove();
            }
        }

        function addMessage(message, isUser = false, metadata = {}) {
            removeWelcomeMessage();
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;
            
            const bubble = document.createElement('div');
            bubble.className = `max-w-xs lg:max-w-2xl px-4 py-3 rounded-lg ${
                isUser 
                    ? 'bg-blue-500 text-white' 
                    : 'bg-white border border-gray-200 text-gray-800 shadow-sm'
            }`;
            
            if (!isUser && metadata.invoice_created) {
                // Special handling for invoice creation
                bubble.className = 'max-w-xs lg:max-w-2xl p-4 rounded-lg bg-green-50 border border-green-200 text-gray-800';
                bubble.innerHTML = createInvoiceSuccessHTML(message, metadata);
            } else {
                bubble.textContent = message;
            }
            
            messageDiv.appendChild(bubble);
            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function createInvoiceSuccessHTML(message, metadata) {
            return `
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <div class="text-sm font-medium text-green-800">Invoice/Quotation Created Successfully!</div>
                            <div class="text-sm text-green-700 mt-1 whitespace-pre-line">${message}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-green-200">
                        ${metadata.view_url ? `<a href="${metadata.view_url}" target="_blank" class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            View
                        </a>` : ''}
                        ${metadata.pdf_url ? `<a href="${metadata.pdf_url}" target="_blank" class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m-4-4H6a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-6z"></path>
                            </svg>
                            PDF
                        </a>` : ''}
                    </div>
                </div>
            `;
        }

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            addMessage(message, true);
            
            // Add to conversation history
            conversationHistory.push({
                role: 'user',
                content: message
            });

            messageInput.value = '';
            charCount.textContent = '0/500';
            setLoading(true);

            try {
                // FIXED: Using web session authentication (no Bearer token)
                const response = await fetch('{{ route("chatgpt.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        // NO Authorization header - using web session
                    },
                    body: JSON.stringify({ 
                        message: message,
                        history: conversationHistory.slice(-10)
                    })
                });

                console.log('Response status:', response.status);

                if (response.ok) {
                    const data = await response.json();
                    
                    // Add assistant response to history
                    conversationHistory.push({
                        role: 'assistant',
                        content: data.reply
                    });

                    // Check for special metadata
                    const metadata = {
                        invoice_created: data.invoice_created || false,
                        client_created: data.client_created || false,
                        invoice_id: data.invoice_id,
                        invoice_number: data.invoice_number,
                        total: data.total,
                        view_url: data.view_url,
                        pdf_url: data.pdf_url
                    };

                    addMessage(data.reply, false, metadata);
                } else {
                    // Show actual HTTP error
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    addMessage(`Sorry, I encountered an error (${response.status}). Please try again.`, false);
                }
            } catch (error) {
                console.error('Network error:', error);
                addMessage(`Network error: ${error.message}. Please check your connection and try again.`, false);
            } finally {
                setLoading(false);
            }
        }

        function setLoading(loading) {
            sendBtn.disabled = loading;
            if (loading) {
                sendBtn.innerHTML = `
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
            } else {
                sendBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                `;
            }
        }

        // Voice recording functionality
        async function handleVoiceRecording() {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                updateMicButton(false);
            } else {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = function(event) {
                        audioChunks.push(event.data);
                    };

                    mediaRecorder.onstop = async function() {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                        await transcribeAudio(audioBlob);
                        stream.getTracks().forEach(track => track.stop());
                    };

                    mediaRecorder.start();
                    updateMicButton(true);
                } catch (error) {
                    console.error('Error accessing microphone:', error);
                    showToast('Could not access microphone. Please check permissions.', 'error');
                }
            }
        }

        function updateMicButton(recording) {
            if (recording) {
                micBtn.className = 'bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors pulse';
                micBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9l6 6m0-6l-6 6"></path>
                    </svg>
                `;
            } else {
                micBtn.className = 'bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition-colors';
                micBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m14 0a7 7 0 00-14 0m14 0v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14 0V9a2 2 0 00-2-2M5 7a2 2 0 012-2h10a2 2 0 012 2v2m-14 0V9"></path>
                    </svg>
                `;
            }
        }

        async function transcribeAudio(audioBlob) {
            const formData = new FormData();
            formData.append('audio', audioBlob, 'recording.wav');

            try {
                micBtn.disabled = true;
                micBtn.innerHTML = `
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;

                // FIXED: Using web session authentication (no Bearer token)
                const response = await fetch('{{ route("chatgpt.transcribe") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        // NO Authorization header - using web session
                    },
                    body: formData
                });

                const data = await response.json();
                
                if (data.transcription) {
                    messageInput.value = data.transcription;
                    charCount.textContent = `${data.transcription.length}/500`;
                    sendMessage();
                } else {
                    showToast('Transcription failed. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Transcription error:', error);
                showToast('Transcription failed. Please try again.', 'error');
            } finally {
                micBtn.disabled = false;
                updateMicButton(false);
            }
        }

        // Utility functions
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${type === 'success' 
                            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                        }
                    </svg>
                    <span class="text-sm">${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }

        // Add pulse animation for recording
        const style = document.createElement('style');
        style.textContent = `
            .pulse {
                animation: pulse 1.5s infinite;
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }
        `;
        document.head.appendChild(style);
    </script>
</x-app-layout>