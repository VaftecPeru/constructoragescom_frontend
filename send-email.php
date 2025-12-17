<?php
/**
 * Script de envío de correo para formulario de contacto
 * Constructora GESCOM SAC
 */

// Cargar dependencias
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Cargar variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

error_reporting(0);
ini_set('display_errors', 0);

// Configuración desde variables de entorno
$config = [
    'smtp_host'     => $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com',
    'smtp_port'     => $_ENV['SMTP_PORT'] ?? 587,
    'smtp_user'     => $_ENV['SMTP_USER'] ?? '',
    'smtp_password' => $_ENV['SMTP_PASSWORD'] ?? '',
    'destinatario'  => $_ENV['MAIL_TO'] ?? '',
    'from_name'     => 'GESCOM Web',
    'asunto_prefijo'=> '[GESCOM Web] '
];


// Headers para CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);
    exit;
}

// Obtener y sanitizar datos
$nombre = isset($_POST['name']) ? htmlspecialchars(strip_tags(trim($_POST['name']))) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$telefono = isset($_POST['phone']) ? htmlspecialchars(strip_tags(trim($_POST['phone']))) : '';
$proyecto = isset($_POST['project']) ? htmlspecialchars(strip_tags(trim($_POST['project']))) : '';
$asunto = isset($_POST['subject']) ? htmlspecialchars(strip_tags(trim($_POST['subject']))) : 'Consulta desde el sitio web';
$mensaje = isset($_POST['message']) ? htmlspecialchars(strip_tags(trim($_POST['message']))) : '';

// Validaciones
$errores = [];

if (empty($nombre)) {
    $errores[] = "El nombre es requerido";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo electrónico no es válido";
}

if (empty($mensaje)) {
    $errores[] = "El mensaje es requerido";
}

// Si hay errores, retornar
if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Error de validación",
        "errors" => $errores
    ]);
    exit;
}

// Construir el cuerpo del correo
$cuerpoTexto = "
===========================================
NUEVO MENSAJE DE CONTACTO - GESCOM WEB
===========================================

DATOS DEL CLIENTE:
------------------
Nombre: $nombre
Correo: $email
Teléfono: $telefono
Proyecto: $proyecto

MENSAJE:
--------
$mensaje

===========================================
Este mensaje fue enviado desde el formulario
de contacto del sitio web de GESCOM.
===========================================
";

// URL base del sitio
$urlSitio = $_ENV['SITE_URL'] ?? 'http://localhost:8000';

