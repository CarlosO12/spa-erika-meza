<?php
/**
 * Helper de Envío de Emails
 * SPA Erika Meza
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/vendor/autoload.php';

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->CharSet = 'UTF-8';

        // Remitente y destinatario
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Contenido del mensaje
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando email: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Plantilla base para emails
 */
function emailTemplate($content) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #879bf1 0%, #dd8fd9 100%);
                color: #ffffff;
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
            }
            .content {
                padding: 30px;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #879bf1 0%, #dd8fd9 100%);
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
            }
            .footer {
                background-color: #f8f9fa;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . APP_NAME . '</h1>
            </div>
            <div class="content">
                ' . $content . '
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. Todos los derechos reservados.</p>
                <p>Si tienes alguna pregunta, contáctanos en ' . APP_EMAIL . '</p>
            </div>
        </div>
    </body>
    </html>
    ';
}

/**
 * Enviar email de verificación
 */
function sendVerificationEmail($to, $nombre, $code) {
    $subject = 'Verifica tu cuenta - ' . APP_NAME;
    $content = '
        <h2>¡Hola ' . htmlspecialchars($nombre) . '!</h2>
        <p>Gracias por registrarte en ' . APP_NAME . '.</p>
        <p>Por favor verifica tu cuenta con el siguiente código:</p>
        <div style="text-align: center; margin: 30px 0;">
            <div style="display: inline-block; padding: 20px 40px; background-color: #f8f9fa; border-radius: 10px; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #667eea;">' . $code . '</div>
        </div>
        <p>Este código expirará en 24 horas.</p>';
    $message = emailTemplate($content);
    return sendEmail($to, $subject, $message);
}

/**
 * Enviar email de bienvenida
 */
function sendWelcomeEmail($to, $nombre) {
    $subject = '¡Bienvenido a ' . APP_NAME . '!';
    
    $content = '
        <h2>¡Bienvenido ' . e($nombre) . '!😊</h2>
        <p>Tu cuenta ha sido verificada exitosamente.</p>
        <p>Ahora puedes disfrutar de todos nuestros servicios:</p>
        <ul>
            <li>Reservar citas en línea</li>
            <li>Ver historial de servicios</li>
            <li>Recibir notificaciones y recordatorios</li>
            <li>Dejar reseñas de nuestros servicios</li>
        </ul>
        <div style="text-align: center;">
            <a href="' . BASE_URL . '/index.php?page=login" class="button">Iniciar Sesión</a>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($to, $subject, $message);
}

/**
 * Enviar email de recuperación de contraseña
 */
function sendPasswordResetEmail($to, $nombre, $token) {
    $subject = 'Recuperación de Contraseña - ' . APP_NAME;
    
    $resetLink = BASE_URL . '/index.php?page=reset-password&token=' . $token;
    
    $content = '
        <h2>Hola ' . e($nombre) . ',😎</h2>
        <p>Recibimos una solicitud para restablecer tu contraseña.</p>
        <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
        <div style="text-align: center;">
            <a href="' . $resetLink . '" class="button">Restablecer Contraseña</a>
        </div>
        <p>O copia y pega este enlace en tu navegador:</p>
        <p style="word-break: break-all; color: #667eea;">' . $resetLink . '</p>
        <p>Este enlace expirará en 1 hora.</p>
        <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este email de forma segura.</p>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($to, $subject, $message);
}

/**
 * Enviar confirmación de cita
 */
function sendAppointmentConfirmation($appointmentData) {
    $subject = 'Confirmación de Cita - ' . APP_NAME;
    
    $content = '
        <h2>Cita Confirmada 😊</h2>
        <p>Hola ' . e($appointmentData['nombre_cliente']) . ',</p>
        <p>Tu cita ha sido confirmada exitosamente.</p>
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">Detalles de la Cita:</h3>
            <p><strong>Servicio:</strong> ' . e($appointmentData['nombre_servicio']) . '</p>
            <p><strong>Fecha:</strong> ' . formatDate($appointmentData['fecha_cita']) . '</p>
            <p><strong>Hora:</strong> ' . date('h:i A', strtotime($appointmentData['hora_cita'])) . '</p>
            <p><strong>Especialista:</strong> ' . e($appointmentData['nombre_especialista']) . '</p>
            <p><strong>Duración:</strong> ' . $appointmentData['duracion_servicio'] . ' minutos</p>
            <p><strong>Precio:</strong> ' . formatPrice($appointmentData['precio_servicio']) . '</p>
        </div>
        <p><strong>⚠️ Importante:</strong> Si necesitas cancelar o reprogramar, por favor hazlo con al menos 24 horas de anticipación.</p>
        <div style="text-align: center;">
            <a href="' . BASE_URL . '/index.php?page=my-appointments" class="button">Ver Mis Citas</a>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($appointmentData['email_cliente'], $subject, $message);
}

