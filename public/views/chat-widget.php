<?php
/**
 * Chat Widget Template
 *
 * @package TutorAI
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('tutor_ai_settings', []);
$chat_enabled = $settings['chat_enabled'] ?? true;

if (!$chat_enabled) {
    return;
}

$bot_name = $settings['bot_name'] ?? 'Asistente de Cursos';
$welcome_message = $settings['welcome_message'] ?? '¡Hola! Soy tu asistente de cursos. ¿En qué puedo ayudarte hoy?';
$chat_position = $settings['chat_position'] ?? 'bottom-right';
$chat_color = $settings['chat_color'] ?? '#2563eb';
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
