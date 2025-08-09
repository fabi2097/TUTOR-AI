<?php
/**
 * Chat Widget Template - Modern Interface
 * Inspirado en los diseños widget.html e index.html
 * 
 * @package TutorAI
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener configuraciones del plugin
$settings = get_option('tutor_ai_settings', []);
$widget_position = $settings['widget_position'] ?? 'bottom-right';
$bot_name = $settings['bot_name'] ?? 'Asistente de Cursos';
$welcome_message = $settings['welcome_message'] ?? '¡Hola! Soy tu asistente de cursos. ¿En qué puedo ayudarte hoy?';
$widget_enabled = $settings['chat_enabled'] ?? true;
$widget_color = $settings['widget_color'] ?? '#2563eb';

if (!$widget_enabled) {
    return;
}

// Determinar posicionamiento
$is_left = strpos($widget_position, 'left') !== false;
$position_class = $is_left ? 'tutor-ai-left' : 'tutor-ai-right';
?>

<style>
:root {
    --tutor-ai-brand: <?php echo esc_attr($widget_color); ?>;
    --tutor-ai-brand-600: color-mix(in srgb, <?php echo esc_attr($widget_color); ?> 85%, black);
    --tutor-ai-bg: #ffffff;
    --tutor-ai-text: #0f172a;
    --tutor-ai-muted: #64748b;
    --tutor-ai-surface: #f8fafc;
    --tutor-ai-border: #e2e8f0;
    --tutor-ai-gap: 24px;
    --tutor-ai-shadow: 0 10px 24px rgba(15,23,42,.25);
    --tutor-ai-shadow-sm: 0 4px 14px rgba(0,0,0,.25);
}

/* ===== Botón Flotante (FAB) ===== */
#tutorAIFab {
    position: fixed;
    bottom: var(--tutor-ai-gap);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--tutor-ai-brand);
    box-shadow: var(--tutor-ai-shadow-sm);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: transform .15s ease, box-shadow .15s ease;
    z-index: 999999;
    border: none;
    outline: none;
}

#tutorAIFab:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,.28);
}

#tutorAIFab svg {
    width: 28px;
    height: 28px;
    fill: white;
}

/* Posicionamiento */
.tutor-ai-right #tutorAIFab {
    right: var(--tutor-ai-gap);
}

.tutor-ai-left #tutorAIFab {
    left: var(--tutor-ai-gap);
}

/* ===== Ventana de Chat ===== */
#tutorAIWindow {
    position: fixed;
    bottom: calc(var(--tutor-ai-gap) + 70px);
    width: 380px;
    max-width: 92vw;
    height: 550px;
    background: var(--tutor-ai-bg);
    border-radius: 16px;
    box-shadow: var(--tutor-ai-shadow);
    overflow: hidden;
    display: none;
    flex-direction: column;
    z-index: 999998;
    border: 1px solid var(--tutor-ai-border);
}

.tutor-ai-right #tutorAIWindow {
    right: var(--tutor-ai-gap);
}

.tutor-ai-left #tutorAIWindow {
    left: var(--tutor-ai-gap);
}

/* Header */
#tutorAIHeader {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #fff;
    border-bottom: 1px solid var(--tutor-ai-border);
    font-weight: 600;
}

#tutorAIHeader .brand {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

#tutorAIHeader .avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--tutor-ai-brand), var(--tutor-ai-brand-600));
    display: grid;
    place-items: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

#tutorAIHeader h3 {
    margin: 0;
    font-size: 16px;
    color: var(--tutor-ai-text);
}

#tutorAIHeader .status {
    font-size: 12px;
    color: var(--tutor-ai-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}

#tutorAIHeader .status::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

#tutorAICloseBtn {
    border: none;
    background: transparent;
    font-size: 20px;
    cursor: pointer;
    line-height: 1;
    padding: 4px;
    color: var(--tutor-ai-muted);
    border-radius: 4px;
    transition: background .15s ease;
}

#tutorAICloseBtn:hover {
    background: var(--tutor-ai-surface);
    color: var(--tutor-ai-text);
}