/**
 * Enviar recordatorio de cita
 */
function sendAppointmentReminder($appointmentData) {
    $subject = 'Recordatorio de Cita - ' . APP_NAME;
    
    $content = '
        <h2>Recordatorio de Cita 📆</h2>
        <p>Hola ' . e($appointmentData['nombre_cliente']) . ',</p>
        <p>Te recordamos que tienes una cita próximamente:</p>
        <div style="background-color: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p><strong>Servicio:</strong> ' . e($appointmentData['nombre_servicio']) . '</p>
            <p><strong>Fecha:</strong> ' . formatDate($appointmentData['fecha_cita']) . '</p>
            <p><strong>Hora:</strong> ' . date('h:i A', strtotime($appointmentData['hora_cita'])) . '</p>
            <p><strong>Especialista:</strong> ' . e($appointmentData['nombre_especialista']) . '</p>
        </div>
        <p>Te esperamos!</p>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($appointmentData['email_cliente'], $subject, $message);
}

/**
 * Enviar notificación de cancelación
 */
function sendCancellationEmail($appointmentData) {
    $subject = 'Cita Cancelada - ' . APP_NAME;
    
    $content = '
        <h2>Cita Cancelada 😟</h2>
        <p>Hola ' . e($appointmentData['nombre_cliente']) . ',</p>
        <p>Tu cita ha sido cancelada.</p>
        <div style="background-color: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;">
            <p><strong>Servicio:</strong> ' . e($appointmentData['nombre_servicio']) . '</p>
            <p><strong>Fecha:</strong> ' . formatDate($appointmentData['fecha_cita']) . '</p>
            <p><strong>Hora:</strong> ' . date('h:i A', strtotime($appointmentData['hora_cita'])) . '</p>
        </div>
        <p>Si deseas agendar una nueva cita, puedes hacerlo en cualquier momento.</p>
        <div style="text-align: center;">
            <a href="' . BASE_URL . '/index.php?page=services" class="button">Ver Servicios</a>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($appointmentData['email_cliente'], $subject, $message);
}


/**
 * Enviar notificación de mensaje de contacto al administrador
 */
function sendContactNotification($contactData) {
    $subject = 'Nuevo mensaje de contacto - ' . APP_NAME;
    
    // Mapeo de asuntos para mostrar texto legible
    $asuntosMap = [
        'informacion' => 'Información General',
        'cita' => 'Agendar Cita',
        'servicio' => 'Consulta sobre Servicios',
        'queja' => 'Queja o Reclamo',
        'otro' => 'Otro'
    ];
    
    $asuntoTexto = $asuntosMap[$contactData['asunto']] ?? $contactData['asunto'];
    $telefono = !empty($contactData['telefono']) ? $contactData['telefono'] : 'No proporcionado';
    
    $content = '
        <h2>Nuevo Mensaje de Contacto 😎</h2>
        <p>Has recibido un nuevo mensaje desde el formulario de contacto.</p>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">Información del Remitente:</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 120px; color: #555;">Nombre:</td>
                    <td style="padding: 8px 0;">' . e($contactData['nombre']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555;">Email:</td>
                    <td style="padding: 8px 0;">
                        <a href="mailto:' . e($contactData['email']) . '" style="color: #667eea; text-decoration: none;">
                            ' . e($contactData['email']) . '
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555;">Teléfono:</td>
                    <td style="padding: 8px 0;">
                        <a href="tel:' . preg_replace('/\D/', '', $telefono) . '" style="color: #667eea; text-decoration: none;">
                            ' . e($telefono) . '
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555;">Asunto:</td>
                    <td style="padding: 8px 0;">
                        <span style="display: inline-block; padding: 4px 12px; background: linear-gradient(135deg, #879bf1 0%, #dd8fd9 100%); color: white; border-radius: 15px; font-size: 12px;">
                            ' . e($asuntoTexto) . '
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <div style="background-color: #fff; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #667eea;">
            <h3 style="margin-top: 0; color: #667eea;">Mensaje:</h3>
            <p style="margin: 0; line-height: 1.6; white-space: pre-wrap; color: #333;">' . nl2br(e($contactData['mensaje'])) . '</p>
        </div>
        
        <div style="background-color: #e7f3ff; padding: 15px; border-radius: 10px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #0066cc;">
                <strong>Tip:</strong> Responde directamente a este correo o contacta al remitente en: 
                <a href="mailto:' . e($contactData['email']) . '" style="color: #667eea;">' . e($contactData['email']) . '</a>
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="' . BASE_URL . '/index.php?page=admin-messages" class="button">Ver Todos los Mensajes</a>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail(APP_EMAIL, $subject, $message);
}

