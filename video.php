<?php
/**
 * Servidor de Videos Protegidos
 * 
 * Este script verifica tokens temporales, referers y permisos
 * antes de servir videos con soporte de streaming parcial (Range requests)
 */

// Cargar variables de entorno
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

// Cargar configuración
loadEnv(__DIR__ . '/.env');

// Configuración
$SECRET_KEY = getenv('SECRET_KEY') ?: 'change-this-secret-key-in-production';
$ALLOWED_DOMAIN = getenv('ALLOWED_DOMAIN') ?: 'https://midominio.com';
$VIDEO_PATH = getenv('VIDEO_PATH') ?: '/volume1/web/videos/';

// Si estamos en Heroku, usar ruta local
if (getenv('DYNO')) {
    $VIDEO_PATH = __DIR__ . '/videos/';
}

/**
 * Enviar respuesta de error
 */
function sendError($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $message
    ]);
    exit;
}

/**
 * Validar el referer
 */
function validateReferer($referer, $allowedDomain) {
    if (empty($referer)) {
        return false;
    }
    
    $refererHost = parse_url($referer, PHP_URL_HOST);
    $allowedHost = parse_url($allowedDomain, PHP_URL_HOST);
    
    // Permitir localhost en desarrollo
    if (in_array($refererHost, ['localhost', '127.0.0.1', '::1'])) {
        return true;
    }
    
    return $refererHost === $allowedHost;
}

/**
 * Validar el token HMAC
 */
function validateToken($file, $expires, $token, $secretKey) {
    $expectedToken = hash_hmac('sha256', $file . $expires, $secretKey);
    return hash_equals($expectedToken, $token);
}

/**
 * Servir archivo con soporte de Range requests
 */
function serveVideo($filePath) {
    $fileSize = filesize($filePath);
    $fileName = basename($filePath);
    $mimeType = mime_content_type($filePath);
    
    // Headers básicos
    header('Content-Type: ' . $mimeType);
    header('Accept-Ranges: bytes');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Cache-Control: public, max-age=3600');
    
    // Verificar si es una solicitud de rango
    $range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;
    
    if ($range) {
        // Parsear el rango
        list($param, $range) = explode('=', $range);
        
        if (strtolower(trim($param)) !== 'bytes') {
            sendError(400, 'Invalid range parameter');
        }
        
        $range = explode('-', $range);
        $start = intval($range[0]);
        $end = (isset($range[1]) && is_numeric($range[1])) ? intval($range[1]) : $fileSize - 1;
        
        // Validar rango
        if ($start > $end || $start > $fileSize - 1 || $end >= $fileSize) {
            header('Content-Range: bytes */' . $fileSize);
            sendError(416, 'Requested range not satisfiable');
        }
        
        $length = $end - $start + 1;
        
        // Headers para respuesta parcial
        http_response_code(206);
        header('Content-Length: ' . $length);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        
        // Abrir y servir el rango solicitado
        $fp = fopen($filePath, 'rb');
        fseek($fp, $start);
        
        $buffer = 1024 * 8; // 8KB buffer
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        
        fclose($fp);
    } else {
        // Servir archivo completo
        header('Content-Length: ' . $fileSize);
        readfile($filePath);
    }
    
    exit;
}

// ========================================
// INICIO DEL SCRIPT PRINCIPAL
// ========================================

// Obtener parámetros
$file = isset($_GET['file']) ? $_GET['file'] : null;
$token = isset($_GET['token']) ? $_GET['token'] : null;
$expires = isset($_GET['expires']) ? $_GET['expires'] : null;

// Validar que existan los parámetros necesarios
if (!$file || !$token || !$expires) {
    sendError(400, 'Missing required parameters: file, token, expires');
}

// Validar que el token no haya expirado
if (time() > intval($expires)) {
    sendError(403, 'Token has expired');
}

// Validar el token HMAC
if (!validateToken($file, $expires, $token, $SECRET_KEY)) {
    sendError(403, 'Invalid token');
}

// Validar el referer
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if (!validateReferer($referer, $ALLOWED_DOMAIN)) {
    sendError(403, 'Invalid referer. Access denied.');
}

// Construir ruta completa del archivo
// Prevenir directory traversal
$file = str_replace(['../', '..\\'], '', $file);
$filePath = rtrim($VIDEO_PATH, '/') . '/' . ltrim($file, '/');

// Verificar que el archivo existe
if (!file_exists($filePath)) {
    sendError(404, 'Video not found');
}

// Verificar que es un archivo (no un directorio)
if (!is_file($filePath)) {
    sendError(403, 'Invalid file');
}

// Servir el video
serveVideo($filePath);

