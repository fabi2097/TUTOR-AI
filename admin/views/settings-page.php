<?php
/**
 * Admin Settings Page Template
 *
 * @package TutorAI
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = TutorAI\AdminSettings::instance()->get_settings();
?>

<div class="wrap tutor-ai-settings">
    <style>
        :root {
            --tutor-ai-primary: #2563eb;
            --tutor-ai-primary-dark: #1d4ed8;
            --tutor-ai-bg: #ffffff;
            --tutor-ai-text: #0f172a;
            --tutor-ai-muted: #64748b;
            --tutor-ai-surface: #f8fafc;
            --tutor-ai-border: #e2e8f0;
            --tutor-ai-success: #10b981;
            --tutor-ai-warning: #f59e0b;
            --tutor-ai-error: #ef4444;
            --tutor-ai-radius: 8px;
        }

        .tutor-ai-settings {
            background: #f6f7fb;
            margin-left: -20px;
            padding: 0;
        }

        .tutor-ai-header {
            background: var(--tutor-ai-bg);
            border-bottom: 1px solid var(--tutor-ai-border);
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .tutor-ai-header h1 {
            margin: 0;
            font-size: 24px;
            color: var(--tutor-ai-text);
        }

        .tutor-ai-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--tutor-ai-primary), var(--tutor-ai-primary-dark));
            border-radius: var(--tutor-ai-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .tutor-ai-content {
            display: grid;
            grid-template-columns: 280px 1fr;
            max-width: 1200px;
            margin: 0 auto;
            background: var(--tutor-ai-bg);
            min-height: calc(100vh - 120px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .tutor-ai-sidebar {
            background: linear-gradient(180deg, #fff 0%, #f9fbff 100%);
            border-right: 1px solid var(--tutor-ai-border);
            padding: 20px 0;
        }

        .tutor-ai-nav {
            list-style: none;
            margin: 0;
            padding: 0 15px;
        }

        .tutor-ai-nav li {
            margin-bottom: 5px;
        }

        .tutor-ai-nav button {
            width: 100%;
            text-align: left;
            background: transparent;
            border: 0;
            padding: 12px 15px;
            border-radius: var(--tutor-ai-radius);
            cursor: pointer;
            color: var(--tutor-ai-text);
            font-weight: 500;
            transition: all 0.2s;
        }

        .tutor-ai-nav button:hover {
            background: #f1f5f9;
        }

        .tutor-ai-nav button.active {
            background: #eaf2ff;
            color: var(--tutor-ai-primary);
            font-weight: 700;
            border: 1px solid #c7dbff;
        }

        .tutor-ai-panel {
            padding: 30px;
            display: none;
        }

        .tutor-ai-panel.active {
            display: block;
        }

        .tutor-ai-panel h2 {
            margin: 0 0 10px;
            font-size: 20px;
            color: var(--tutor-ai-text);
        }

        .tutor-ai-panel .description {
            color: var(--tutor-ai-muted);
            margin-bottom: 25px;
        }

        .tutor-ai-card {
            background: var(--tutor-ai-bg);
            border: 1px solid var(--tutor-ai-border);
            border-radius: var(--tutor-ai-radius);
            padding: 20px;
            margin-bottom: 20px;
        }

        .tutor-ai-card h3 {
            margin: 0 0 15px;
            font-size: 16px;
            color: var(--tutor-ai-text);
        }

        .tutor-ai-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .tutor-ai-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .tutor-ai-field label {
            font-weight: 600;
            color: var(--tutor-ai-text);
        }

        .tutor-ai-field input,
        .tutor-ai-field select,
        .tutor-ai-field textarea {
            padding: 10px 12px;
            border: 1px solid var(--tutor-ai-border);
            border-radius: var(--tutor-ai-radius);
            background: var(--tutor-ai-surface);
            font: inherit;
        }

        .tutor-ai-field textarea {
            min-height: 100px;
            resize: vertical;
        }

        .tutor-ai-field small {
            color: var(--tutor-ai-muted);
        }

        .tutor-ai-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tutor-ai-switch {
            position: relative;
            width: 44px;
            height: 24px;
            display: inline-block;
        }

        .tutor-ai-switch input {
            display: none;
        }

        .tutor-ai-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #cbd5e1;
            border-radius: 24px;
            transition: 0.2s;
        }

        .tutor-ai-slider:before {
            content: "";
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            top: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .tutor-ai-switch input:checked + .tutor-ai-slider {
            background: var(--tutor-ai-primary);
        }

        .tutor-ai-switch input:checked + .tutor-ai-slider:before {
            transform: translateX(20px);
        }

        .tutor-ai-btn {
            padding: 10px 16px;
            border-radius: var(--tutor-ai-radius);
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--tutor-ai-border);
            background: var(--tutor-ai-bg);
            color: var(--tutor-ai-text);
            transition: all 0.2s;
        }

        .tutor-ai-btn:hover {
            background: var(--tutor-ai-surface);
        }

        .tutor-ai-btn.primary {
            background: var(--tutor-ai-primary);
            color: white;
            border-color: var(--tutor-ai-primary);
        }

        .tutor-ai-btn.primary:hover {
            background: var(--tutor-ai-primary-dark);
        }

        .tutor-ai-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        .tutor-ai-status.success {
            color: var(--tutor-ai-success);
        }

        .tutor-ai-status.error {
            color: var(--tutor-ai-error);
        }

        .tutor-ai-status.warning {
            color: var(--tutor-ai-warning);
        }

        .tutor-ai-actions {
            position: sticky;
            bottom: 0;
            background: var(--tutor-ai-bg);
            border-top: 1px solid var(--tutor-ai-border);
            padding: 20px 30px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        @media (max-width: 900px) {
            .tutor-ai-content {
                grid-template-columns: 1fr;
            }
            .tutor-ai-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="tutor-ai-header">
        <div class="tutor-ai-logo">AI</div>
        <h1><?php _e('Tutor AI - Configuración del Asistente', 'tutor-ai'); ?></h1>
    </div>

    <?php
    // Procesar formulario si se envió
    if (isset($_POST['tutor_ai_save_settings']) && wp_verify_nonce($_POST['tutor_ai_nonce'], 'tutor_ai_settings')) {
        TutorAI\AdminSettings::instance()->save_settings($_POST);
        echo '<div class="notice notice-success"><p>' . __('Configuración guardada exitosamente.', 'tutor-ai') . '</p></div>';
    }
    ?>

    <form method="post" action="" id="tutor-ai-form">
        <?php wp_nonce_field('tutor_ai_settings', 'tutor_ai_nonce'); ?>
        <input type="hidden" name="tutor_ai_save_settings" value="1">
        
        <div class="tutor-ai-content">
            <!-- Sidebar Navigation -->
            <aside class="tutor-ai-sidebar">
                <ul class="tutor-ai-nav">
                    <li><button type="button" class="nav-btn active" data-target="general"><?php _e('General', 'tutor-ai'); ?></button></li>
                    <li><button type="button" class="nav-btn" data-target="behavior"><?php _e('Comportamiento', 'tutor-ai'); ?></button></li>
                    <li><button type="button" class="nav-btn" data-target="knowledge"><?php _e('Conocimiento (RAG)', 'tutor-ai'); ?></button></li>
                    <li><button type="button" class="nav-btn" data-target="integrations"><?php _e('Integraciones LMS', 'tutor-ai'); ?></button></li>
                    <li><button type="button" class="nav-btn" data-target="appearance"><?php _e('Apariencia', 'tutor-ai'); ?></button></li>
                    <li><button type="button" class="nav-btn" data-target="advanced"><?php _e('Avanzado', 'tutor-ai'); ?></button></li>
                </ul>
            </aside>

            <!-- Panels -->
            <main>
                <!-- General Panel -->
                <section class="tutor-ai-panel active" id="general">
                    <h2><?php _e('Configuración General', 'tutor-ai'); ?></h2>
                    <p class="description"><?php _e('Configuración básica del proveedor de IA y conexión.', 'tutor-ai'); ?></p>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Proveedor de IA', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-row">
                            <div class="tutor-ai-field">
                                <label><?php _e('Proveedor', 'tutor-ai'); ?></label>
                                <select name="ai_provider" id="ai_provider">
                                    <option value="openai" <?php selected($settings['ai_provider'] ?? 'openai', 'openai'); ?>>OpenAI</option>
                                    <option value="anthropic" <?php selected($settings['ai_provider'] ?? '', 'anthropic'); ?>>Anthropic (Claude)</option>
                                    <option value="google" <?php selected($settings['ai_provider'] ?? '', 'google'); ?>>Google (Gemini)</option>
                                </select>
                            </div>
                            <div class="tutor-ai-field">
                                <label><?php _e('Modelo', 'tutor-ai'); ?></label>
                                <select name="ai_model" id="ai_model">
                                    <!-- Los modelos se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="tutor-ai-field">
                            <label><?php _e('Clave API', 'tutor-ai'); ?></label>
                            <input type="password" name="tutor_ai_openai_api_key" value="<?php echo esc_attr($settings['api_key']); ?>" placeholder="sk-...">
                            <small><?php _e('Tu clave API de OpenAI. Se almacena de forma segura.', 'tutor-ai'); ?></small>
                        </div>
                        <div class="tutor-ai-toggle">
                            <button type="button" class="tutor-ai-btn" id="test-connection"><?php _e('Probar Conexión', 'tutor-ai'); ?></button>
                            <span id="connection-status" class="tutor-ai-status"><?php _e('Sin probar', 'tutor-ai'); ?></span>
                        </div>
                    </div>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Configuración del Modelo', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-row">
                            <div class="tutor-ai-field">
                                <label><?php _e('Temperatura', 'tutor-ai'); ?> <span id="temp-value"><?php echo $settings['temperature'] ?? 0.7; ?></span></label>
                                <input type="range" name="temperature" min="0" max="1" step="0.1" value="<?php echo $settings['temperature'] ?? 0.7; ?>" id="temperature-slider">
                                <small><?php _e('Controla la creatividad de las respuestas (0 = preciso, 1 = creativo)', 'tutor-ai'); ?></small>
                            </div>
                            <div class="tutor-ai-field">
                                <label><?php _e('Máximo de Tokens', 'tutor-ai'); ?></label>
                                <input type="number" name="max_tokens" value="<?php echo $settings['max_tokens'] ?? 800; ?>" min="100" max="4000">
                                <small><?php _e('Longitud máxima de las respuestas', 'tutor-ai'); ?></small>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Behavior Panel -->
                <section class="tutor-ai-panel" id="behavior">
                    <h2><?php _e('Comportamiento del Asistente', 'tutor-ai'); ?></h2>
                    <p class="description"><?php _e('Personaliza cómo interactúa el asistente con los estudiantes.', 'tutor-ai'); ?></p>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Personalidad', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-row">
                            <div class="tutor-ai-field">
                                <label><?php _e('Nombre del Asistente', 'tutor-ai'); ?></label>
                                <input type="text" name="bot_name" value="<?php echo esc_attr($settings['bot_name'] ?? 'Asistente de Cursos'); ?>">
                            </div>
                            <div class="tutor-ai-field">
                                <label><?php _e('Idioma', 'tutor-ai'); ?></label>
                                <select name="bot_language">
                                    <option value="es" <?php selected($settings['bot_language'] ?? 'es', 'es'); ?>>Español</option>
                                    <option value="en" <?php selected($settings['bot_language'] ?? '', 'en'); ?>>English</option>
                                    <option value="pt" <?php selected($settings['bot_language'] ?? '', 'pt'); ?>>Português</option>
                                </select>
                            </div>
                        </div>
                        <div class="tutor-ai-field">
                            <label><?php _e('Mensaje de Bienvenida', 'tutor-ai'); ?></label>
                            <textarea name="welcome_message" rows="3"><?php echo esc_textarea($settings['welcome_message'] ?? '¡Hola! Soy tu asistente de cursos. ¿En qué puedo ayudarte hoy?'); ?></textarea>
                        </div>
                        <div class="tutor-ai-field">
                            <label><?php _e('Instrucciones del Sistema', 'tutor-ai'); ?></label>
                            <textarea name="system_prompt" rows="5"><?php echo esc_textarea($settings['system_prompt'] ?? 'Eres un asistente educativo especializado en ayudar a estudiantes con cursos online. Responde de manera clara, útil y motivadora.'); ?></textarea>
                            <small><?php _e('Define cómo debe comportarse el asistente', 'tutor-ai'); ?></small>
                        </div>
                    </div>
                </section>

                <!-- Knowledge Panel -->
                <section class="tutor-ai-panel" id="knowledge">
                    <h2><?php _e('Base de Conocimiento (RAG)', 'tutor-ai'); ?></h2>
                    <p class="description"><?php _e('Configura qué información puede usar el asistente para responder.', 'tutor-ai'); ?></p>

                    <div class="tutor-ai-card">
                        <div class="tutor-ai-toggle">
                            <label class="tutor-ai-switch">
                                <input type="checkbox" name="rag_enabled" <?php checked($settings['rag_enabled'] ?? true); ?>>
                                <span class="tutor-ai-slider"></span>
                            </label>
                            <label><?php _e('Activar Base de Conocimiento', 'tutor-ai'); ?></label>
                        </div>
                        <small><?php _e('Permite al asistente acceder a información específica de tus cursos', 'tutor-ai'); ?></small>
                    </div>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Fuentes de Información', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-field">
                            <label>
                                <input type="checkbox" name="rag_sources[]" value="courses" <?php checked(in_array('courses', $settings['rag_sources'] ?? ['courses'])); ?>>
                                <?php _e('Contenido de Cursos', 'tutor-ai'); ?>
                            </label>
                        </div>
                        <div class="tutor-ai-field">
                            <label>
                                <input type="checkbox" name="rag_sources[]" value="lessons" <?php checked(in_array('lessons', $settings['rag_sources'] ?? ['lessons'])); ?>>
                                <?php _e('Lecciones y Módulos', 'tutor-ai'); ?>
                            </label>
                        </div>
                        <div class="tutor-ai-field">
                            <label>
                                <input type="checkbox" name="rag_sources[]" value="quizzes" <?php checked(in_array('quizzes', $settings['rag_sources'] ?? [])); ?>>
                                <?php _e('Cuestionarios y Evaluaciones', 'tutor-ai'); ?>
                            </label>
                        </div>
                        <div class="tutor-ai-field">
                            <label>
                                <input type="checkbox" name="rag_sources[]" value="faqs" <?php checked(in_array('faqs', $settings['rag_sources'] ?? [])); ?>>
                                <?php _e('Preguntas Frecuentes', 'tutor-ai'); ?>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Integrations Panel -->
                <section class="tutor-ai-panel" id="integrations">
                    <h2><?php _e('Integraciones LMS', 'tutor-ai'); ?></h2>
                    <p class="description"><?php _e('Conecta el asistente con tu plataforma de aprendizaje.', 'tutor-ai'); ?></p>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Sistema LMS Activo', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-field">
                            <label><?php _e('Plataforma', 'tutor-ai'); ?></label>
                            <select name="lms_platform">
                                <option value="tutor" <?php selected($settings['lms_platform'] ?? 'tutor', 'tutor'); ?>>Tutor LMS</option>
                                <option value="learndash" <?php selected($settings['lms_platform'] ?? '', 'learndash'); ?>>LearnDash (Próximamente)</option>
                                <option value="lifterlms" <?php selected($settings['lms_platform'] ?? '', 'lifterlms'); ?>>LifterLMS (Próximamente)</option>
                            </select>
                            <small><?php _e('Selecciona tu plataforma LMS principal', 'tutor-ai'); ?></small>
                        </div>
                    </div>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Funciones Disponibles', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-toggle">
                            <label class="tutor-ai-switch">
                                <input type="checkbox" name="enable_course_recommendations" <?php checked($settings['enable_course_recommendations'] ?? true); ?>>
                                <span class="tutor-ai-slider"></span>
                            </label>
                            <label><?php _e('Recomendaciones de Cursos', 'tutor-ai'); ?></label>
                        </div>
                        <div class="tutor-ai-toggle">
                            <label class="tutor-ai-switch">
                                <input type="checkbox" name="enable_progress_tracking" <?php checked($settings['enable_progress_tracking'] ?? true); ?>>
                                <span class="tutor-ai-slider"></span>
                            </label>
                            <label><?php _e('Seguimiento de Progreso', 'tutor-ai'); ?></label>
                        </div>
                        <div class="tutor-ai-toggle">
                            <label class="tutor-ai-switch">
                                <input type="checkbox" name="enable_enrollment_help" <?php checked($settings['enable_enrollment_help'] ?? false); ?>>
                                <span class="tutor-ai-slider"></span>
                            </label>
                            <label><?php _e('Ayuda con Inscripciones', 'tutor-ai'); ?></label>
                        </div>
                    </div>
                </section>

                <!-- Appearance Panel -->
                <section class="tutor-ai-panel" id="appearance">
                    <h2><?php _e('Apariencia del Chat', 'tutor-ai'); ?></h2>
                    <p class="description"><?php _e('Personaliza cómo se ve el widget de chat en tu sitio.', 'tutor-ai'); ?></p>

                    <div class="tutor-ai-card">
                        <div class="tutor-ai-toggle">
                            <label class="tutor-ai-switch">
                                <input type="checkbox" name="tutor_ai_chat_enabled" <?php checked($settings['chat_enabled']); ?>>
                                <span class="tutor-ai-slider"></span>
                            </label>
                            <label><?php _e('Activar Widget de Chat', 'tutor-ai'); ?></label>
                        </div>
                    </div>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Posición y Diseño', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-row">
                            <div class="tutor-ai-field">
                                <label><?php _e('Posición', 'tutor-ai'); ?></label>
                                <select name="chat_position">
                                    <option value="bottom-right" <?php selected($settings['chat_position'] ?? 'bottom-right', 'bottom-right'); ?>>Abajo Derecha</option>
                                    <option value="bottom-left" <?php selected($settings['chat_position'] ?? '', 'bottom-left'); ?>>Abajo Izquierda</option>
                                </select>
                            </div>
                            <div class="tutor-ai-field">
                                <label><?php _e('Color Principal', 'tutor-ai'); ?></label>
                                <input type="color" name="chat_color" value="<?php echo esc_attr($settings['chat_color'] ?? '#2563eb'); ?>">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Advanced Panel -->
                <section class="tutor-ai-panel" id="advanced">
                    <h2><?php _e('Configuración Avanzada', 'tutor-ai'); ?></h2>
                    <p class="description"><?php _e('Opciones para usuarios avanzados y desarrolladores.', 'tutor-ai'); ?></p>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Límites y Rendimiento', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-row">
                            <div class="tutor-ai-field">
                                <label><?php _e('Límite de Mensajes por Usuario/Día', 'tutor-ai'); ?></label>
                                <input type="number" name="daily_message_limit" value="<?php echo $settings['daily_message_limit'] ?? 50; ?>" min="1">
                            </div>
                            <div class="tutor-ai-field">
                                <label><?php _e('Timeout de Respuesta (segundos)', 'tutor-ai'); ?></label>
                                <input type="number" name="response_timeout" value="<?php echo $settings['response_timeout'] ?? 30; ?>" min="5" max="120">
                            </div>
                        </div>
                    </div>

                    <div class="tutor-ai-card">
                        <h3><?php _e('Logs y Depuración', 'tutor-ai'); ?></h3>
                        <div class="tutor-ai-toggle">
                            <label class="tutor-ai-switch">
                                <input type="checkbox" name="enable_logging" <?php checked($settings['enable_logging'] ?? true); ?>>
                                <span class="tutor-ai-slider"></span>
                            </label>
                            <label><?php _e('Activar Logs del Sistema', 'tutor-ai'); ?></label>
                        </div>
                        <div class="tutor-ai-toggle">
                            <label class="tutor-ai-switch">
                                <input type="checkbox" name="enable_debug" <?php checked($settings['enable_debug'] ?? false); ?>>
                                <span class="tutor-ai-slider"></span>
                            </label>
                            <label><?php _e('Modo Debug (Solo Desarrollo)', 'tutor-ai'); ?></label>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <div class="tutor-ai-actions">
            <button type="button" class="tutor-ai-btn" onclick="location.reload()"><?php _e('Cancelar', 'tutor-ai'); ?></button>
            <button type="submit" class="tutor-ai-btn primary"><?php _e('Guardar Configuración', 'tutor-ai'); ?></button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Modelos disponibles por proveedor
    const aiModels = {
        openai: [
            {value: 'gpt-5-mini', label: 'GPT-5 Mini (Más económico - Recomendado)', recommended: true},
            {value: 'gpt-5-nano', label: 'GPT-5 Nano (Ultra rápido)', recommended: false},
            {value: 'gpt-5', label: 'GPT-5 (Más avanzado)', recommended: false},
            {value: 'gpt-4o-mini', label: 'GPT-4o Mini (Económico)', recommended: false},
            {value: 'gpt-4o', label: 'GPT-4o', recommended: false},
            {value: 'gpt-4-turbo', label: 'GPT-4 Turbo', recommended: false},
            {value: 'o1-preview', label: 'o1-preview (Razonamiento avanzado)', recommended: false},
            {value: 'o1-mini', label: 'o1-mini (Razonamiento rápido)', recommended: false}
        ],
        anthropic: [
            {value: 'claude-3-5-sonnet-20241022', label: 'Claude 3.5 Sonnet (Recomendado)', recommended: true},
            {value: 'claude-3-sonnet-20240229', label: 'Claude 3 Sonnet', recommended: false},
            {value: 'claude-3-haiku-20240307', label: 'Claude 3 Haiku (Rápido)', recommended: false},
            {value: 'claude-2.1', label: 'Claude 2.1', recommended: false}
        ],
        google: [
            {value: 'gemini-1.5-pro', label: 'Gemini 1.5 Pro (Recomendado)', recommended: true},
            {value: 'gemini-1.5-flash', label: 'Gemini 1.5 Flash (Rápido)', recommended: false},
            {value: 'gemini-pro', label: 'Gemini Pro', recommended: false}
        ]
    };

    // Función para actualizar modelos según el proveedor
    function updateModels() {
        const provider = $('#ai_provider').val();
        const $modelSelect = $('#ai_model');
        const currentModel = $modelSelect.val();
        
        $modelSelect.empty();
        
        if (aiModels[provider]) {
            aiModels[provider].forEach(function(model) {
                const label = model.recommended ? model.label : model.label;
                $modelSelect.append(new Option(label, model.value));
            });
            
            // Intentar mantener el modelo actual si existe para este proveedor
            if (currentModel && $modelSelect.find(`option[value="${currentModel}"]`).length) {
                $modelSelect.val(currentModel);
            } else {
                // Seleccionar el modelo recomendado
                const recommended = aiModels[provider].find(m => m.recommended);
                if (recommended) {
                    $modelSelect.val(recommended.value);
                }
            }
        }
    }

    // Inicializar modelos al cargar la página
    updateModels();

    // Actualizar modelos cuando cambie el proveedor
    $('#ai_provider').on('change', updateModels);

    // Navigation
    $('.nav-btn').on('click', function() {
        $('.nav-btn').removeClass('active');
        $(this).addClass('active');
        
        const target = $(this).data('target');
        $('.tutor-ai-panel').removeClass('active');
        $('#' + target).addClass('active');
    });

    // Temperature slider
    $('#temperature-slider').on('input', function() {
        $('#temp-value').text($(this).val());
    });

    // Test connection
    $('#test-connection').on('click', function() {
        const $btn = $(this);
        const $status = $('#connection-status');
        const apiKey = $('input[name="tutor_ai_openai_api_key"]').val();
        const provider = $('#ai_provider').val();

        if (!apiKey) {
            $status.removeClass().addClass('tutor-ai-status error').text('<?php _e('Por favor ingresa una clave API', 'tutor-ai'); ?>');
            return;
        }

        $btn.prop('disabled', true).text('<?php _e('Probando...', 'tutor-ai'); ?>');
        $status.removeClass().addClass('tutor-ai-status').text('<?php _e('Probando conexión...', 'tutor-ai'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tutor_ai_test_connection',
                api_key: apiKey,
                provider: provider,
                nonce: '<?php echo wp_create_nonce('tutor_ai_test_connection'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $status.removeClass().addClass('tutor-ai-status success').text('<?php _e('Conexión exitosa', 'tutor-ai'); ?>');
                } else {
                    $status.removeClass().addClass('tutor-ai-status error').text(response.data || '<?php _e('Error de conexión', 'tutor-ai'); ?>');
                }
            },
            error: function() {
                $status.removeClass().addClass('tutor-ai-status error').text('<?php _e('Error de conexión', 'tutor-ai'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php _e('Probar Conexión', 'tutor-ai'); ?>');
            }
        });
    });
});
</script>