/**
 * Enviar confirmación al usuario que envió el mensaje de contacto
 */
function sendContactConfirmation($contactData) {
    $subject = 'Hemos recibido tu mensaje - ' . APP_NAME;
    
    $asuntosMap = [
        'informacion' => 'Información General',
        'cita' => 'Agendar Cita',
        'servicio' => 'Consulta sobre Servicios',
        'queja' => 'Queja o Reclamo',
        'otro' => 'Otro'
    ];
    
    $asuntoTexto = $asuntosMap[$contactData['asunto']] ?? $contactData['asunto'];
    
    $content = '
        <h2>¡Gracias por contactarnos! 😁</h2>
        <p>Hola ' . e($contactData['nombre']) . ',</p>
        <p>Hemos recibido tu mensaje y te responderemos lo antes posible, generalmente dentro de 24-48 horas.</p>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">Resumen de tu mensaje:</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 120px; color: #555;">Asunto:</td>
                    <td style="padding: 8px 0;">' . e($asuntoTexto) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555;">Fecha:</td>
                    <td style="padding: 8px 0;">' . date('d/m/Y H:i') . '</td>
                </tr>
            </table>
            
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                <p style="margin: 0; font-weight: bold; color: #555; margin-bottom: 8px;">Tu mensaje:</p>
                <p style="margin: 0; line-height: 1.6; color: #666;">' . nl2br(e($contactData['mensaje'])) . '</p>
            </div>
        </div>
        
        <div style="background-color: #d4edda; padding: 15px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;">
            <p style="margin: 0; font-size: 14px; color: #155724;">
                <strong>✓ Mensaje recibido exitosamente</strong><br>
                Nuestro equipo revisará tu solicitud y te contactaremos pronto.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">¿Necesitas atención inmediata?</p>
            <a href="tel:' . APP_PHONE . '" class="button">Llámanos: ' . APP_PHONE . '</a>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <p style="color: #666; font-size: 14px; text-align: center; margin: 0;">
                <strong>Horario de atención:</strong><br>
                ' . config('business_schedule') . ' de ' . config('business_hours_start') . ' a ' . config('business_hours_end') . '
            </p>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($contactData['email'], $subject, $message);
}

