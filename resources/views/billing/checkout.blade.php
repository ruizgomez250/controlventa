<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contratar Control Venta</title>
    <style>
        body { background: #f4f6f9; color: #243142; font-family: Arial, sans-serif; margin: 0; }
        main { margin: 70px auto; max-width: 520px; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 8px 30px #1f29371a; padding: 32px; }
        label { display: block; font-weight: 700; margin: 20px 0 6px; }
        input { border: 1px solid #b8c2cc; border-radius: 6px; box-sizing: border-box; padding: 11px; width: 100%; }
        button { background: #1769e0; border: 0; border-radius: 6px; color: white; cursor: pointer; font-weight: 700; margin-top: 22px; padding: 12px; width: 100%; }
        .price { font-size: 28px; font-weight: 700; }
        .error { color: #b42318; font-size: 14px; }
    </style>
</head>
<body>
<main>
    <div class="card">
        <h1>Empezá con Control Venta</h1>
        <p class="price">{{ number_format($amount, 0, ',', '.') }} {{ $currency }}</p>
        <p>Al confirmarse el pago recibirás un enlace personal para crear tu empresa.</p>
        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf
            <label for="email">Correo de contacto</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255">
            @error('email') <div class="error">{{ $message }}</div> @enderror
            <button type="submit">Continuar al pago</button>
        </form>
    </div>
</main>
</body>
</html>