// Cuerpo HTML
$cuerpoHTML = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;'>
    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color: #f4f4f4; padding: 20px 0;'>
        <tr>
            <td align='center'>
                <table role='presentation' width='600' cellspacing='0' cellpadding='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                    
                    <!-- Header con logo -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #1B2A4E 0%, #2d4a7c 100%); padding: 30px 40px; text-align: center;'>
                            <table role='presentation' width='100%' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td style='text-align: center;'>
                                        <img src='$urlSitio/img/rueda.png' alt='GESCOM' width='50' height='50' style='display: block; margin: 0 auto 10px auto;'>
                                        <h1 style='color: #ffffff; margin: 10px 0 5px 0; font-size: 28px; font-weight: bold;'>GESCOM</h1>
                                        <p style='color: #D4A853; margin: 0; font-size: 12px; letter-spacing: 2px;'>CONSTRUCTORA SAC</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Banner de notificación -->
                    <tr>
                        <td style='background-color: #D4A853; padding: 15px 40px; text-align: center;'>
                            <h2 style='color: #1B2A4E; margin: 0; font-size: 18px; font-weight: 600;'>
                                NUEVO MENSAJE DE CONTACTO
                            </h2>
                        </td>
                    </tr>
                    
                    <!-- Contenido principal -->
                    <tr>
                        <td style='padding: 40px;'>
                            
                            <!-- Datos del cliente -->
                            <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='margin-bottom: 30px;'>
                                <tr>
                                    <td style='padding-bottom: 15px; border-bottom: 2px solid #D4A853;'>
                                        <h3 style='color: #1B2A4E; margin: 0; font-size: 16px;'>
                                            <span style='color: #D4A853; font-weight: bold;'>|</span> DATOS DEL CLIENTE
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding-top: 20px;'>
                                        <table role='presentation' width='100%' cellspacing='0' cellpadding='8'>
                                            <tr>
                                                <td width='30%' style='color: #666; font-size: 14px; vertical-align: top;'>Nombre:</td>
                                                <td style='color: #1B2A4E; font-size: 14px; font-weight: 600;'>$nombre</td>
                                            </tr>
                                            <tr>
                                                <td style='color: #666; font-size: 14px; vertical-align: top;'>Correo:</td>
                                                <td><a href='mailto:$email' style='color: #D4A853; text-decoration: none; font-size: 14px;'>$email</a></td>
                                            </tr>
                                            <tr>
                                                <td style='color: #666; font-size: 14px; vertical-align: top;'>Teléfono:</td>
                                                <td><a href='tel:$telefono' style='color: #D4A853; text-decoration: none; font-size: 14px;'>$telefono</a></td>
                                            </tr>
                                            <tr>
                                                <td style='color: #666; font-size: 14px; vertical-align: top;'>Proyecto:</td>
                                                <td style='color: #1B2A4E; font-size: 14px;'>$proyecto</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Mensaje -->
                            <table role='presentation' width='100%' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td style='padding-bottom: 15px; border-bottom: 2px solid #D4A853;'>
                                        <h3 style='color: #1B2A4E; margin: 0; font-size: 16px;'>
                                            <span style='color: #D4A853; font-weight: bold;'>|</span> MENSAJE
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding-top: 20px;'>
                                        <div style='background-color: #f8f9fa; border-left: 4px solid #D4A853; padding: 20px; border-radius: 0 8px 8px 0;'>
                                            <p style='color: #333; font-size: 14px; line-height: 1.8; margin: 0; white-space: pre-wrap;'>$mensaje</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Botón de respuesta -->
                            <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='margin-top: 30px;'>
                                <tr>
                                    <td align='center'>
                                        <a href='mailto:$email?subject=Re: $asunto' style='display: inline-block; background-color: #D4A853; color: #1B2A4E; text-decoration: none; padding: 14px 40px; border-radius: 6px; font-weight: 600; font-size: 14px;'>
                                            RESPONDER AL CLIENTE
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #1B2A4E; padding: 30px 40px;'>
                            <table role='presentation' width='100%' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td style='text-align: center; padding-bottom: 15px;'>
                                        <img src='$urlSitio/img/rueda.png' alt='GESCOM' width='30' height='30' style='display: inline-block; vertical-align: middle;'>
                                        <span style='color: #ffffff; font-size: 16px; font-weight: bold; margin-left: 8px; vertical-align: middle;'>GESCOM</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align: center; color: #888; font-size: 12px; line-height: 2;'>
                                        <span style='color: #D4A853;'>&#9679;</span> Lima, Perú<br>
                                        <span style='color: #D4A853;'>&#9679;</span> info@constructoragescom.com<br>
                                        <span style='color: #D4A853;'>&#9679;</span> +51 975 130 810
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align: center; padding-top: 20px; border-top: 1px solid #333; margin-top: 15px;'>
                                        <p style='color: #666; font-size: 11px; margin: 15px 0 0 0;'>
                                            Este mensaje fue enviado desde el formulario de contacto<br>
                                            del sitio web de Constructora GESCOM SAC
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";



$mail = new PHPMailer(true);

try {
    // Usando función mail() del servidor
    $mail->isMail();
    
    $mail->CharSet = 'UTF-8';
    
    // Remitente y destinatario
    $mail->setFrom('info@constructoragescom.com', $config['from_name']);
    $mail->addAddress($config['destinatario']);
    $mail->addReplyTo($email, $nombre);
    
    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = $config['asunto_prefijo'] . $asunto;
    $mail->Body    = $cuerpoHTML;
    $mail->AltBody = $cuerpoTexto;
    
    // Enviar
    $mail->send();
    
    echo json_encode([
        "success" => true,
        "message" => "¡Mensaje enviado correctamente! Nos pondremos en contacto contigo pronto."
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al enviar el mensaje: " . $mail->ErrorInfo
    ]);
}
?>
