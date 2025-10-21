/**
 * Fashion Store Chat Widget - Sistema Optimizado
 * Chat flotante que se inicia bajo demanda como las mejores páginas con IA
 */

class FashionStoreChatWidget {
    constructor() {
        this.isInitialized = false;
        this.isOpen = false;
        this.botUrl = null; // Se detectará automáticamente
        this.widget = null;
        this.chatContainer = null;
        this.messageHistory = [];
        this.isBotResponding = false; // Control de estado del bot
        this.currentBotMessage = null; // Mensaje actual del bot
        this.abortController = null; // Para cancelar peticiones
        this.botStatusChecked = false; // Nueva variable para verificar estado del bot solo una vez
        this.botAvailable = false; // Estado del bot
        
        // URL del bot - Puerto único 80 (Apache)
        // Detectar base path automáticamente
        const basePath = window.location.pathname.includes('/fashion-master/') ? '/fashion-master' : '';
        this.botUrl = window.location.protocol + '//' + window.location.hostname + basePath + '/proyecto-bot-main/api/bot_api.php';
        this.healthUrl = this.botUrl + '?action=health';
        this.chatUrl = this.botUrl + '?action=chat';
        this.suggestionsUrl = this.botUrl + '?action=suggestions';
        
        // Configuración
        this.config = {
            position: 'bottom-right', // Posición del botón
            theme: 'modern', // Tema moderno
            autoGreeting: false, // Sin saludo automático
            lazyLoad: true, // Carga bajo demanda
            animations: true, // Animaciones suaves
            typingSpeed: 13 // Velocidad de escritura en millisegundos (menor = más rápido)
        };
        
        // Inicializar widget
        this.init();
    }