/* Mensajes */
#tutorAIMessages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: linear-gradient(180deg, #fff 0%, #f9fbff 100%);
    scroll-behavior: smooth;
}

/* Welcome Screen */
.tutor-ai-welcome {
    max-width: 300px;
    margin: 60px auto;
    text-align: center;
    padding: 32px 20px;
    border: 1px dashed var(--tutor-ai-border);
    border-radius: 16px;
    background: #fff;
}

.tutor-ai-welcome .orb {
    width: 68px;
    height: 68px;
    margin: 0 auto 14px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: radial-gradient(closest-side, #dbeafe, transparent 60%),
                conic-gradient(from 0deg, var(--tutor-ai-brand) 0 40%, #93c5fd 40% 70%, var(--tutor-ai-brand-600) 70% 100%);
    -webkit-mask: radial-gradient(circle at center, transparent 30px, #000 31px);
    mask: radial-gradient(circle at center, transparent 30px, #000 31px);
}

.tutor-ai-welcome h2 {
    margin: 0 0 6px;
    font-size: 20px;
    color: var(--tutor-ai-text);
}

.tutor-ai-welcome p {
    margin: 0;
    color: var(--tutor-ai-muted);
    font-size: 14px;
    line-height: 1.4;
}

/* Mensajes */
.tutor-ai-message {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    margin: 12px 0;
}

.tutor-ai-message.user {
    justify-content: flex-end;
}

.tutor-ai-message .avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #dbeafe;
    flex: 0 0 28px;
    border: 1px solid var(--tutor-ai-border);
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: bold;
    color: var(--tutor-ai-brand);
}

.tutor-ai-message.user .avatar {
    background: var(--tutor-ai-brand);
    color: white;
}

.tutor-ai-bubble {
    max-width: 80%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid var(--tutor-ai-border);
    background: #fff;
    line-height: 1.4;
    box-shadow: 0 2px 6px rgba(2,6,23,.04);
    position: relative;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tutor-ai-message.user .tutor-ai-bubble {
    background: #eaf2ff;
    border-color: #c7dbff;
}

.tutor-ai-message.ai .tutor-ai-bubble {
    border-left: 3px solid var(--tutor-ai-brand);
}

/* Typing indicator */
.tutor-ai-typing {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    margin: 12px 0;
}

.tutor-ai-typing .avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #dbeafe;
    flex: 0 0 28px;
    border: 1px solid var(--tutor-ai-border);
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: bold;
    color: var(--tutor-ai-brand);
}

.tutor-ai-typing .bubble {
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid var(--tutor-ai-border);
    background: #fff;
    border-left: 3px solid var(--tutor-ai-brand);
    display: flex;
    gap: 4px;
    align-items: center;
}

.tutor-ai-typing .dots {
    display: flex;
    gap: 3px;
}

.tutor-ai-typing .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--tutor-ai-muted);
    animation: typing 1.4s infinite;
}

.tutor-ai-typing .dot:nth-child(2) {
    animation-delay: 0.2s;
}

.tutor-ai-typing .dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.4;
    }
    30% {
        transform: translateY(-10px);
        opacity: 1;
    }
}

/* Sugerencias */
.tutor-ai-suggestions {
    padding: 0 20px 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tutor-ai-suggestion {
    background: white;
    border: 1px solid var(--tutor-ai-border);
    border-radius: 20px;
    padding: 8px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all .15s ease;
    color: var(--tutor-ai-text);
}

.tutor-ai-suggestion:hover {
    background: var(--tutor-ai-surface);
    border-color: var(--tutor-ai-brand);
}

/* Composer */
#tutorAIComposer {
    padding: 12px;
    border-top: 1px solid var(--tutor-ai-border);
    background: #fff;
}

#tutorAIForm {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    background: var(--tutor-ai-surface);
    border: 1px solid var(--tutor-ai-border);
    border-radius: 12px;
    padding: 8px;
    transition: border-color .15s ease;
}

#tutorAIForm:focus-within {
    border-color: var(--tutor-ai-brand);
}

