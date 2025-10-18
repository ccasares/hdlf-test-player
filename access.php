<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Reproductor Seguro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .access-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        
        .access-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            text-align: center;
            color: white;
        }
        
        .access-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .access-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .access-body {
            padding: 40px;
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-message h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .welcome-message p {
            color: #666;
            line-height: 1.6;
        }
        
        .access-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-help {
            font-size: 12px;
            color: #999;
        }
        
        .btn-access {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        .btn-access:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-access:active {
            transform: translateY(0);
        }
        
        .btn-access:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            color: #1976d2;
        }
        
        .alert-error {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
        }
        
        .alert-success {
            background: #e8f5e9;
            border: 1px solid #4caf50;
            color: #2e7d32;
        }
        
        .loading {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .features {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }
        
        .features h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .feature-list {
            list-style: none;
        }
        
        .feature-list li {
            padding: 10px 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .feature-list li::before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            font-size: 18px;
        }
        
        .quick-access {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        
        .quick-access p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .btn-quick {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-quick:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="access-container">
        <div class="access-header">
            <h1>🔒 Acceso Seguro</h1>
            <p>Reproductor de Video Protegido</p>
        </div>
        
        <div class="access-body">
            <div id="alertContainer"></div>
            
            <div class="welcome-message">
                <h2>Bienvenido</h2>
                <p>Para acceder al reproductor de video, necesitas generar un token de sesión temporal.</p>
            </div>
            
            <form id="accessForm" class="access-form">
                <div class="form-group" id="codeGroup" style="display: none;">
                    <label for="accessCode">Código de Acceso (Opcional)</label>
                    <input 
                        type="password" 
                        id="accessCode" 
                        placeholder="Ingresa el código si se requiere"
                    >
                    <span class="form-help">Solo requerido si el administrador configuró ACCESS_CODE</span>
                </div>
                
                <button type="submit" class="btn-access" id="submitBtn">
                    🎬 Acceder al Reproductor
                </button>
            </form>
            
            <div class="quick-access">
                <p><strong>Acceso Rápido:</strong> Si no se requiere código de acceso</p>
                <button class="btn-quick" onclick="quickAccess()">
                    ⚡ Acceso Directo
                </button>
            </div>
            
            <div class="features">
                <h3>Características de Seguridad</h3>
                <ul class="feature-list">
                    <li>URL del video nunca expuesta en el navegador</li>
                    <li>Tokens de sesión temporal con expiración</li>
                    <li>Transmisión encriptada vía API proxy</li>
                    <li>Protección contra descarga no autorizada</li>
                    <li>Acceso controlado y monitoreado</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        /**
         * Mostrar alerta
         */
        function showAlert(message, type = 'info') {
            const container = document.getElementById('alertContainer');
            const icons = {
                'info': 'ℹ️',
                'error': '❌',
                'success': '✅'
            };
            
            container.innerHTML = `
                <div class="alert alert-${type}">
                    <span>${icons[type]}</span>
                    <span>${message}</span>
                </div>
            `;
        }
        
        /**
         * Crear sesión y obtener token
         */
        async function createSession(accessCode = null) {
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Generando token...';
            
            try {
                const payload = {
                    video_id: 'webtest',
                    duration: 3600 // 1 hora
                };
                
                if (accessCode) {
                    payload.access_code = accessCode;
                }
                
                const response = await fetch('/api/session.php?action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('✓ Token generado correctamente. Redirigiendo...', 'success');
                    
                    // Redirigir al reproductor con el token
                    setTimeout(() => {
                        window.location.href = `/player.html?token=${data.token}`;
                    }, 1000);
                } else {
                    showAlert(data.error || 'Error al generar token', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                showAlert('Error de conexión: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
        
        /**
         * Acceso rápido sin código
         */
        async function quickAccess() {
            await createSession();
        }
        
        /**
         * Manejar envío del formulario
         */
        document.getElementById('accessForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const accessCode = document.getElementById('accessCode').value.trim();
            await createSession(accessCode || null);
        });
        
        /**
         * Verificar si se requiere código de acceso
         */
        async function checkAccessCodeRequired() {
            try {
                // Intentar crear sesión de prueba
                const response = await fetch('/api/session.php?action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        video_id: 'webtest',
                        duration: 10
                    })
                });
                
                const data = await response.json();
                
                if (!data.success && data.error && data.error.includes('access code')) {
                    // Se requiere código de acceso
                    document.getElementById('codeGroup').style.display = 'flex';
                    showAlert('⚠️ Se requiere código de acceso para continuar', 'info');
                }
            } catch (error) {
                console.error('Error checking access code:', error);
            }
        }
        
        // Verificar al cargar
        checkAccessCodeRequired();
    </script>
</body>
</html>

