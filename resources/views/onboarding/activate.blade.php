<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activar empresa</title>
    <style>
        body { background: #f4f6f9; color: #243142; font-family: Arial, sans-serif; margin: 0; }
        main { margin: 48px auto; max-width: 680px; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 8px 30px #1f29371a; padding: 32px; }
        h1 { margin-top: 0; }
        label { display: block; font-weight: 700; margin: 18px 0 6px; }
        input { border: 1px solid #b8c2cc; border-radius: 6px; box-sizing: border-box; padding: 11px; width: 100%; }
        button { background: #1769e0; border: 0; border-radius: 6px; color: white; cursor: pointer; font-weight: 700; margin-top: 24px; padding: 12px 18px; width: 100%; }
        .error { color: #b42318; font-size: 14px; margin-top: 5px; }
        .paid { color: #067647; font-weight: 700; }
    </style>
</head>
<body>
<main>
    <div class="card">
        <p class="paid">Pago confirmado</p>
        <h1>Configurá tu empresa</h1>
        <p>Este enlace vence el {{ $order->activation_expires_at->format('d/m/Y H:i') }} y puede usarse una sola vez.</p>
        <form method="POST" action="{{ route('onboarding.store', ['token' => $token]) }}">
            @csrf
            @error('empresa') <div class="error">{{ $message }}</div> @enderror
            <label for="nombre">Nombre de la empresa</label>
            <input id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="255">
            @error('nombre') <div class="error">{{ $message }}</div> @enderror
            <label for="dominio">Subdominio</label>
            <input id="dominio" name="dominio" value="{{ old('dominio') }}" required maxlength="63" pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
            @error('dominio') <div class="error">{{ $message }}</div> @enderror
            <label for="email_admin">Correo del administrador</label>
            <input id="email_admin" type="email" name="email_admin" value="{{ old('email_admin', $order->customer_email) }}" required maxlength="255">
            @error('email_admin') <div class="error">{{ $message }}</div> @enderror
            <label for="password_admin">Contraseña</label>
            <input id="password_admin" type="password" name="password_admin" required minlength="10">
            @error('password_admin') <div class="error">{{ $message }}</div> @enderror
            <label for="password_admin_confirmation">Confirmar contraseña</label>
            <input id="password_admin_confirmation" type="password" name="password_admin_confirmation" required minlength="10">
            <button type="submit">Crear mi empresa</button>
        </form>
    </div>
</main>
</body>
</html>