#tutorAIInput {
    flex: 1;
    resize: none;
    border: 0;
    outline: 0;
    background: transparent;
    padding: 6px 8px;
    max-height: 120px;
    font-family: inherit;
    font-size: 14px;
    color: var(--tutor-ai-text);
    line-height: 1.4;
}

#tutorAIInput::placeholder {
    color: var(--tutor-ai-muted);
}

#tutorAISendBtn {
    border: 0;
    cursor: pointer;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 700;
    background: var(--tutor-ai-brand);
    color: #fff;
    transition: all .15s ease;
    font-size: 14px;
}

#tutorAISendBtn:hover:not(:disabled) {
    background: var(--tutor-ai-brand-600);
}

#tutorAISendBtn:active {
    transform: translateY(1px);
}

#tutorAISendBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.tutor-ai-footer {
    padding: 8px 12px;
    text-align: center;
    font-size: 11px;
    color: var(--tutor-ai-muted);
    background: var(--tutor-ai-surface);
}

/* Responsive */
@media (max-width: 480px) {
    #tutorAIWindow {
        width: 100vw;
        height: 100vh;
        bottom: 0;
        left: 0 !important;
        right: 0 !important;
        border-radius: 0;
        max-width: none;
    }
    
    .tutor-ai-bubble {
        max-width: 90%;
    }
    
    .tutor-ai-welcome {
        margin: 30px auto;
        padding: 20px;
    }
}

/* Scrollbar personalizada */
#tutorAIMessages::-webkit-scrollbar {
    width: 4px;
}

#tutorAIMessages::-webkit-scrollbar-track {
    background: transparent;
}

#tutorAIMessages::-webkit-scrollbar-thumb {
    background: var(--tutor-ai-border);
    border-radius: 2px;
}

#tutorAIMessages::-webkit-scrollbar-thumb:hover {
    background: var(--tutor-ai-muted);
}

/* Animaciones para mostrar/ocultar */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes fadeOut {
    from { opacity: 1; transform: translateY(0) scale(1); }
    to { opacity: 0; transform: translateY(20px) scale(0.95); }
}

#tutorAIWindow.show {
    animation: fadeIn 0.2s ease;
}

#tutorAIWindow.hide {
    animation: fadeOut 0.2s ease;
}
</style>