    /**
     * Inicialización del widget - Solo el botón flotante
     */
    init() {
        // Solo mostrar log de inicialización una vez
        
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.createFloatingButton());
        } else {
            this.createFloatingButton();
        }
        
        this.isInitialized = true;
    }

    /**
     * Verificar si el bot está disponible - SOLO UNA VEZ
     */
    async detectBotPort() {
        // Si ya verificamos el estado del bot, devolver el resultado cached
        if (this.botStatusChecked) {
            return { active: this.botAvailable, port: this.botAvailable ? 80 : null };
        }

        try {
            const response = await fetch(this.healthUrl, {
                method: 'GET',
                mode: 'cors',
                cache: 'no-cache',
                signal: AbortSignal.timeout(3000)
            });
            
            if (response.ok) {
                const data = await response.json();
                this.botAvailable = true;
                this.botStatusChecked = true;
                return { active: true, port: 80, data };
            }
        } catch (error) {
        }
        
        this.botAvailable = false;
        this.botStatusChecked = true;
        return { active: false, port: null };
    }

    /**
     * Crear solo el botón flotante (sin el chat aún)
     */
    createFloatingButton() {
        // BOTÓN OCULTO - El chatbot no está activo actualmente
        return;
        
        // Verificar que el CSS esté cargado
        this.ensureCSSLoaded();
        
        // Crear botón flotante
        this.widget = document.createElement('div');
        this.widget.className = 'fs-chat-widget';
        
        // Ajustar posición del botón según el tamaño de pantalla
        this.adjustButtonPosition();
        
        this.widget.innerHTML = `
            <button class="fs-chat-button" id="fsChatButton" aria-label="Abrir asistente virtual">
                <div class="fs-chat-icon">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none">
                        <!-- Burbujas de chat modernas -->
                        <g class="fs-chat-bubbles">
                            <!-- Burbuja principal -->
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V6c0-1.1-.9-2-2-2z" 
                                  fill="white" 
                                  stroke="currentColor" 
                                  stroke-width="1.5" 
                                  stroke-linejoin="round"/>
                            
                            <!-- Puntos de escritura animados -->
                            <circle cx="8" cy="12" r="1.5" fill="currentColor">
                                <animate attributeName="opacity" values="0.3;1;0.3" dur="1.4s" repeatCount="indefinite" begin="0s"/>
                            </circle>
                            <circle cx="12" cy="12" r="1.5" fill="currentColor">
                                <animate attributeName="opacity" values="0.3;1;0.3" dur="1.4s" repeatCount="indefinite" begin="0.2s"/>
                            </circle>
                            <circle cx="16" cy="12" r="1.5" fill="currentColor">
                                <animate attributeName="opacity" values="0.3;1;0.3" dur="1.4s" repeatCount="indefinite" begin="0.4s"/>
                            </circle>
                        </g>
                        
                        <!-- Efecto de brillo sutil -->
                        <path d="M6 8 Q8 6, 10 8" 
                              stroke="white" 
                              stroke-width="1.5" 
                              stroke-linecap="round" 
                              opacity="0.6"/>
                        
                        <!-- Onda de señal -->
                        <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="0.5" fill="none" opacity="0">
                            <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.4;0;0.4" dur="2s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                </div>
                <div class="fs-chat-text">¿Necesitas ayuda?</div>
                <div class="fs-chat-pulse"></div>
            </button>
        `;
        
        // Agregar al DOM
        document.body.appendChild(this.widget);
        
        // Event listeners
        this.setupEventListeners();
        
        // Listener para cambios de tamaño de ventana
        window.addEventListener('resize', () => this.adjustButtonPosition());
        
        // Animación de entrada
        setTimeout(() => {
            this.widget.classList.add('fs-visible');
        }, 1000); // Aparece después de 1 segundo
    }

    /**
     * Ajustar posición del botón según el tamaño de pantalla
     */
    adjustButtonPosition() {
        if (!this.widget) return;
        
        const screenWidth = window.innerWidth;
        
        if (screenWidth <= 500) {
            // Pantallas muy pequeñas: botón más centrado
            this.widget.style.cssText = `
                position: fixed !important; 
                bottom: 20px !important; 
                right: 20px !important; 
                z-index: 10000 !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                pointer-events: auto !important;
            `;
        } else {
            // Todas las demás pantallas: pegado a la derecha y visible
            this.widget.style.cssText = `
                position: fixed !important; 
                bottom: 20px !important; 
                right: 20px !important; 
                z-index: 10000 !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                pointer-events: auto !important;
            `;
        }
    }

    /**
     * Verificar que el CSS del chat widget esté cargado
     */
    ensureCSSLoaded() {
        const cssId = 'fs-chat-widget-css';
        if (!document.getElementById(cssId)) {
            const link = document.createElement('link');
            link.id = cssId;
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = 'public/assets/css/fashion-chat-modal.css?v=' + Date.now();
            document.head.appendChild(link);
        }
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        const button = this.widget.querySelector('#fsChatButton');
        
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Agregar clase de click para animaciones
            button.classList.add('fs-clicked');
            
            // Remover la clase después de las animaciones
            setTimeout(() => {
                button.classList.remove('fs-clicked');
            }, 300); // 300ms coincide con la nueva duración más rápida de las animaciones CSS
            
            this.toggleChat();
        });

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeChat();
            }
        });
    }

    /**
     * Alternar chat (abrir/cerrar)
     */
    async toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            await this.openChat();
        }
    }

    /**
     * Abrir chat - Optimizado para no re-verificar el bot constantemente
     */
    async openChat() {
        if (this.isOpen) return;
        
        // Solo mostrar el mensaje de "abriendo" si es la primera vez
        if (!this.chatContainer) {        }
        
        // Cambiar estado del botón
        const button = this.widget.querySelector('#fsChatButton');
        button.classList.add('fs-loading');
        
        try {
            // Crear interfaz de chat si no existe
            if (!this.chatContainer) {
                await this.createChatInterface();
            }
            
            // Mostrar chat
            this.showChatInterface();
            
            // Cambiar estado
            this.isOpen = true;
            button.classList.remove('fs-loading');
            button.classList.add('fs-active');
            
            // Verificar que el bot esté disponible SOLO si no se ha verificado antes
            if (!this.botStatusChecked) {
                const botCheck = await this.detectBotPort();
                if (!botCheck.active) {
                    throw new Error('El servicio de chat no está disponible en este momento');
                }
            } else if (!this.botAvailable) {
                throw new Error('El servicio de chat no está disponible en este momento');
            }
            
            // Enviar saludo inicial si es la primera vez
            if (this.messageHistory.length === 0) {
                // Limpiar mensaje de inicio
                const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
                messagesContainer.innerHTML = '';
                this.addWelcomeMessage();
            }
            
        } catch (error) {
            button.classList.remove('fs-loading');
            this.showError(`Error iniciando el asistente: ${error.message}`);
            
            // Mostrar mensaje de error en el chat
            if (this.chatContainer) {
                const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
                messagesContainer.innerHTML = `
                    <div class="fs-error-message">
                        <h3>⚠️ Error de Conexión</h3>
                        <p>No se pudo conectar con el asistente virtual.</p>
                        <p><strong>Posibles causas:</strong></p>
                        <ul>
                            <li>El servidor está temporalmente no disponible</li>
                            <li>Problema de conectividad de red</li>
                            <li>Configuración del navegador</li>
                        </ul>
                        <button class="fs-retry-btn" onclick="this.parentElement.style.display='none'; window.location.reload()">🔄 Reintentar</button>
                    </div>
                `;
            }
        }
    }

    /**
     * Verificar disponibilidad del bot
     */
    async checkBotAvailability() {
        try {
            const response = await fetch(this.healthUrl);
            if (!response.ok) {
                throw new Error(`Bot no disponible: HTTP ${response.status}`);
            }
            return response.json();
        } catch (error) {
            throw new Error(`Error verificando disponibilidad del bot: ${error.message}`);
        }
    }

    /**
     * Crear interfaz completa del chat
     */
    async createChatInterface() {
        this.chatContainer = document.createElement('div');
        this.chatContainer.className = 'fs-chat-container';
        
        // Ajustar posicionamiento según el tamaño de pantalla
        this.adjustChatPosition();
        
        this.chatContainer.innerHTML = `
            <div class="fs-chat-header">
                <div class="fs-chat-avatar">
                    <img src="public/assets/img/logomarca_32.png" alt="Logo Fashion Store" width="32" height="32">
                </div>
                <div class="fs-chat-info">
                    <h3>Asistente Virtual</h3>
                    <p class="fs-chat-status">
                        <span class="fs-status-dot"></span>
                        En línea
                    </p>
                </div>
                <button class="fs-chat-close" id="fsChatClose" aria-label="Cerrar chat">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
            
            <div class="fs-chat-messages" id="fsChatMessages">
                <!-- Los mensajes se agregan aquí dinámicamente -->
            </div>
            
            <div class="fs-chat-input-container">
                <div class="fs-chat-input-wrapper">
                    <textarea 
                        id="fsChatInput" 
                        placeholder="Escribe tu pregunta..." 
                        maxlength="500"
                        autocomplete="off"
                        rows="1"
                    ></textarea>
                    <button class="fs-chat-send" id="fsChatSend" aria-label="Enviar mensaje" data-state="ready">
                        <!-- Estado normal: enviar con icono profesional -->
                        <div class="fs-send-state fs-send-ready">
                            <svg viewBox="0 0 24 24" width="20" height="20" class="fs-professional-send">
                                <!-- Círculo principal -->
                                <circle cx="12" cy="12" r="8" fill="currentColor" opacity="0.9">
                                    <animate attributeName="opacity" values="0.9;1;0.9" dur="2s" repeatCount="indefinite"/>
                                </circle>
                                
                                <!-- Chevron moderno centrado -->
                                <path fill="white" d="M10.5 9l3 3-3 3V9z" opacity="0.95" transform="translate(0.5, 0)">
                                    <animateTransform 
                                        attributeName="transform" 
                                        type="scale" 
                                        values="1;1.1;1" 
                                        dur="1.5s" 
                                        repeatCount="indefinite"
                                        additive="sum"/>
                                </path>
                                
                                <!-- Anillos de energía -->
                                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1" opacity="0.2">
                                    <animate attributeName="r" values="10;14;10" dur="2.5s" repeatCount="indefinite"/>
                                    <animate attributeName="opacity" values="0.2;0;0.2" dur="2.5s" repeatCount="indefinite"/>
                                </circle>
                                <circle cx="12" cy="12" r="12" fill="none" stroke="currentColor" stroke-width="0.5" opacity="0.1">
                                    <animate attributeName="r" values="12;16;12" dur="3s" repeatCount="indefinite" begin="0.5s"/>
                                    <animate attributeName="opacity" values="0.1;0;0.1" dur="3s" repeatCount="indefinite" begin="0.5s"/>
                                </circle>
                            </svg>
                        </div>
                        
                        <!-- Estado esperando: spinner con puntos orbitales -->
                        <div class="fs-send-state fs-send-waiting">
                            <div class="fs-orbital-spinner">
                                <div class="fs-orbital-center"></div>
                                <div class="fs-orbital-dot fs-dot-1"></div>
                                <div class="fs-orbital-dot fs-dot-2"></div>
                                <div class="fs-orbital-dot fs-dot-3"></div>
                            </div>
                        </div>
                        
                        <!-- Estado respondiendo: spinner circular giratorio -->
                        <div class="fs-send-state fs-send-responding">
                            <div class="fs-stop-container">
                                <div class="fs-stop-spinner"></div>
                            </div>
                        </div>
                    </button>
                </div>
                <div class="fs-typing-indicator" id="fsTypingIndicator" style="display: none;">
                    <span>Asistente está escribiendo</span>
                    <div class="fs-typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;

        // Agregar al widget
        this.widget.appendChild(this.chatContainer);
        
        // Configurar eventos del chat
        this.setupChatEvents();
        
        // Agregar listener para cambios de tamaño de ventana
        window.addEventListener('resize', () => this.adjustChatPosition());
    }

    /**
     * Ajustar posición del chat según el tamaño de pantalla
     */
    adjustChatPosition() {
        if (!this.chatContainer) return;
        
        const screenWidth = window.innerWidth;
        
        if (screenWidth <= 500) {
            // Pantallas muy pequeñas: modal centrado perfectamente
            this.chatContainer.style.cssText = `
                position: fixed !important; 
                top: 25px !important; 
                left: 50% !important; 
                right: auto !important; 
                bottom: auto !important; 
                width: calc(100vw - 32px) !important; 
                height: calc(100vh - 50px) !important; 
                z-index: 10000 !important;
                border-radius: 20px !important;
                transform: translateX(-50%) !important;
                border: 1px solid var(--fs-border) !important;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
                margin: 0 !important;
            `;
        } else if (screenWidth <= 576) {
            // Pantallas pequeñas: modal centrado
            this.chatContainer.style.cssText = `
                position: fixed !important; 
                top: 30px !important; 
                left: 50% !important; 
                right: auto !important; 
                bottom: auto !important; 
                width: calc(100vw - 40px) !important; 
                height: calc(100vh - 60px) !important; 
                z-index: 10000 !important;
                border-radius: 16px !important;
                max-height: calc(100vh - 60px) !important;
                border: 1px solid var(--fs-border) !important;
                transform: translateX(-50%) !important;
                margin: 0 !important;
            `;
        } else if (screenWidth >= 580 && screenWidth <= 750) {
            // RANGO PROBLEMÁTICO 580px-750px: Solución optimizada
            this.chatContainer.style.cssText = `
                position: fixed !important; 
                top: 60px !important; 
                left: 50% !important; 
                right: auto !important; 
                bottom: auto !important; 
                width: calc(100vw - 80px) !important; 
                height: calc(100vh - 120px) !important; 
                z-index: 10000 !important;
                border-radius: 16px !important;
                max-height: calc(100vh - 120px) !important;
                border: 1px solid var(--fs-border) !important;
                transform: translateX(-50%) !important;
                margin: 0 !important;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
            `;
        } else if (screenWidth <= 768) {
            // Rango general: resto de tablets
            this.chatContainer.style.cssText = `
                position: fixed !important; 
                top: 40px !important; 
                left: 50% !important; 
                right: auto !important; 
                bottom: auto !important; 
                width: calc(100vw - 50px) !important; 
                height: calc(100vh - 80px) !important; 
                z-index: 10000 !important;
                border-radius: 16px !important;
                max-height: calc(100vh - 80px) !important;
                border: 1px solid var(--fs-border) !important;
                transform: translateX(-50%) !important;
                margin: 0 !important;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12) !important;
            `;
        } else {
            // Pantallas normales: pegado a la derecha
            this.chatContainer.style.cssText = `
                position: fixed !important; 
                bottom: 90px !important; 
                right: 10px !important; 
                z-index: 10000 !important;
                transform: none !important;
            `;
        }
    }

    /**
     * Configurar eventos del chat
     */
    setupChatEvents() {
        // Botón cerrar
        const closeBtn = this.chatContainer.querySelector('#fsChatClose');
        closeBtn.addEventListener('click', () => this.closeChat());
        
        // Textarea y botón enviar
        const input = this.chatContainer.querySelector('#fsChatInput');
        const sendBtn = this.chatContainer.querySelector('#fsChatSend');
        
        const handleSendAction = () => {
            const state = sendBtn.getAttribute('data-state');
            
            if (state === 'ready') {
                // Enviar mensaje normal
                const message = input.value.trim();
                if (message && !this.isBotResponding) {
                    this.sendMessage(message);
                }
            } else if (state === 'responding') {
                // Cancelar respuesta del bot
                this.cancelBotResponse();
            }
            // No hacer nada si está en estado 'waiting'
        };
        
        // Auto-resize del textarea
        input.addEventListener('input', () => {
            this.autoResizeTextarea(input);
        });
        
        sendBtn.addEventListener('click', handleSendAction);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                // Solo permitir enviar si el bot no está respondiendo
                if (!this.isBotResponding) {
                    handleSendAction();
                } 
            }
        });
        
        // Auto-focus en el input
        input.focus();
    }

    /**
     * Mostrar interfaz del chat
     */
    showChatInterface() {
        // 🔧 FIX: Calcular ancho del scrollbar antes de ocultar overflow
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        
        // Solo compensar si hay scrollbar visible
        if (scrollbarWidth > 0) {
            document.body.style.paddingRight = `${scrollbarWidth}px`;
            // Guardar el valor para restaurarlo después
            document.body.setAttribute('data-scrollbar-width', scrollbarWidth);
        }
        
        this.chatContainer.classList.add('fs-visible');
        document.body.classList.add('fs-chat-open');
    }

    /**
     * Cerrar chat
     */
    closeChat() {
        if (!this.isOpen) return;
        
        // Cambiar estado
        this.isOpen = false;
        
        // Actualizar UI
        const button = this.widget.querySelector('#fsChatButton');
        button.classList.remove('fs-active');
        
        if (this.chatContainer) {
            this.chatContainer.classList.remove('fs-visible');
        }
        
        // 🔧 FIX: Remover el padding agregado cuando se abrió el chat
        document.body.style.paddingRight = '';
        document.body.removeAttribute('data-scrollbar-width');
        
        document.body.classList.remove('fs-chat-open');
    }

    /**
     * Agregar mensaje de bienvenida
     */
    addWelcomeMessage() {
        const welcomeMsg = `¡Hola! 👋 Soy tu asistente virtual de Fashion Store. 

