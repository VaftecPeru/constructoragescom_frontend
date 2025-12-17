<?php
/**
 * Manejador de suscripciones al newsletter
 */

// Configurar cabeceras CORS y tipo de respuesta
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message_es' => 'Método no permitido',
        'message_en' => 'Method not allowed'
    ]);
    exit;
}

// Obtener datos del formulario
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validar email
if (empty($email)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message_es' => 'Por favor ingresa tu correo electrónico',
        'message_en' => 'Please enter your email address'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message_es' => 'Por favor ingresa un correo electrónico válido',
        'message_en' => 'Please enter a valid email address'
    ]);
    exit;
}

// Archivo para almacenar suscriptores
$subscribersFile = __DIR__ . '/subscribers.json';

// Cargar suscriptores existentes
$subscribers = [];
if (file_exists($subscribersFile)) {
    $content = file_get_contents($subscribersFile);
    $subscribers = json_decode($content, true) ?: [];
}

// Verificar si ya está suscrito
$emailLower = strtolower($email);
foreach ($subscribers as $subscriber) {
    if (strtolower($subscriber['email']) === $emailLower) {
        echo json_encode([
            'success' => true,
            'already_subscribed' => true,
            'message_es' => '¡Ya estás suscrito a nuestro newsletter!',
            'message_en' => 'You are already subscribed to our newsletter!'
        ]);
        exit;
    }
}