<div class="<?php echo esc_attr($position_class); ?>">
    <!-- Botón Flotante -->
    <button id="tutorAIFab" title="<?php echo esc_attr($bot_name); ?>" aria-label="Abrir chat con asistente">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 2H4C2.9 2 2 2.9 2 4V16C2 17.1 2.9 18 4 18H18L22 22V4C22 2.9 21.1 2 20 2ZM20 17.17L18.83 16H4V4H20V17.17Z"/>
            <path d="M7 9H17V11H7V9ZM7 12H14V14H7V12Z"/>
        </svg>
    </button>

    <!-- Ventana de Chat -->
    <div id="tutorAIWindow" role="dialog" aria-labelledby="tutorAITitle" aria-hidden="true">
        <!-- Header -->
        <div id="tutorAIHeader">
            <div class="brand">
                <div class="avatar"><?php echo esc_html(mb_substr($bot_name, 0, 2)); ?></div>
                <div>
                    <h3 id="tutorAITitle"><?php echo esc_html($bot_name); ?></h3>
                    <div class="status">En línea</div>
                </div>
            </div>
            <button id="tutorAICloseBtn" aria-label="Cerrar chat">&times;</button>
        </div>

        <!-- Mensajes -->
        <div id="tutorAIMessages" aria-live="polite">
            <div class="tutor-ai-welcome">
                <div class="orb"></div>
                <h2>¡Bienvenido!</h2>
                <p><?php echo esc_html($welcome_message); ?></p>
            </div>
        </div>

        <!-- Sugerencias -->
        <div class="tutor-ai-suggestions">
            <button class="tutor-ai-suggestion" data-message="¿Qué cursos me recomiendas?">Recomendar cursos</button>
            <button class="tutor-ai-suggestion" data-message="¿Cuál es mi progreso?">Ver progreso</button>
            <button class="tutor-ai-suggestion" data-message="¿Cómo obtengo certificado?">Certificados</button>
        </div>

        <!-- Composer -->
        <div id="tutorAIComposer">
            <form id="tutorAIForm">
                <textarea 
                    id="tutorAIInput" 
                    rows="1" 
                    placeholder="Escribe tu pregunta... (Enter para enviar)"
                    aria-label="Escribe tu mensaje"
                ></textarea>
                <button id="tutorAISendBtn" type="submit" aria-label="Enviar mensaje">
                    Enviar
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="tutor-ai-footer">
            Powered by AI • <?php echo esc_html($bot_name); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const TutorAIChat = {
        // Elementos DOM
        fab: document.getElementById('tutorAIFab'),
        window: document.getElementById('tutorAIWindow'),
        closeBtn: document.getElementById('tutorAICloseBtn'),
        form: document.getElementById('tutorAIForm'),
        input: document.getElementById('tutorAIInput'),
        sendBtn: document.getElementById('tutorAISendBtn'),
        messages: document.getElementById('tutorAIMessages'),
        suggestions: document.querySelectorAll('.tutor-ai-suggestion'),
        
        // Estado
        isOpen: false,
        isTyping: false,
        
        // Configuración desde PHP
        config: {
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('tutor_ai_chat'); ?>',
            botName: '<?php echo esc_js($bot_name); ?>'
        },

        init() {
            this.bindEvents();
            this.setupAutoResize();
            this.setupSuggestions();
        },

        bindEvents() {
            // Toggle chat
            this.fab.addEventListener('click', () => this.openChat());
            this.closeBtn.addEventListener('click', () => this.closeChat());
            
            // Envío de mensajes
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            
            // Enter para enviar (Shift+Enter para nueva línea)
            this.input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.form.requestSubmit();
                }
            });
            
            // Cerrar con Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeChat();
                }
            });

            // Habilitar/deshabilitar botón enviar
            this.input.addEventListener('input', () => {
                this.sendBtn.disabled = !this.input.value.trim();
            });
        },

        setupAutoResize() {
            const autoResize = () => {
                this.input.style.height = 'auto';
                this.input.style.height = Math.min(this.input.scrollHeight, 120) + 'px';
            };
            
            this.input.addEventListener('input', autoResize);
            autoResize();
        },

        setupSuggestions() {
            this.suggestions.forEach(btn => {
                btn.addEventListener('click', () => {
                    const message = btn.dataset.message;
                    this.input.value = message;
                    this.input.style.height = 'auto';
                    this.sendBtn.disabled = false;
                    this.input.focus();
                    
                    // Ocultar sugerencias después del primer uso
                    document.querySelector('.tutor-ai-suggestions').style.display = 'none';
                });
            });
        },

        openChat() {
            this.isOpen = true;
            this.window.style.display = 'flex';
            this.window.classList.add('show');
            this.fab.style.display = 'none';
            this.window.setAttribute('aria-hidden', 'false');
            
            // Focus en el input
            setTimeout(() => this.input.focus(), 100);
        },

        closeChat() {
            this.window.classList.add('hide');
            
            setTimeout(() => {
                this.isOpen = false;
                this.window.style.display = 'none';
                this.window.classList.remove('show', 'hide');
                this.fab.style.display = 'grid';
                this.window.setAttribute('aria-hidden', 'true');
            }, 200);
        },

        async handleSubmit(e) {
            e.preventDefault();
            
            const message = this.input.value.trim();
            if (!message || this.isTyping) return;
            
            // Agregar mensaje del usuario
            this.addMessage(message, 'user');
            
            // Limpiar input
            this.input.value = '';
            this.input.style.height = 'auto';
            this.sendBtn.disabled = true;
            
            // Ocultar sugerencias
            const suggestions = document.querySelector('.tutor-ai-suggestions');
            if (suggestions) suggestions.style.display = 'none';
            
            // Mostrar typing indicator
            this.showTyping();
            
            try {
                // Enviar a la API
                const response = await this.sendToAPI(message);
                
                // Remover typing
                this.hideTyping();
                
                // Agregar respuesta
                this.addMessage(response, 'ai');
                
            } catch (error) {
                console.error('Error en chat:', error);
                this.hideTyping();
                this.addMessage('Lo siento, ocurrió un error. Por favor intenta de nuevo.', 'ai');
            } finally {
                this.input.focus();
            }
        },

        async sendToAPI(message) {
            const formData = new FormData();
            formData.append('action', 'tutor_ai_chat');
            formData.append('message', message);
            formData.append('nonce', this.config.nonce);

            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data || 'Error desconocido');
            }

            return data.data;
        },

        addMessage(text, type) {
            // Remover welcome si existe
            const welcome = this.messages.querySelector('.tutor-ai-welcome');
            if (welcome) {
                welcome.remove();
            }

            const messageEl = document.createElement('div');
            messageEl.className = `tutor-ai-message ${type}`;

            let avatarHtml = '';
            if (type === 'ai') {
                avatarHtml = `<div class="avatar">${this.config.botName.substring(0, 2)}</div>`;
            } else {
                avatarHtml = `<div class="avatar">TÚ</div>`;
            }

            messageEl.innerHTML = `
                ${type === 'ai' ? avatarHtml : ''}
                <div class="tutor-ai-bubble">${this.escapeHtml(text)}</div>
                ${type === 'user' ? avatarHtml : ''}
            `;

            this.messages.appendChild(messageEl);
            this.scrollToBottom();
        },

        showTyping() {
            this.isTyping = true;
            
            const typingEl = document.createElement('div');
            typingEl.className = 'tutor-ai-typing';
            typingEl.id = 'tutorAITyping';
            
            typingEl.innerHTML = `
                <div class="avatar">${this.config.botName.substring(0, 2)}</div>
                <div class="bubble">
                    <div class="dots">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                </div>
            `;
            
            this.messages.appendChild(typingEl);
            this.scrollToBottom();
        },

        hideTyping() {
            this.isTyping = false;
            const typing = document.getElementById('tutorAITyping');
            if (typing) {
                typing.remove();
            }
        },

        scrollToBottom() {
            this.messages.scrollTop = this.messages.scrollHeight;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Inicializar chat
    TutorAIChat.init();

    // Exponer globalmente para debugging
    window.TutorAIChat = TutorAIChat;
});
</script>
?>

