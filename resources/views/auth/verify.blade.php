<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} | Verificar Correo</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 50%, rgba(168, 85, 247, 0.06) 0%, transparent 50%);
            animation: bgShift 20s ease-in-out infinite alternate;
        }
        @keyframes bgShift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-5%, -5%) rotate(5deg); }
        }
        .login-container { position: relative; z-index: 1; width: 100%; max-width: 480px; }
        .login-header { text-align: center; margin-bottom: 32px; }
        .login-header .logo {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.3);
        }
        .login-header .logo svg { width: 32px; height: 32px; fill: white; }
        .login-header h1 { color: #f1f5f9; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .login-header p { color: #94a3b8; font-size: 14px; margin-top: 6px; }
        .login-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 20px; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; }
        .alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #86efac; }
        .verify-text { color: #cbd5e1; font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .btn-link {
            background: none !important;
            border: none;
            color: #818cf8;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.2s;
        }
        .btn-link:hover { color: #a5b4fc; text-decoration: underline; }
        .login-footer { text-align: center; margin-top: 24px; }
        .login-footer a { color: #818cf8; font-size: 13px; text-decoration: none; transition: color 0.2s; }
        .login-footer a:hover { color: #a5b4fc; }
        @media (max-width: 480px) {
            .login-card { padding: 24px; }
            .login-header h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h1>{{ config('app.name', 'ControlVenta') }}</h1>
            <p>Verificación de correo electrónico</p>
        </div>

        <div class="login-card">
            @if (session('resent'))
                <div class="alert alert-success">Se ha enviado un nuevo enlace de verificación a tu correo electrónico.</div>
            @endif

            <div class="verify-text">
                Antes de continuar, por favor verifica tu correo electrónico para obtener el enlace de verificación.
            </div>

            <div class="verify-text">
                Si no recibiste el correo,
                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-link">haz clic aquí para solicitar otro</button>.
                </form>
            </div>

            <div class="login-footer">
                <a href="{{ route('login') }}">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
