<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} | Confirmar Contraseña</title>
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
        .login-container { position: relative; z-index: 1; width: 100%; max-width: 440px; }
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
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #cbd5e1; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .input-wrapper { position: relative; }
        .input-wrapper .icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #64748b; width: 18px; height: 18px;
            pointer-events: none;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 12px;
            color: #f1f5f9;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            outline: none;
        }
        .form-control::placeholder { color: #475569; }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: rgba(15, 23, 42, 0.8);
        }
        .form-control.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1); }
        .invalid-feedback { display: block; color: #f87171; font-size: 12px; margin-top: 6px; }
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4); }
        .btn-login:active { transform: translateY(0); }
        .alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 20px; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; }
        .alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #86efac; }
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
            <p>{{ __('Confirma tu contraseña para continuar') }}</p>
        </div>

        <div class="login-card">
            @if ($errors->any())
                <div class="alert alert-danger"><strong>{{ $errors->first() }}</strong></div>
            @endif

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="form-group">
                    <label for="password">{{ __('Contraseña') }}</label>
                    <div class="input-wrapper">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('••••••••') }}" required autofocus>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-login">{{ __('Confirmar contraseña') }}</button>
            </form>

            <div class="login-footer">
                <a href="{{ route('password.request') }}">{{ __('¿Olvidaste tu contraseña?') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