<div id="tutor-ai-chat-widget" class="tutor-ai-chat-widget <?php echo esc_attr($chat_position); ?>" style="--tutor-ai-color: <?php echo esc_attr($chat_color); ?>">
    <!-- Chat Toggle Button -->
    <button id="tutor-ai-chat-toggle" class="tutor-ai-chat-toggle" aria-label="<?php esc_attr_e('Open chat assistant', 'tutor-ai'); ?>">
        <svg class="tutor-ai-icon-chat" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 2H4C2.9 2 2 2.9 2 4V16C2 17.1 2.9 18 4 18H18L22 22V4C22 2.9 21.1 2 20 2ZM20 17.17L18.83 16H4V4H20V17.17Z" fill="currentColor"/>
            <path d="M7 9H17V11H7V9ZM7 12H14V14H7V12Z" fill="currentColor"/>
        </svg>
        <svg class="tutor-ai-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="currentColor"/>
        </svg>
        <span class="tutor-ai-notification-badge" style="display: none;">1</span>
    </button>

    <!-- Chat Window -->
    <div id="tutor-ai-chat-window" class="tutor-ai-chat-window">
        <!-- Header -->
        <div class="tutor-ai-chat-header">
            <div class="tutor-ai-chat-avatar">
                <div class="tutor-ai-avatar-circle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 7.5V4C15 1.79 13.21 0 11 0S7 1.79 7 4V7.5L1 7V9L7 9.5V15H5V17H7V19C7 20.1 7.9 21 9 21S11 20.1 11 19V17H13V19C13 20.1 13.9 21 15 21S17 20.1 17 19V17H19V15H17V9.5L21 9Z" fill="currentColor"/>
                    </svg>
                </div>
            </div>
            <div class="tutor-ai-chat-info">
                <div class="tutor-ai-chat-title"><?php echo esc_html($bot_name); ?></div>
                <div class="tutor-ai-chat-status">
                    <span class="tutor-ai-status-dot"></span>
                    <span class="tutor-ai-status-text"><?php esc_html_e('Online', 'tutor-ai'); ?></span>
                </div>
            </div>
            <button class="tutor-ai-chat-minimize" aria-label="<?php esc_attr_e('Minimize chat', 'tutor-ai'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 13H5V11H19V13Z" fill="currentColor"/>
                </svg>
            </button>
        </div>

        <!-- Messages Container -->
        <div id="tutor-ai-messages" class="tutor-ai-messages">
            <!-- Welcome Message -->
            <div class="tutor-ai-message tutor-ai-message-bot">
                <div class="tutor-ai-message-avatar">
                    <div class="tutor-ai-avatar-circle">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
                <div class="tutor-ai-message-content">
                    <div class="tutor-ai-message-bubble">
                        <?php echo wp_kses_post($welcome_message); ?>
                    </div>
                    <div class="tutor-ai-message-time"><?php echo date_i18n(get_option('time_format')); ?></div>
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <div id="tutor-ai-suggestions" class="tutor-ai-suggestions">
            <button class="tutor-ai-suggestion" data-message="¿Qué cursos me recomiendas?"><?php esc_html_e('Recomendar cursos', 'tutor-ai'); ?></button>
            <button class="tutor-ai-suggestion" data-message="¿Cuál es mi progreso?"><?php esc_html_e('Ver mi progreso', 'tutor-ai'); ?></button>
            <button class="tutor-ai-suggestion" data-message="¿Cómo obtengo un certificado?"><?php esc_html_e('Sobre certificados', 'tutor-ai'); ?></button>
        </div>

        <!-- Typing Indicator -->
        <div id="tutor-ai-typing" class="tutor-ai-typing" style="display: none;">
            <div class="tutor-ai-message tutor-ai-message-bot">
                <div class="tutor-ai-message-avatar">
                    <div class="tutor-ai-avatar-circle">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
                <div class="tutor-ai-message-content">
                    <div class="tutor-ai-message-bubble">
                        <div class="tutor-ai-typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="tutor-ai-chat-input">
            <div class="tutor-ai-input-container">
                <textarea 
                    id="tutor-ai-message-input" 
                    class="tutor-ai-message-input" 
                    placeholder="<?php esc_attr_e('Escribe tu pregunta...', 'tutor-ai'); ?>"
                    rows="1"
                    maxlength="1000"
                ></textarea>
                <button id="tutor-ai-send-button" class="tutor-ai-send-button" disabled aria-label="<?php esc_attr_e('Send message', 'tutor-ai'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
            <div class="tutor-ai-input-footer">
                <small class="tutor-ai-powered-by"><?php esc_html_e('Powered by AI', 'tutor-ai'); ?></small>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --tutor-ai-color: <?php echo esc_attr($chat_color); ?>;
    --tutor-ai-color-hover: color-mix(in srgb, var(--tutor-ai-color) 90%, black);
    --tutor-ai-bg: #ffffff;
    --tutor-ai-border: #e5e7eb;
    --tutor-ai-text: #1f2937;
    --tutor-ai-text-muted: #6b7280;
    --tutor-ai-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.tutor-ai-chat-widget {
    position: fixed;
    z-index: 999999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-size: 14px;
    line-height: 1.4;
}