function sendMessageReply($data) {
    $subject = $data['subject'];
    
    $content = '
        <h2>Respuesta a tu mensaje 🥳</h2>
        <p>Hola ' . e($data['to_name']) . ',</p>
        <p>Gracias por contactarnos. A continuación encontrarás nuestra respuesta a tu consulta:</p>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">Nuestra respuesta:</h3>
            <div style="line-height: 1.6; color: #333; white-space: pre-wrap;">
                ' . nl2br(e($data['message'])) . '
            </div>
        </div>
        
        <div style="background-color: #f1f3f5; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0; font-weight: bold; color: #555; font-size: 14px;">Tu mensaje original:</p>
            <div style="padding: 15px; background-color: #ffffff; border-radius: 8px; font-size: 14px; color: #666; line-height: 1.6; white-space: pre-wrap;">
                ' . nl2br(e($data['original_message'])) . '
            </div>
        </div>
        
        <div style="background-color: #e3f2fd; padding: 15px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196F3;">
            <p style="margin: 0; font-size: 14px; color: #1565C0;">
                <strong>💬 ¿Tienes más preguntas?</strong><br>
                No dudes en contactarnos nuevamente. Estamos aquí para ayudarte.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">¿Necesitas atención inmediata?</p>
            <a href="tel:' . APP_PHONE . '" class="button">Llámanos: ' . APP_PHONE . '</a>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <p style="color: #666; font-size: 14px; text-align: center; margin: 0;">
                <strong>Horario de atención:</strong><br>
                ' . config('business_schedule') . ' de ' . config('business_hours_start') . ' a ' . config('business_hours_end') . '
            </p>
        </div>
        
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center;">
            <p style="margin: 0; font-size: 13px; color: #999;">
                Atendido por: <strong>' . e($data['admin_name']) . '</strong>
            </p>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($data['to_email'], $subject, $message);
}

/**
 * Enviar notificación de nueva cita al especialista
 */
function sendSpecialistNewAppointmentNotification($appointmentData) {
    // Validar que exista el email del especialista
    if (empty($appointmentData['email_especialista'])) {
        error_log("No se puede enviar notificación: email_especialista no existe");
        return false;
    }
    
    $subject = 'Nueva Cita Asignada - ' . APP_NAME;
    
    $notasSection = '';
    if (!empty($appointmentData['notas'])) {
        $notasSection = '
        <div style="background-color: #d1ecf1; padding: 15px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #17a2b8;">
            <p style="margin: 0; font-size: 14px; color: #0c5460;">
                <strong>📝 Notas del cliente:</strong><br>
                <span style="margin-top: 8px; display: block;">' . nl2br(e($appointmentData['notas'])) . '</span>
            </p>
        </div>';
    }
    
    $telefonoCliente = !empty($appointmentData['telefono_cliente']) ? $appointmentData['telefono_cliente'] : 'No proporcionado';
    
    $content = '
        <h2>¡Nueva Cita Asignada! 🥳</h2>
        <p>Hola ' . e($appointmentData['nombre_especialista']) . ',</p>
        <p>Se ha agendado una nueva cita para ti. A continuación los detalles:</p>
        
        <div style="background-color: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;">
            <h3 style="margin-top: 0; color: #155724;">📅 Detalles de la Cita:</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 140px; color: #155724;">Fecha:</td>
                    <td style="padding: 8px 0; color: #155724;">' . formatDate($appointmentData['fecha_cita']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #155724;">Hora:</td>
                    <td style="padding: 8px 0; color: #155724;">' . date('h:i A', strtotime($appointmentData['hora_cita'])) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #155724;">Servicio:</td>
                    <td style="padding: 8px 0; color: #155724;">' . e($appointmentData['nombre_servicio']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #155724;">Duración:</td>
                    <td style="padding: 8px 0; color: #155724;">' . $appointmentData['duracion_servicio'] . ' minutos</td>
                </tr>
            </table>
        </div>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">👤 Información del Cliente:</h3>
            
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 100px; color: #555; vertical-align: top;">Nombre:</td>
                    <td style="padding: 8px 0; word-wrap: break-word; overflow-wrap: break-word;">' . e($appointmentData['nombre_cliente']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555; vertical-align: top;">Email:</td>
                    <td style="padding: 8px 0; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all;">
                        <a href="mailto:' . e($appointmentData['email_cliente']) . '" style="color: #667eea; text-decoration: none; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all;">
                            ' . e($appointmentData['email_cliente']) . '
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555; vertical-align: top;">Teléfono:</td>
                    <td style="padding: 8px 0; word-wrap: break-word; overflow-wrap: break-word;">
                        <a href="tel:' . preg_replace('/\D/', '', $telefonoCliente) . '" style="color: #667eea; text-decoration: none;">
                            ' . e($telefonoCliente) . '
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        
        ' . $notasSection . '
        
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; font-size: 14px; color: #856404;">
                <strong>⚠️ Recordatorio:</strong> La cita puede ser cancelada por el cliente hasta 24 horas antes.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="' . BASE_URL . '/index.php?page=specialist-appointments" class="button">Ver Todas Mis Citas</a>
        </div>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="' . BASE_URL . '/index.php?page=specialist-schedule" style="color: #667eea; text-decoration: none; font-size: 14px;">
                📆 Ver Mi Horario Semanal
            </a>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($appointmentData['email_especialista'], $subject, $message);
}

