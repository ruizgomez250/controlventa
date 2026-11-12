<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Descubrí el catálogo de MiniFashion. Elegí tus favoritos y coordiná tu pedido por WhatsApp.">
    <meta name="tienda-api" content="{{ url('/api/tienda/v1') }}">
    <title>MiniFashion · Tu próximo favorito</title>
    <link rel="stylesheet" href="{{ asset('tienda/tienda.css') }}">
    <script src="{{ asset('tienda/tienda.js') }}" defer></script>
</head>
<body>
<svg class="icon-library" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs>
<symbol id="i-bag" viewBox="0 0 24 24"><path d="M5 7h14l1 14H4L5 7Zm3 0V5a4 4 0 0 1 8 0v2"/></symbol>
<symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></symbol>
<symbol id="i-heart" viewBox="0 0 24 24"><path d="M20.8 4.6c-2.4-2.2-6-1.5-8.8 1.5-2.8-3-6.4-3.7-8.8-1.5C-1 8.6 4 14 12 21c8-7 13-12.4 8.8-16.4Z"/></symbol>
<symbol id="i-arrow" viewBox="0 0 24 24"><path d="M4 12h16m-6-6 6 6-6 6"/></symbol>
<symbol id="i-close" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></symbol>
<symbol id="i-star" viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></symbol>
<symbol id="i-chat" viewBox="0 0 24 24"><path d="M21 11.5a9 9 0 0 1-13 8L3 21l1.5-5a9 9 0 1 1 16.5-4.5Z"/><path d="M8 8c0 4 2 6 6 7l2-2-2-1-1 1-3-3 1-1-1-2-2 1Z"/></symbol>
<symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></symbol>
<symbol id="i-shirt" viewBox="0 0 120 120"><path d="m40 22-24 15 13 23 12-6v44h38V54l12 6 13-23-24-15c-3 15-37 15-40 0Z"/><path d="M49 25c0 14 22 14 22 0"/></symbol>
</defs></svg>
<div class="announcement"><div class="wrap"><span id="announcement">Elegí tus favoritos y coordiná tu pedido por WhatsApp</span><span class="announcement-note">Hecho para elegir a tu ritmo</span></div></div>
<header class="header">
    <div class="wrap header-main">
        <a class="brand" href="{{ route('tienda.publica') }}" aria-label="Inicio de la tienda"><span class="brand-flower">✳</span><span id="brand-name">MiniFashion</span><span class="brand-dot">.</span></a>
        <form id="search-form" class="search" role="search"><label class="sr-only" for="search">Buscar en el catálogo</label><svg class="icon"><use href="#i-search"/></svg><input id="search" placeholder="¿Qué estás buscando hoy?" maxlength="100" autocomplete="off"><button type="submit">Buscar</button></form>
        <div class="header-actions">
            <button class="header-action" data-action="pedidos" aria-label="Ver mis pedidos"><svg class="icon"><use href="#i-clock"/></svg><span>Mis pedidos</span></button>
            <button class="header-action" data-section="favoritos" aria-label="Ver favoritos"><svg class="icon"><use href="#i-heart"/></svg><span>Favoritos</span></button>
            <button class="cart-button" data-action="carrito"><svg class="icon"><use href="#i-bag"/></svg><span class="cart-label">Mi carrito</span><b id="cart-count">0</b></button>
        </div>
    </div>
    <nav class="wrap navigation" aria-label="Navegación del catálogo">
        <button class="nav-pill active" data-section="todos">Todo el catálogo</button>
        <button data-section="ofertas">Ofertas <span class="nav-sale">%</span></button>
        <button data-section="premium"><svg class="icon small"><use href="#i-star"/></svg> Colección premium</button>
        <button data-section="destacados">Destacados</button>
        <button data-section="recientes">Vistos recientemente</button>
        <span class="nav-end"><svg class="icon small"><use href="#i-chat"/></svg> Pedí por WhatsApp</span>
    </nav>