.tutor-ai-chat-widget.bottom-right {
    bottom: 20px;
    right: 20px;
}

.tutor-ai-chat-widget.bottom-left {
    bottom: 20px;
    left: 20px;
}

.tutor-ai-chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--tutor-ai-color);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: var(--tutor-ai-shadow);
    transition: all 0.3s ease;
    position: relative;
}

.tutor-ai-chat-toggle:hover {
    background: var(--tutor-ai-color-hover);
    transform: scale(1.05);
}

.tutor-ai-chat-toggle .tutor-ai-icon-close {
    display: none;
}

.tutor-ai-chat-toggle.active .tutor-ai-icon-chat {
    display: none;
}

.tutor-ai-chat-toggle.active .tutor-ai-icon-close {
    display: block;
}

.tutor-ai-notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tutor-ai-chat-window {
    position: absolute;
    bottom: 70px;
    width: 380px;
    height: 500px;
    background: var(--tutor-ai-bg);
    border-radius: 12px;
    box-shadow: var(--tutor-ai-shadow);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.tutor-ai-chat-widget.bottom-left .tutor-ai-chat-window {
    right: 0;
}

.tutor-ai-chat-window.open {
    display: flex;
}

.tutor-ai-chat-header {
    background: var(--tutor-ai-color);
    color: white;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.tutor-ai-avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.tutor-ai-chat-info {
    flex: 1;
}

.tutor-ai-chat-title {
    font-weight: 600;
    font-size: 16px;
}

.tutor-ai-chat-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    opacity: 0.9;
}

.tutor-ai-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
}

