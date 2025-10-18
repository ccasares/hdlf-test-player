<?php
/**
 * API de Gestión de Sesiones Temporales
 * 
 * Genera tokens de sesión temporal para acceder al reproductor de video
 * Los tokens expiran después de un tiempo determinado
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
$SECRET_KEY = getenv('SECRET_KEY') ?: 'change-this-secret-key-in-production';
$SESSION_DIR = __DIR__ . '/../sessions/';
$SESSION_DURATION = 3600; // 1 hora

// Crear directorio de sesiones si no existe
if (!file_exists($SESSION_DIR)) {
    mkdir($SESSION_DIR, 0755, true);
}

/**
 * Limpiar sesiones expiradas
 */
function cleanExpiredSessions($dir) {
    $files = glob($dir . 'session_*.json');
    $now = time();
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && $data['expires'] < $now) {
            unlink($file);
        }
    }
}

/**
 * Generar token de sesión
 */
function generateSessionToken($secretKey) {
    $randomBytes = random_bytes(32);
    $timestamp = time();
    $data = $randomBytes . $timestamp;
    
    return hash_hmac('sha256', $data, $secretKey);
}

/**
 * Crear nueva sesión
 */
function createSession($dir, $duration, $secretKey, $videoId = 'webtest') {
    $token = generateSessionToken($secretKey);
    $expires = time() + $duration;
    
    $sessionData = [
        'token' => $token,
        'created' => time(),
        'expires' => $expires,
        'video_id' => $videoId,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $filename = $dir . 'session_' . $token . '.json';
    file_put_contents($filename, json_encode($sessionData));
    
    return $sessionData;
}

/**
 * Validar sesión existente
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

// ========================================
// RUTAS DE LA API
// ========================================

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'create';

// Limpiar sesiones expiradas
cleanExpiredSessions($SESSION_DIR);

switch ($method) {
    case 'POST':
        if ($action === 'create') {
            // Crear nueva sesión
            $input = json_decode(file_get_contents('php://input'), true);
            $videoId = $input['video_id'] ?? 'webtest';
            $duration = $input['duration'] ?? $SESSION_DURATION;
            
            // Validación opcional: password o acceso code
            $accessCode = $input['access_code'] ?? null;
            $expectedCode = getenv('ACCESS_CODE') ?: null;
            
            if ($expectedCode && $accessCode !== $expectedCode) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid access code'
                ]);
                exit;
            }
            
            $session = createSession($SESSION_DIR, $duration, $SECRET_KEY, $videoId);
            
            echo json_encode([
                'success' => true,
                'token' => $session['token'],
                'expires' => $session['expires'],
                'expires_in' => $duration,
                'expires_at' => date('Y-m-d H:i:s', $session['expires'])
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
        }
        break;
        
    case 'GET':
        if ($action === 'validate') {
            // Validar token existente
            $token = $_GET['token'] ?? '';
            
            if (!$token) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Token required'
                ]);
                exit;
            }
            
            $session = validateSession($SESSION_DIR, $token);
            
            if ($session) {
                echo json_encode([
                    'success' => true,
                    'valid' => true,
                    'expires' => $session['expires'],
                    'remaining' => $session['expires'] - time(),
                    'video_id' => $session['video_id']
                ]);
            } else {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'valid' => false,
                    'error' => 'Invalid or expired token'
                ]);
            }
        } else {
            // Información de la API
            echo json_encode([
                'api' => 'Session Management',
                'version' => '1.0',
                'endpoints' => [
                    'POST /api/session.php?action=create' => 'Create new session token',
                    'GET /api/session.php?action=validate&token=...' => 'Validate existing token'
                ]
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
}