Puedo ayudarte con:
• 🛍️ Productos y catálogo
• 📏 Guía de tallas  
• 🔥 Ofertas especiales
• 📦 Información de envíos
• 🕒 Horarios de atención

¿En qué puedo ayudarte hoy?`;

        this.addBotMessage(welcomeMsg, true);
        this.addQuickSuggestions();
    }

    /**
     * Agregar sugerencias rápidas
     */
    addQuickSuggestions() {
        const suggestions = [
            { text: "Ver ofertas 🔥", query: "ofertas" },
            { text: "Guía de tallas 📏", query: "tallas" },
            { text: "Envíos 📦", query: "envios" },
            { text: "Horarios 🕒", query: "horarios" }
        ];

        const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
        const suggestionsDiv = document.createElement('div');
        suggestionsDiv.className = 'fs-quick-suggestions';
        
        suggestions.forEach(suggestion => {
            const btn = document.createElement('button');
            btn.className = 'fs-suggestion-btn';
            btn.textContent = suggestion.text;
            btn.addEventListener('click', () => {
                this.sendMessage(suggestion.query);
                suggestionsDiv.remove(); // Remover sugerencias después de usar
            });
            suggestionsDiv.appendChild(btn);
        });
        
        messagesContainer.appendChild(suggestionsDiv);
        this.forceScrollToBottom(); // Forzar scroll para sugerencias iniciales
    }

    /**
     * Enviar mensaje
     */
    async sendMessage(message) {
        if (!message || this.isBotResponding) return;
        
        // Marcar que el bot está procesando
        this.isBotResponding = true;
        
        // Limpiar input pero NO deshabilitarlo
        const input = this.chatContainer.querySelector('#fsChatInput');
        const inputWrapper = this.chatContainer.querySelector('.fs-chat-input-wrapper');
        input.value = '';
        // NO deshabilitar el input, solo agregar clase visual
        inputWrapper.classList.add('fs-bot-responding');
        
        // Cambiar estado del botón a esperando
        this.setSendButtonState('waiting');
        
        // Agregar mensaje del usuario
        this.addUserMessage(message);
        
        // Mostrar indicador de escritura
        this.showTypingIndicator(true);
        
        try {
            // Crear AbortController para poder cancelar
            this.abortController = new AbortController();
            
            // Enviar al bot
            const response = await this.sendToBot(message, this.abortController.signal);
            
            // Verificar si no fue cancelado
            if (!this.abortController.signal.aborted) {
                // Ocultar indicador
                this.showTypingIndicator(false);
                
                // Cambiar estado del botón a respondiendo
                this.setSendButtonState('responding');
                
                // Agregar respuesta del bot con efecto de escritura
                await this.addBotMessage(response);
                
                // Restaurar estado normal
                this.resetSendButton();
            }
            
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.showTypingIndicator(false);
                this.addBotMessage('Disculpa, ha ocurrido un error. Por favor intenta de nuevo.');
                this.resetSendButton();
            }
        }
    }

    /**
     * Enviar mensaje al bot
     */
    async sendToBot(message, signal) {
        const response = await fetch(this.chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message }),
            signal: signal
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        return data.response;
    }

    /**
     * Agregar mensaje del usuario
     */
    addUserMessage(message) {
        const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'fs-message fs-user-message';
        messageDiv.innerHTML = `
            <div class="fs-message-content">
                <p>${this.escapeHtml(message)}</p>
                <span class="fs-message-time">${this.getTimeString()}</span>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        this.messageHistory.push({ type: 'user', message, timestamp: new Date() });
        this.forceScrollToBottom(); // Forzar scroll al enviar mensaje
    }

    /**
     * Agregar mensaje del bot con efecto de escritura
     */
    async addBotMessage(message, instant = false) {
        const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'fs-message fs-bot-message';
        messageDiv.innerHTML = `
            <div class="fs-message-avatar">
                <img src="public/assets/img/logomarca_32.png" alt="Logo Fashion Store">
            </div>
            <div class="fs-message-content">
                <div class="fs-message-text"></div>
                <span class="fs-message-time">${this.getTimeString()}</span>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        this.currentBotMessage = messageDiv; // Guardar referencia
        this.messageHistory.push({ type: 'bot', message, timestamp: new Date() });
        
        // Efecto de escritura o mostrar instantáneamente
        const textElement = messageDiv.querySelector('.fs-message-text');
        if (instant) {
            textElement.innerHTML = this.formatMessage(message);
        } else {
            await this.typeWriterEffect(textElement, message);
        }
        
        this.scrollToBottom();
    }

    /**
     * Efecto de máquina de escribir - Mejorado para mostrar formato progresivamente
     */
    async typeWriterEffect(element, text) {
        // Limpiar y formatear el texto antes de procesarlo
        const cleanText = text.trim();
        const formattedText = this.formatMessage(cleanText);
        element.innerHTML = '';
        
        // Crear estructura inicial para mantener formato
        let currentHtml = '';
        let i = 0;
        
        // Convertir HTML a array de caracteres manteniendo tags
        const htmlParts = this.parseHtmlForTyping(formattedText);
        
        return new Promise((resolve, reject) => {
            this.typeInterval = setInterval(() => {
                // Verificar si fue cancelado
                if (this.abortController && this.abortController.signal.aborted) {
                    clearInterval(this.typeInterval);
                    reject(new Error('Typing cancelled'));
                    return;
                }
                
                if (i < htmlParts.length) {
                    const part = htmlParts[i];
                    
                    if (part.type === 'tag') {
                        // Agregar tags HTML inmediatamente
                        currentHtml += part.content;
                    } else {
                        // Agregar caracteres de texto uno por uno
                        currentHtml += part.content;
                    }
                    
                    // Actualizar el contenido del elemento
                    element.innerHTML = currentHtml;
                    i++;
                    this.scrollToBottom();
                } else {
                    clearInterval(this.typeInterval);
                    // Asegurar que el contenido final sea correcto
                    element.innerHTML = formattedText;
                    resolve();
                }
            }, this.config.typingSpeed); // Usar velocidad configurable
        });
    }

    /**
     * Parsear HTML para efecto de escritura manteniendo formato
     */
    parseHtmlForTyping(html) {
        const parts = [];
        let currentPos = 0;
        let openTags = [];
        
        // Regex para encontrar tags HTML
        const tagRegex = /<\/?[^>]+>/g;
        let match;
        
        while ((match = tagRegex.exec(html)) !== null) {
            // Agregar texto antes del tag
            if (match.index > currentPos) {
                const textBefore = html.substring(currentPos, match.index);
                for (let char of textBefore) {
                    parts.push({
                        type: 'char',
                        content: char
                    });
                }
            }
            
            const tag = match[0];
            const isClosingTag = tag.startsWith('</');
            const tagName = tag.replace(/[<>\/]/g, '').split(' ')[0];
            
            if (isClosingTag) {
                // Tag de cierre - añadir inmediatamente
                parts.push({
                    type: 'tag',
                    content: tag
                });
                // Remover de openTags
                const index = openTags.lastIndexOf(tagName);
                if (index > -1) {
                    openTags.splice(index, 1);
                }
            } else {
                // Tag de apertura - añadir inmediatamente
                parts.push({
                    type: 'tag',
                    content: tag
                });
                // Agregar a openTags si no es self-closing
                if (!tag.endsWith('/>') && !['br', 'hr', 'img'].includes(tagName)) {
                    openTags.push(tagName);
                }
            }
            
            currentPos = match.index + match[0].length;
        }
        
        // Agregar texto restante
        if (currentPos < html.length) {
            const remainingText = html.substring(currentPos);
            for (let char of remainingText) {
                parts.push({
                    type: 'char',
                    content: char
                });
            }
        }
        
        return parts;
    }

    /**
     * Crear fragmentos de texto preservando HTML - Mejorado para saltos de línea
     */
    createTextFragments(html) {
        const fragments = [];
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        const processNode = (node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                // Dividir texto en caracteres individuales, preservando espacios y estructura
                const text = node.textContent;
                for (let i = 0; i < text.length; i++) {
                    fragments.push({
                        type: 'text',
                        content: text[i]
                    });
                }
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                const tagName = node.tagName.toLowerCase();
                
                // Manejar tags especiales como <br>
                if (tagName === 'br') {
                    fragments.push({
                        type: 'html',
                        content: '<br>'
                    });
                } else {
                    // Añadir tag de apertura para otros elementos
                    const attrs = Array.from(node.attributes)
                        .map(attr => `${attr.name}="${attr.value}"`)
                        .join(' ');
                    const openTag = attrs ? `<${tagName} ${attrs}>` : `<${tagName}>`;
                    
                    fragments.push({
                        type: 'html',
                        content: openTag
                    });
                    
                    // Procesar hijos
                    for (let child of node.childNodes) {
                        processNode(child);
                    }
                    
                    // Añadir tag de cierre
                    fragments.push({
                        type: 'html',
                        content: `</${tagName}>`
                    });
                }
            }
        };
        
        for (let child of tempDiv.childNodes) {
            processNode(child);
        }
        
        return fragments;
    }

    /**
     * Añadir contenido al elemento preservando formato - Mejorado
     */
    appendToElement(element, content) {
        element.innerHTML += content;
        // Forzar re-renderizado para que los estilos se apliquen inmediatamente
        element.offsetHeight;
    }

    /**
     * Formatear mensaje con markdown básico - Mejorado para mejor procesamiento
     */
    formatMessage(text) {
        // Normalizar saltos de línea y evitar duplicados
        let formatted = text
            .replace(/\r\n/g, '\n') // Normalizar Windows line endings
            .replace(/\r/g, '\n')   // Normalizar Mac line endings
            .replace(/\n\s*\n\s*\n/g, '\n\n') // Máximo dos saltos consecutivos
            .replace(/\n{3,}/g, '\n\n') // Limitar saltos múltiples
            .trim(); // Remover espacios al inicio y final
        
        // Aplicar formato markdown
        formatted = formatted
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Negritas
            .replace(/\*(.*?)\*/g, '<em>$1</em>') // Cursivas
            .replace(/`(.*?)`/g, '<code>$1</code>') // Código inline
            .replace(/\n/g, '<br>'); // Saltos de línea
        
        return formatted;
    }

    /**
     * Mostrar/ocultar indicador de escritura
     */
    showTypingIndicator(show) {
        const indicator = this.chatContainer.querySelector('#fsTypingIndicator');
        // Siempre oculto - indicador deshabilitado
        indicator.style.display = 'none';
        // if (show) this.scrollToBottom();
    }

    /**
     * Scroll suave al final - Mejorado para no interrumpir al usuario
     */
    scrollToBottom() {
        const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
        
        // Verificar si el usuario está cerca del final (dentro de los últimos 100px)
        const isNearBottom = messagesContainer.scrollTop + messagesContainer.clientHeight >= 
                            messagesContainer.scrollHeight - 100;
        
        // Solo hacer scroll automático si el usuario está cerca del final
        if (isNearBottom) {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        }
    }

    /**
     * Forzar scroll al final (para casos específicos como envío de mensajes)
     */
    forceScrollToBottom() {
        const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    }

    /**
     * Obtener hora actual formateada
     */
    getTimeString() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    /**
     * Escapar HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Auto-redimensionar textarea
     */
    autoResizeTextarea(textarea) {
        // Resetear altura para obtener scrollHeight correcto
        textarea.style.height = 'auto';
        
        // Calcular nueva altura basada en contenido
        const scrollHeight = textarea.scrollHeight;
        const lineHeight = parseInt(window.getComputedStyle(textarea).lineHeight);
        const maxLines = 4; // Máximo 4 líneas
        const maxHeight = lineHeight * maxLines;
        
        // Aplicar nueva altura (mínimo 1 línea, máximo 4 líneas)
        const newHeight = Math.min(scrollHeight, maxHeight);
        textarea.style.height = newHeight + 'px';
        
        // Ajustar scroll si excede el máximo
        if (scrollHeight > maxHeight) {
            textarea.style.overflowY = 'auto';
        } else {
            textarea.style.overflowY = 'hidden';
        }
    }

    /**
     * Mostrar error
     */
    showError(message) {
        // Crear notificación de error
        const notification = document.createElement('div');
        notification.className = 'fs-error-notification';
        notification.innerHTML = `
            <div class="fs-error-content">
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    /**
     * Cambiar estado del botón de envío
     */
    setSendButtonState(state) {
        if (!this.chatContainer) return;
        
        const sendBtn = this.chatContainer.querySelector('#fsChatSend');
        if (!sendBtn) return;
        
        sendBtn.setAttribute('data-state', state);
        
        // Actualizar aria-label según el estado
        const labels = {
            'ready': 'Enviar mensaje',
            'waiting': 'Esperando respuesta...',
            'responding': 'Cancelar respuesta'
        };
        
        sendBtn.setAttribute('aria-label', labels[state] || labels.ready);
    }

    /**
     * Restaurar botón de envío al estado normal
     */
    resetSendButton() {
        this.isBotResponding = false;
        this.currentBotMessage = null;
        this.abortController = null;
        
        // Restaurar input wrapper y remover clase visual
        const input = this.chatContainer.querySelector('#fsChatInput');
        const inputWrapper = this.chatContainer.querySelector('.fs-chat-input-wrapper');
        if (input && inputWrapper) {
            inputWrapper.classList.remove('fs-bot-responding');
            input.focus();
        }
        
        // Restaurar estado del botón
        this.setSendButtonState('ready');
    }

    /**
     * Cancelar respuesta del bot
     */
    cancelBotResponse() {
        
        // Cancelar petición HTTP
        if (this.abortController) {
            this.abortController.abort();
        }
        
        // Detener efecto de escritura
        if (this.typeInterval) {
            clearInterval(this.typeInterval);
        }
        
        // Eliminar último mensaje del bot si existe
        if (this.currentBotMessage) {
            this.currentBotMessage.remove();
            
            // Eliminar del historial también
            if (this.messageHistory.length > 0 && 
                this.messageHistory[this.messageHistory.length - 1].type === 'bot') {
                this.messageHistory.pop();
            }
        }
        
        // Ocultar indicador de escritura
        this.showTypingIndicator(false);
        
        // Restaurar estado normal
        this.resetSendButton();
        
    }


    
    /**
     * Mostrar mensaje de que el bot se está iniciando
     */
    showBotStartingMessage(customMessage = null) {
        if (!this.chatContainer) return;
        
        const messagesContainer = this.chatContainer.querySelector('#fsChatMessages');
        
        // Limpiar mensajes anteriores
        messagesContainer.innerHTML = '';
        
        const startingMsg = customMessage || `🚀 Iniciando asistente virtual...

Por favor espera unos segundos mientras iniciamos el servidor del bot.

⏳ Esto puede tardar entre 5-15 segundos la primera vez.

🔄 El sistema se está preparando para atenderte.`;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'fs-message fs-bot-message fs-starting-message';
        messageDiv.innerHTML = `
            <div class="fs-message-avatar">
                <div class="fs-loading-spinner"></div>
            </div>
            <div class="fs-message-content">
                <div class="fs-message-text">${this.formatMessage(startingMsg)}</div>
                <span class="fs-message-time">${this.getTimeString()}</span>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.fashionStoreChat = new FashionStoreChatWidget();
});

// Exponer globalmente para debugging
window.FashionStoreChatWidget = FashionStoreChatWidget;