.tutor-ai-chat-minimize {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    opacity: 0.8;
}

.tutor-ai-chat-minimize:hover {
    background: rgba(255, 255, 255, 0.1);
    opacity: 1;
}

.tutor-ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.tutor-ai-message {
    display: flex;
    gap: 8px;
}

.tutor-ai-message-bot {
    align-self: flex-start;
}

.tutor-ai-message-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.tutor-ai-message-avatar .tutor-ai-avatar-circle {
    width: 32px;
    height: 32px;
    background: var(--tutor-ai-color);
    color: white;
}

.tutor-ai-message-user .tutor-ai-message-avatar .tutor-ai-avatar-circle {
    background: var(--tutor-ai-text-muted);
}

.tutor-ai-message-content {
    max-width: 80%;
}

.tutor-ai-message-bubble {
    background: #f3f4f6;
    padding: 12px 16px;
    border-radius: 18px;
    border-bottom-left-radius: 4px;
}

.tutor-ai-message-user .tutor-ai-message-bubble {
    background: var(--tutor-ai-color);
    color: white;
    border-bottom-left-radius: 18px;
    border-bottom-right-radius: 4px;
}

.tutor-ai-message-time {
    font-size: 11px;
    color: var(--tutor-ai-text-muted);
    margin-top: 4px;
    padding: 0 4px;
}

.tutor-ai-suggestions {
    padding: 0 16px 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tutor-ai-suggestion {
    background: none;
    border: 1px solid var(--tutor-ai-border);
    border-radius: 20px;
    padding: 8px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--tutor-ai-text);
}

.tutor-ai-suggestion:hover {
    background: var(--tutor-ai-color);
    color: white;
    border-color: var(--tutor-ai-color);
}

.tutor-ai-typing-dots {
    display: flex;
    gap: 4px;
}

.tutor-ai-typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--tutor-ai-text-muted);
    animation: typing 1.4s infinite ease-in-out;
}

.tutor-ai-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.tutor-ai-typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

.tutor-ai-chat-input {
    border-top: 1px solid var(--tutor-ai-border);
    background: var(--tutor-ai-bg);
}

.tutor-ai-input-container {
    display: flex;
    align-items: flex-end;
    padding: 16px;
    gap: 8px;
}

.tutor-ai-message-input {
    flex: 1;
    border: 1px solid var(--tutor-ai-border);
    border-radius: 20px;
    padding: 12px 16px;
    font-family: inherit;
    font-size: 14px;
    resize: none;
    outline: none;
    max-height: 100px;
}

.tutor-ai-message-input:focus {
    border-color: var(--tutor-ai-color);
}

.tutor-ai-send-button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--tutor-ai-color);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.tutor-ai-send-button:disabled {
    background: var(--tutor-ai-text-muted);
    cursor: not-allowed;
}

.tutor-ai-send-button:not(:disabled):hover {
    background: var(--tutor-ai-color-hover);
}

.tutor-ai-input-footer {
    padding: 0 16px 12px;
    text-align: center;
}

.tutor-ai-powered-by {
    color: var(--tutor-ai-text-muted);
    font-size: 11px;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .tutor-ai-chat-window {
        width: calc(100vw - 40px);
        height: calc(100vh - 100px);
        bottom: 70px;
        right: 20px;
        left: 20px;
    }
    
    .tutor-ai-chat-widget.bottom-left .tutor-ai-chat-window {
        right: 20px;
    }
}
</style>
