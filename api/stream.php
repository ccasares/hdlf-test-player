<?php
/**
 * API Proxy de Streaming de Video
 * 
 * Sirve videos desde una URL externa sin exponerla al frontend
 * Requiere token de sesión válido para acceder
 * Soporta Range requests para streaming parcial
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
        
        if (strpos($line, '=') === false) {
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

loadEnv(__DIR__ . '/../.env');

// Configuración
$SESSION_DIR = __DIR__ . '/../sessions/';
$VIDEO_URL = 'http://quasars.ddns.net:8880/videos/webtest.mp4';

/**
 * Enviar error
 */
function sendError($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

/**
 * Validar sesión
 */
function validateSession($dir, $token) {
    $filename = $dir . 'session_' . $token . '.json';
    
    if (!file_exists($filename)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($filename), true);
    
    if (!$data) {
        return false;
    }
    
    // Verificar expiración
    if ($data['expires'] < time()) {
        unlink($filename);
        return false;
    }
    
    return $data;
}

/**
 * Obtener cabeceras del video remoto
 */
function getRemoteVideoHeaders($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'content_length' => $contentLength,
        'content_type' => $contentType
    ];
}

/**
 * Streaming de video con soporte de Range requests
 */
function streamVideo($url, $range = null) {
    // Obtener información del video
    $info = getRemoteVideoHeaders($url);
    
    if ($info['http_code'] !== 200) {
        sendError(502, 'Remote video not available');
    }
    
    $fileSize = $info['content_length'];
    $contentType = $info['content_type'] ?: 'video/mp4';
    
    // Headers básicos
    header('Content-Type: ' . $contentType);
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, max-age=3600');
    
    // Procesar Range request
    if ($range) {
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
        
        // Streaming con Range
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RANGE, $start . '-' . $end);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        
        // Escribir directamente al output
        curl_exec($ch);
        curl_close($ch);
    } else {
        // Servir archivo completo
        header('Content-Length: ' . $fileSize);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        
        curl_exec($ch);
        curl_close($ch);
    }
    
    exit;
}

// ========================================
// INICIO DEL SCRIPT PRINCIPAL
// ========================================

// Obtener token de sesión
$token = $_GET['token'] ?? $_SERVER['HTTP_X_SESSION_TOKEN'] ?? '';

if (!$token) {
    sendError(401, 'Session token required');
}

// Validar sesión
$session = validateSession($SESSION_DIR, $token);

if (!$session) {
    sendError(403, 'Invalid or expired session token');
}

// Obtener video ID (por ahora solo hay uno)
$videoId = $_GET['video'] ?? $session['video_id'] ?? 'webtest';

// Por ahora solo soportamos un video, pero puedes extenderlo
// para tener un mapeo de video_id => URL
$videoUrls = [
    'webtest' => 'http://quasars.ddns.net:8880/videos/webtest.mp4'
];

if (!isset($videoUrls[$videoId])) {
    sendError(404, 'Video not found');
}

$videoUrl = $videoUrls[$videoId];

// Obtener Range header si existe
$range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;

// Stream del video
streamVideo($videoUrl, $range);

