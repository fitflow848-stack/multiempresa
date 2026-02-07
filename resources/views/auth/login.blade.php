<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - GenAck</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #696cff;
            /* Brand Purple */
            --primary-dark: #5f61e6;
            --secondary: #8592a3;
            --success: #71dd37;
            --info: #03c3ec;
            --warning: #ffab00;
            --danger: #ff3e1d;
            --dark: #233446;
            --light: #f5f5f9;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --text-main: #566a7f;
            --text-light: #697a8d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            background-color: var(--white);
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
        }

        /* --- Left Side: Brand Visuals --- */
        .brand-side {
            flex: 1.2;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            overflow: hidden;
            padding: 2rem;
            display: none;
            /* Hidden on mobile by default */
        }

        @media (min-width: 900px) {
            .brand-side {
                display: flex;
            }
        }

        /* Abstract shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            z-index: 1;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -50px;
            left: -50px;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.05));
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            bottom: -100px;
            right: -100px;
            background: linear-gradient(to top left, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.05));
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            top: 40%;
            right: 15%;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 450px;
        }

        .brand-logo-large {
            width: 120px;
            height: auto;
            margin-bottom: 2rem;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.15));
            background: white;
            padding: 15px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .brand-desc {
            font-size: 1.1rem;
            font-weight: 300;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* --- Right Side: Login Form --- */
        .form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--white);
            position: relative;
            padding: 2rem;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-logo {
            display: block;
            width: 60px;
            margin: 0 auto 1.5rem;
            display: none;
        }

        @media (max-width: 899px) {
            .mobile-logo {
                display: block;
            }
        }

        .auth-header {
            margin-bottom: 2.5rem;
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* Modern Input Styling */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 0;
            font-size: 1rem;
            color: var(--text-main);
            background: transparent;
            border: none;
            border-bottom: 2px solid var(--gray-300);
            transition: all 0.3s;
            border-radius: 0;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-label {
            position: absolute;
            top: 0.75rem;
            left: 0;
            font-size: 1rem;
            color: var(--gray-500);
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .form-control:focus~.form-label,
        .form-control:not(:placeholder-shown)~.form-label {
            top: -0.75rem;
            font-size: 0.85rem;
            color: var(--primary);
            font-weight: 500;
        }

        /* Error states */
        .form-control.is-invalid {
            border-bottom-color: var(--danger);
        }

        .form-control.is-invalid~.form-label {
            color: var(--danger);
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .password-toggle {
            position: absolute;
            right: 0;
            top: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-500);
            padding: 5px;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        /* Actions */
        .actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--text-light);
            user-select: none;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            height: 18px;
            width: 18px;
            background-color: var(--white);
            border: 2px solid var(--gray-400);
            border-radius: 4px;
            margin-right: 8px;
            position: relative;
            transition: all 0.2s;
        }

        .custom-checkbox:hover .checkmark {
            border-color: var(--primary);
        }

        .custom-checkbox input:checked~.checkmark {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checkbox input:checked~.checkmark:after {
            display: block;
        }

        .custom-checkbox .checkmark:after {
            left: 5px;
            top: 1px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .alert-box {
            background-color: #ffe0db;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: var(--danger);
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .forgot-link {
            font-size: 0.9rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Button */
        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(105, 108, 255, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <!-- Left Side: Branding -->
    <div class="brand-side">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>

        <div class="brand-content">
            <img src="{{ asset('assets/img/logo.png') }}" alt="GenAck Logo" class="brand-logo-large">
            <h1 class="brand-title">GenAck POS</h1>
            <p class="brand-desc">Gestiona tu negocio de manera inteligente, segura y eficiente con nuestra plataforma
                integral.</p>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="form-side">
        <div class="login-wrapper">
            <!-- Mobile Logo -->
            <img src="{{ asset('assets/img/logo.png') }}" alt="GenAck Logo" class="mobile-logo">

            <div class="auth-header">
                <h2 class="auth-title">¡Bienvenido de nuevo! 👋</h2>
                <p class="auth-subtitle">Ingresa tus credenciales para acceder a tu cuenta.</p>
            </div>

            @if ($errors->any())
                <div class="alert-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <input type="email" id="email" name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder=" "
                        value="{{ old('email', request()->cookie('remember_email')) }}" required autofocus>
                    <label for="email" class="form-label">Correo Electrónico</label>
                    @if ($errors->has('email'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <input type="password" id="password" name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder=" "
                        required>
                    <label for="password" class="form-label">Contraseña</label>
                    <button type="button" class="password-toggle" id="togglePassword"
                        aria-label="Toggle password visibility">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                    @if ($errors->has('password'))
                        <div class="invalid-feedback">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>

                <div class="actions-row">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : (request()->cookie('remember_email') ? 'checked' : '') }}>
                        <span class="checkmark"></span>
                        Recordarme
                    </label>
                    {{-- <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a> --}}
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <span id="btnText">Iniciar Sesión</span>
                    <span id="loadingSpinner" class="spinner" style="display: none;"></span>
                </button>

                <div class="footer">
                    © {{ date('Y') }} GenAck. Sistema de Gestión.
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'password') {
                    this.innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                } else {
                    this.innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                }
            });

            // Loading state
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('loadingSpinner');

            form.addEventListener('submit', function() {
                btn.disabled = true;
                btnText.style.display = 'none';
                spinner.style.display = 'block';
            });
        });
    </script>
</body>

</html>
