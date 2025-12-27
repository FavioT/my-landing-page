<?php
/**
 * Script de envío de emails con PHPMailer
 * Configurar las credenciales SMTP antes de usar
 */

// Habilitar CORS si es necesario (ajustar según tu dominio)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Si es una solicitud OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Cargar PHPMailer (ajustar ruta según instalación)
// Si usas Composer: require 'vendor/autoload.php';
// Si descargaste manualmente:
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// ============================================================================
// CONFIGURACIÓN SMTP - ¡MODIFICAR CON TUS DATOS!
// ============================================================================
$config = [
    'smtp_host'     => 'smtp.tuservidor.com',    // Servidor SMTP (ej: smtp.gmail.com)
    'smtp_port'     => 587,                       // Puerto (587 para TLS, 465 para SSL)
    'smtp_secure'   => 'tls',                     // 'tls' o 'ssl'
    'smtp_user'     => 'tu-email@tudominio.com',  // Tu email/usuario SMTP
    'smtp_pass'     => 'tu-contraseña',           // Tu contraseña o App Password
    'from_email'    => 'tu-email@tudominio.com',  // Email remitente
    'from_name'     => 'Landing Page',            // Nombre remitente
    'to_email'      => 'destino@tudominio.com',   // Email donde recibirás los mensajes
    'to_name'       => 'Administrador',           // Nombre destinatario
];

// ============================================================================
// OBTENER Y VALIDAR DATOS DEL FORMULARIO
// ============================================================================

// Obtener datos (soporta tanto form-data como JSON)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $message = trim($input['message'] ?? '');
} else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
}

// Validaciones básicas
$errors = [];

if (empty($name)) {
    $errors[] = 'El nombre es requerido';
}

if (empty($email)) {
    $errors[] = 'El email es requerido';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'El email no es válido';
}

if (empty($message)) {
    $errors[] = 'El mensaje es requerido';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

// Sanitizar datos
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// ============================================================================
// ENVIAR EMAIL CON PHPMAILER
// ============================================================================

$mail = new PHPMailer(true);

try {
    // Configuración del servidor
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port       = $config['smtp_port'];
    $mail->CharSet    = 'UTF-8';

    // Remitente y destinatario
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']);
    $mail->addReplyTo($email, $name);

    // Contenido del email
    $mail->isHTML(true);
    $mail->Subject = "Nuevo mensaje de contacto de: $name";
    
    // Cuerpo HTML
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #007bff; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #555; }
            .value { margin-top: 5px; padding: 10px; background: white; border-radius: 4px; }
            .footer { padding: 15px; font-size: 12px; color: #777; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2 style='margin:0;'>📩 Nuevo mensaje de contacto</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>👤 Nombre:</div>
                    <div class='value'>$name</div>
                </div>
                <div class='field'>
                    <div class='label'>📧 Email:</div>
                    <div class='value'><a href='mailto:$email'>$email</a></div>
                </div>
                <div class='field'>
                    <div class='label'>💬 Mensaje:</div>
                    <div class='value'>" . nl2br($message) . "</div>
                </div>
            </div>
            <div class='footer'>
                Este mensaje fue enviado desde el formulario de contacto de tu Landing Page.
            </div>
        </div>
    </body>
    </html>";

    // Cuerpo alternativo en texto plano
    $mail->AltBody = "Nuevo mensaje de contacto\n\n"
                   . "Nombre: $name\n"
                   . "Email: $email\n"
                   . "Mensaje:\n$message";

    $mail->send();
    
    echo json_encode([
        'success' => true, 
        'message' => '¡Mensaje enviado correctamente! Te responderemos pronto.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error al enviar el mensaje. Por favor, intenta nuevamente.',
        'debug' => $mail->ErrorInfo // Quitar en producción
    ]);
}