</header>
<main class="wrap">
    <div id="global-error" class="notice error" hidden role="alert"></div>
    <section class="hero" id="hero" aria-labelledby="hero-title">
        <div class="hero-copy"><span class="eyebrow"><span class="little-line"></span> BIENVENIDOS A MINIFASHION</span>
            <h1 id="hero-title">Pequeños detalles.<br>Grandes <em>estilos.</em></h1>
            <p id="brand-description">Encontrá ese próximo favorito. Descubrí nuestro catálogo, armá tu carrito y conversemos por WhatsApp.</p>
            <button class="primary hero-cta" data-action="explorar">Explorar colección <svg class="icon"><use href="#i-arrow"/></svg></button>
            <div class="hero-note"><span class="tiny-bag">♡</span><span>Elegí con calma. Tu carrito te espera.</span></div>
        </div>
        <div class="hero-art" aria-hidden="true"><span class="hero-ring"></span><span class="art-word">mini<br><em>moments.</em></span><span class="art-spark">✳</span><div class="look-card look-one"><div id="hero-image-one" class="look-image"><svg><use href="#i-shirt"/></svg></div><span>Para todos los días <b>↗</b></span></div><div class="look-card look-two"><div id="hero-image-two" class="look-image"><svg><use href="#i-shirt"/></svg></div><span>Un toque especial <b>✧</b></span></div><span class="hero-sticker">Tu próximo<br><strong>favorito</strong><span>está por acá ♡</span></span></div>
    </section>
    <section class="benefits" aria-label="Cómo comprar"><div><span class="benefit-icon">✧</span><span><strong>Elegí a tu manera</strong><small>Catálogo, ofertas y favoritos</small></span></div><div><svg class="icon"><use href="#i-bag"/></svg><span><strong>Tu carrito se guarda</strong><small>Retomá cuando quieras, en este dispositivo</small></span></div><div><svg class="icon"><use href="#i-chat"/></svg><span><strong>Atención por WhatsApp</strong><small>Coordinamos tu pedido con vos</small></span></div></section>
    <section class="catalogue" id="catalogue" aria-labelledby="catalogue-title">
        <div class="section-heading"><div><span class="eyebrow" id="catalogue-kicker">ENCONTRÁ TU PRÓXIMO FAVORITO</span><h2 id="catalogue-title">Descubrí la colección</h2></div><label class="sort-label">Ordenar por <select id="sort"><option value="nuevos">Más recientes</option><option value="precio_asc">Menor precio</option><option value="precio_desc">Mayor precio</option></select></label></div>
        <div class="category-bar" id="categories" aria-label="Filtrar por categoría"></div>
        <div class="results-line"><span id="result-count" aria-live="polite">Cargando catálogo…</span><span class="subtle">Precios en guaraníes</span></div>
        <div id="products" class="product-grid" aria-live="polite" aria-busy="true"></div>
        <div class="pagination" id="pagination" aria-label="Páginas del catálogo"></div>
    </section>
    <section class="premium-banner"><div><span class="eyebrow">UN POQUITO MÁS ESPECIAL</span><h2>Descubrí nuestra<br>colección <em>premium.</em></h2><p>Una selección para quienes buscan ese detalle diferente.</p><button class="light-button" data-section="premium">Conocer la colección <svg class="icon"><use href="#i-arrow"/></svg></button></div><div class="premium-decoration" aria-hidden="true">✧<span>THE<br>PREMIUM<br>EDIT</span>✧</div></section>
    <section class="last-note"><svg class="icon"><use href="#i-chat"/></svg><h2>Elegís online. Lo coordinamos con vos.</h2><p>Agregá tus productos al carrito y enviá tu pedido por WhatsApp.<br>Confirmamos disponibilidad, entrega y los últimos detalles juntos.</p><button class="text-button" data-action="carrito">Ver mi carrito <span>↗</span></button></section>
</main>
<footer><div class="wrap footer-content"><a class="brand" href="{{ route('tienda.publica') }}"><span class="brand-flower">✳</span><span id="footer-brand">MiniFashion</span><span class="brand-dot">.</span></a><p>Grandes estilos. Pequeños momentos.</p><span>Catálogo conectado a ControlVenta</span><button class="text-button" data-action="privacidad">Sobre tus datos</button></div></footer>
<dialog id="panel" class="panel"><div class="panel-top"><h2 id="panel-title">Mi carrito</h2><button class="icon-button" data-action="cerrar" aria-label="Cerrar"><svg class="icon"><use href="#i-close"/></svg></button></div><div id="panel-content"></div></dialog>
<div id="toast" class="toast" role="status" hidden></div>
<noscript><p class="wrap notice">Activá JavaScript para consultar el catálogo y armar tu pedido.</p></noscript>
</body>
</html>