/**
 * Enviar notificación de cancelación de cita al especialista
 */
function sendSpecialistCancellationNotification($appointmentData) {
    // Validar que exista el email del especialista
    if (empty($appointmentData['email_especialista'])) {
        error_log("No se puede enviar notificación: email_especialista no existe");
        return false;
    }
    
    $subject = 'Cita Cancelada - ' . APP_NAME;
    
    $razonSection = '';
    if (!empty($appointmentData['razon_cancelacion'])) {
        $razonSection = '
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; font-size: 14px; color: #856404;">
                <strong>💬 Razón de cancelación:</strong><br>
                <span style="margin-top: 8px; display: block;">' . nl2br(e($appointmentData['razon_cancelacion'])) . '</span>
            </p>
        </div>';
    }
    
    $telefonoCliente = !empty($appointmentData['telefono_cliente']) ? $appointmentData['telefono_cliente'] : 'No proporcionado';
    
    $content = '
        <h2>Cita Cancelada 😟</h2>
        <p>Hola ' . e($appointmentData['nombre_especialista']) . ',</p>
        <p>Te informamos que una cita ha sido cancelada. A continuación los detalles:</p>
        
        <div style="background-color: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;">
            <h3 style="margin-top: 0; color: #721c24;">❌ Cita Cancelada:</h3>
            
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 100px; color: #721c24; vertical-align: top;">Fecha:</td>
                    <td style="padding: 8px 0; color: #721c24;">' . formatDate($appointmentData['fecha_cita']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #721c24; vertical-align: top;">Hora:</td>
                    <td style="padding: 8px 0; color: #721c24;">' . date('h:i A', strtotime($appointmentData['hora_cita'])) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #721c24; vertical-align: top;">Servicio:</td>
                    <td style="padding: 8px 0; color: #721c24;">' . e($appointmentData['nombre_servicio']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #721c24; vertical-align: top;">Duración:</td>
                    <td style="padding: 8px 0; color: #721c24;">' . $appointmentData['duracion_servicio'] . ' minutos</td>
                </tr>
            </table>
        </div>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">👤 Información del Cliente:</h3>
            
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 100px; color: #555; vertical-align: top;">Nombre:</td>
                    <td style="padding: 8px 0; word-wrap: break-word; overflow-wrap: break-word;">' . e($appointmentData['nombre_cliente']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555; vertical-align: top;">Email:</td>
                    <td style="padding: 8px 0; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all;">
                        <a href="mailto:' . e($appointmentData['email_cliente']) . '" style="color: #667eea; text-decoration: none; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all;">
                            ' . e($appointmentData['email_cliente']) . '
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #555; vertical-align: top;">Teléfono:</td>
                    <td style="padding: 8px 0; word-wrap: break-word; overflow-wrap: break-word;">
                        <a href="tel:' . preg_replace('/\D/', '', $telefonoCliente) . '" style="color: #667eea; text-decoration: none;">
                            ' . e($telefonoCliente) . '
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        
        ' . $razonSection . '
        
        <div style="background-color: #d1ecf1; padding: 15px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #17a2b8;">
            <p style="margin: 0; font-size: 14px; color: #0c5460;">
                <strong>ℹ️ Información:</strong> Este espacio en tu agenda ahora está disponible para nuevas citas.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="' . BASE_URL . '/index.php?page=specialist-appointments" class="button">Ver Todas Mis Citas</a>
        </div>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="' . BASE_URL . '/index.php?page=specialist-schedule" style="color: #667eea; text-decoration: none; font-size: 14px;">
                📆 Ver Mi Horario Semanal
            </a>
        </div>
    ';
    
    $message = emailTemplate($content);
    return sendEmail($appointmentData['email_especialista'], $subject, $message);
}
?>