// Agregar nuevo suscriptor
$newSubscriber = [
    'email' => $email,
    'date' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

$subscribers[] = $newSubscriber;

// Guardar suscriptores
if (file_put_contents($subscribersFile, json_encode($subscribers, JSON_PRETTY_PRINT))) {
    
    // Enviar email de notificación al administrador (opcional)
    $adminEmail = 'info@constructoragescom.com';
    $subject = 'Nueva suscripción al newsletter - GESCOM';
    
    // Email HTML para el administrador
    $adminHtml = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #1B2A4E 0%, #2d4a7c 100%); padding: 30px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">🔔 Nueva Suscripción</h1>
                                <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 14px;">Newsletter GESCOM</p>
                            </td>
                        </tr>
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px 30px;">
                                <p style="color: #333; font-size: 16px; margin: 0 0 20px 0;">Se ha registrado una nueva suscripción al newsletter:</p>
                                
                                <table width="100%" cellpadding="15" cellspacing="0" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #D4A853;">
                                    <tr>
                                        <td>
                                            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                                                <strong style="color: #333;">📧 Email:</strong><br>
                                                <span style="color: #D4A853; font-size: 16px;">' . htmlspecialchars($email) . '</span>
                                            </p>
                                            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                                                <strong style="color: #333;">📅 Fecha:</strong><br>
                                                ' . date('d/m/Y H:i:s') . '
                                            </p>
                                            <p style="margin: 0; color: #666; font-size: 14px;">
                                                <strong style="color: #333;">🌐 IP:</strong><br>
                                                ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="color: #666; font-size: 14px; margin: 25px 0 0 0; padding-top: 20px; border-top: 1px solid #eee;">
                                    Total de suscriptores: <strong style="color: #D4A853;">' . count($subscribers) . '</strong>
                                </p>
                            </td>
                        </tr>
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #1B2A4E; padding: 25px 30px; text-align: center;">
                                <p style="color: #888; font-size: 12px; margin: 0;">
                                    Constructora GESCOM SAC<br>
                                    Este es un mensaje automático del sistema
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    $headers = "From: newsletter@constructoragescom.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    
    // Intentar enviar email (no fallar si no funciona)
    @mail($adminEmail, $subject, $adminHtml, $headers);
    
    // Enviar email de bienvenida al suscriptor
    $welcomeSubject = '¡Bienvenido al newsletter de GESCOM! 🏗️';
    
    $welcomeHtml = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
                        <!-- Header con Logo -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #1B2A4E 0%, #2d4a7c 100%); padding: 40px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: bold; letter-spacing: 2px;">GESCOM</h1>
                                <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Constructora SAC</p>
                            </td>
                        </tr>
                        <!-- Bienvenida -->
                        <tr>
                            <td style="padding: 40px 30px 20px 30px; text-align: center;">
                                <h2 style="color: #1B2A4E; margin: 0; font-size: 24px;">¡Gracias por suscribirte! 🎉</h2>
                                <p style="color: #666; font-size: 16px; line-height: 1.6; margin: 15px 0 0 0;">
                                    Ahora formas parte de nuestra comunidad. Recibirás las últimas novedades directamente en tu correo.
                                </p>
                            </td>
                        </tr>
                        <!-- Beneficios -->
                        <tr>
                            <td style="padding: 20px 30px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 15px; background: linear-gradient(135deg, #f8f6f0 0%, #fff 100%); border-radius: 10px; border: 1px solid #e8dfc9;">
                                            <h3 style="color: #1B2A4E; margin: 0 0 15px 0; font-size: 18px;">📬 Recibirás información sobre:</h3>
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <span style="color: #D4A853; font-size: 16px;">✓</span>
                                                        <span style="color: #555; font-size: 14px; margin-left: 10px;">Nuevos proyectos y servicios</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <span style="color: #D4A853; font-size: 16px;">✓</span>
                                                        <span style="color: #555; font-size: 14px; margin-left: 10px;">Promociones y ofertas especiales</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <span style="color: #D4A853; font-size: 16px;">✓</span>
                                                        <span style="color: #555; font-size: 14px; margin-left: 10px;">Consejos de construcción y mantenimiento</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <span style="color: #D4A853; font-size: 16px;">✓</span>
                                                        <span style="color: #555; font-size: 14px; margin-left: 10px;">Tendencias del sector inmobiliario</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- CTA Button -->
                        <tr>
                            <td style="padding: 20px 30px 40px 30px; text-align: center;">
                                <a href="https://www.constructoragescom.com" style="display: inline-block; background: linear-gradient(135deg, #D4A853 0%, #c49a43 100%); color: #1B2A4E; text-decoration: none; padding: 15px 40px; border-radius: 50px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 15px rgba(212, 168, 83, 0.4);">
                                    Visitar Nuestra Web
                                </a>
                            </td>
                        </tr>
                        <!-- Contacto -->
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 30px; text-align: center;">
                                <p style="color: #666; font-size: 14px; margin: 0 0 15px 0;">¿Tienes algún proyecto en mente? ¡Contáctanos!</p>
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center">
                                            <table cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="padding: 0 15px;">
                                                        <a href="tel:+51975130810" style="color: #D4A853; text-decoration: none; font-size: 14px;">📞 +51 975 130 810</a>
                                                    </td>
                                                    <td style="padding: 0 15px;">
                                                        <a href="mailto:info@constructoragescom.com" style="color: #D4A853; text-decoration: none; font-size: 14px;">✉️ info@constructoragescom.com</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #1B2A4E; padding: 25px 30px; text-align: center;">
                                <p style="color: #D4A853; font-size: 18px; font-weight: bold; margin: 0 0 5px 0;">GESCOM</p>
                                <p style="color: #888; font-size: 12px; margin: 0 0 15px 0;">Constructora SAC | Lima, Perú</p>
                                <p style="color: #666; font-size: 11px; margin: 0;">
                                    Si no deseas recibir más correos, puedes <a href="#" style="color: #D4A853;">darte de baja aquí</a>.
                                </p>
                            </td>
                        </tr>
                    </table>
                    <!-- Disclaimer -->
                    <p style="color: #999; font-size: 11px; margin: 20px 0 0 0; text-align: center;">
                        © ' . date('Y') . ' Constructora GESCOM SAC. Todos los derechos reservados.
                    </p>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    $welcomeHeaders = "From: newsletter@constructoragescom.com\r\n";
    $welcomeHeaders .= "MIME-Version: 1.0\r\n";
    $welcomeHeaders .= "Content-Type: text/html; charset=utf-8\r\n";
    
    @mail($email, $welcomeSubject, $welcomeHtml, $welcomeHeaders);
    
    echo json_encode([
        'success' => true,
        'message_es' => '¡Gracias por suscribirte! Recibirás nuestras novedades pronto.',
        'message_en' => 'Thank you for subscribing! You will receive our news soon.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message_es' => 'Error al procesar la suscripción. Intenta nuevamente.',
        'message_en' => 'Error processing subscription. Please try again.'
    ]);
}
