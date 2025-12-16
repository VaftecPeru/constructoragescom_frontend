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
    $message = "Se ha registrado una nueva suscripción al newsletter:\n\n";
    $message .= "Email: $email\n";
    $message .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
    $message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    
    $headers = "From: newsletter@constructoragescom.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    // Intentar enviar email (no fallar si no funciona)
    @mail($adminEmail, $subject, $message, $headers);
    
    // Enviar email de bienvenida al suscriptor
    $welcomeSubject = 'Bienvenido al newsletter de GESCOM';
    $welcomeMessage = "¡Hola!\n\n";
    $welcomeMessage .= "Gracias por suscribirte al newsletter de Constructora GESCOM SAC.\n\n";
    $welcomeMessage .= "Recibirás información sobre:\n";
    $welcomeMessage .= "- Nuevos proyectos y servicios\n";
    $welcomeMessage .= "- Promociones especiales\n";
    $welcomeMessage .= "- Consejos de construcción y mantenimiento\n\n";
    $welcomeMessage .= "Saludos cordiales,\n";
    $welcomeMessage .= "Equipo GESCOM\n\n";
    $welcomeMessage .= "---\n";
    $welcomeMessage .= "Constructora GESCOM SAC\n";
    $welcomeMessage .= "Lima, Perú\n";
    $welcomeMessage .= "Tel: +51 975 130 810\n";
    $welcomeMessage .= "www.constructoragescom.com\n";
    
    $welcomeHeaders = "From: newsletter@constructoragescom.com\r\n";
    $welcomeHeaders .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    @mail($email, $welcomeSubject, $welcomeMessage, $welcomeHeaders);
    
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
