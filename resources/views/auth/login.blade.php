<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenAck - Iniciar Sesión</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 32px;
            color: white;
            font-weight: bold;
        }
        
        .login-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #f8f9fa;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-input.error {
            border-color: #dc3545;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .remember-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
        }
        
        .remember-checkbox {
            width: 16px;
            height: 16px;
            accent-color: #667eea;
        }
        
        .remember-label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }
        
        .login-button {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .login-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .demo-credentials {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 12px;
            color: #495057;
        }
        
        .demo-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #28a745;
        }
        
        .demo-item {
            margin-bottom: 4px;
        }
        
        .demo-item strong {
            color: #333;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        .password-toggle {
            position: relative;
        }
        
        .toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 14px;
        }
        
        .toggle-btn:hover {
            color: #333;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">GA</div>
            <h1 class="login-title">GenAck POS</h1>
            <p class="login-subtitle">Sistema de Ventas y Gestión</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/login" id="loginForm">
            @csrf
            
            <div class="form-group">
                <label for="email" class="form-label">📧 Correo Electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input {{ $errors->has('email') ? 'error' : '' }}" 
                    value="{{ old('email') }}" 
                    required 
                    autocomplete="email"
                    placeholder="Ingresa tu correo electrónico"
                >
                @if ($errors->has('email'))
                    <div class="error-message">
                        ⚠️ {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="password" class="form-label">🔒 Contraseña</label>
                <div class="password-toggle">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input {{ $errors->has('password') ? 'error' : '' }}" 
                        required 
                        autocomplete="current-password"
                        placeholder="Ingresa tu contraseña"
                    >
                    <button type="button" class="toggle-btn" id="togglePassword">👁️</button>
                </div>
                @if ($errors->has('password'))
                    <div class="error-message">
                        ⚠️ {{ $errors->first('password') }}
                    </div>
                @endif
            </div>

            <div class="remember-group">
                <input type="checkbox" id="remember" name="remember" class="remember-checkbox">
                <label for="remember" class="remember-label">Recordar sesión</label>
            </div>

            <button type="submit" class="login-button" id="loginButton">
                <span id="buttonText">Iniciar Sesión</span>
                <span id="loadingSpinner" class="loading-spinner" style="display: none;"></span>
            </button>
        </form>

        <div class="footer-text">
            © {{ date('Y') }} GenAck. Sistema de Gestión Comercial
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });

            // Form submission with loading state
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const loadingSpinner = document.getElementById('loadingSpinner');

            loginForm.addEventListener('submit', function() {
                loginButton.disabled = true;
                buttonText.style.display = 'none';
                loadingSpinner.style.display = 'inline-block';
            });

            // Auto-fill demo credentials on click
            const demoCredentials = document.querySelector('.demo-credentials');
            demoCredentials.addEventListener('click', function(e) {
                if (e.target.textContent.includes('admin@genack.com')) {
                    document.getElementById('email').value = 'admin@genack.com';
                    document.getElementById('password').value = 'admin123';
                } else if (e.target.textContent.includes('vendedor@genack.com')) {
                    document.getElementById('email').value = 'vendedor@genack.com';
                    document.getElementById('password').value = 'vendedor123';
                }
            });

            // Focus management
            document.getElementById('email').focus();
            
            // Enter key handling
            document.getElementById('email').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('password').focus();
                }
            });
        });
    </script>
</body>
</html>