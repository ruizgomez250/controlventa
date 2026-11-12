/* This storefront only talks to the public, tenant-scoped /tienda/v1 API. */
(() => {
    'use strict';
    const $ = (s) => document.querySelector(s);
    const apiBase = document.querySelector('meta[name="tienda-api"]').content.replace(/\/$/, '');
    const escape = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[c]));
    const icon = (name) => `<svg class="icon" aria-hidden="true"><use href="#i-${name}"/></svg>`;
    const money = (n) => `Gs. ${new Intl.NumberFormat('es-PY', {maximumFractionDigits: 0}).format(Number(n))}`;
    const date = (d) => new Intl.DateTimeFormat('es-PY', {day:'2-digit', month:'short'}).format(new Date(d));
    let config, storageKey, section = 'todos', category = '', page = 1, totalPages = 1, busy = false, generation = 0, detailGeneration = 0;
    let data = {cart: [], favorites: [], recent: [], orders: []}, productCache = new Map(), pendingAttempt = null;
    let toastTimer, storageWarning = false;

    async function api(path, options = {}) {
        const res = await fetch(`${apiBase}${path}`, {...options, headers: {Accept: 'application/json', ...options.headers}});
        let body;
        try { body = await res.json(); } catch { throw new Error('No se pudo leer la respuesta de la tienda. Intentá nuevamente.'); }
        if (!res.ok) {
            const message = body.errors ? Object.values(body.errors).flat().join(' ') : body.message;
            throw new Error(res.status === 429 ? 'Hiciste varias consultas seguidas. Esperá un minuto y volvé a intentar.' : (message || 'No pudimos completar la consulta.'));
        }
        return body;
    }

    function save() {
        try { localStorage.setItem(storageKey, JSON.stringify(data)); }
        catch { if (!storageWarning) { storageWarning = true; toast('El navegador no permite guardar el carrito. Se conservará mientras mantengas esta página abierta.'); } }
    }
    function loadSaved() {
        try {
            const saved = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (saved && typeof saved === 'object') {
                for (const key of ['cart', 'favorites', 'recent', 'orders']) if (Array.isArray(saved[key])) data[key] = saved[key].slice(0, key === 'cart' ? 50 : 24);
                data.cart = data.cart.filter((i) => Number.isInteger(i.id) && Number.isInteger(i.quantity) && i.quantity > 0 && i.quantity <= 1000);
                data.favorites = data.favorites.filter(Number.isInteger);
                data.recent = data.recent.filter(Number.isInteger);
                data.orders = data.orders.filter((o) => o?.pedido?.uuid && typeof o.token === 'string');
            }
        } catch { /* A damaged or unavailable browser store starts fresh. */ }
    }
    function toast(message) {
        $('#toast').textContent = message; $('#toast').hidden = false;
        clearTimeout(toastTimer); toastTimer = setTimeout(() => { $('#toast').hidden = true; }, 4200);
    }
    function updateCount() { $('#cart-count').textContent = data.cart.reduce((n, i) => n + i.quantity, 0); }
    function image(p, extra = '') {
        return p.imagen ? `<img class="product-image ${extra}" src="${escape(p.imagen)}" alt="${escape(p.nombre)}" loading="lazy">`
            : `<div class="product-image ${extra}" role="img" aria-label="${escape(p.nombre)}: imagen pendiente"><svg aria-hidden="true"><use href="#i-shirt"/></svg></div>`;
    }
    function card(p) {
        productCache.set(p.id, p);
        const saved = data.favorites.includes(p.id);
        return `<article class="product-card">
            <button class="favorite-button ${saved ? 'saved' : ''}" data-favorite="${p.id}" aria-pressed="${saved}" aria-label="${saved ? 'Quitar de' : 'Agregar a'} favoritos: ${escape(p.nombre)}">${icon('heart')}</button>
            <button class="product-image-button" data-product="${p.id}" aria-label="Ver ${escape(p.nombre)}">${image(p)}<span class="badges">${p.descuento ? `<span class="badge sale">−${p.descuento}%</span>` : ''}${p.premium ? '<span class="badge premium">✧ PREMIUM</span>' : ''}${p.stock < 1 ? '<span class="badge sold">AGOTADO</span>' : ''}</span></button>
            <div class="product-copy"><div class="product-meta">${escape(p.categoria || p.marca || 'La colección')}</div>
            <button class="product-name" data-product="${p.id}">${escape(p.nombre)}</button>
            <div class="product-options">${escape([p.talle && `Talle ${p.talle}`, p.color].filter(Boolean).join(' · ') || p.marca || 'Elegí tu próximo favorito')}</div>
            <div class="price-row"><span class="price">${money(p.precio)}${p.precio_anterior ? `<span class="old-price">${money(p.precio_anterior)}</span>` : ''}</span><button class="add-button" data-add="${p.id}" ${p.stock < 1 ? 'disabled' : ''} aria-label="Agregar ${escape(p.nombre)} al carrito">+ Agregar</button></div>
            <span class="availability">${p.stock >= 1 ? '● Disponible para pedir' : 'Sin disponibilidad por el momento'}</span>
            ${p.oferta_hasta ? `<div class="offer-end">Oferta hasta el ${date(p.oferta_hasta)}</div>` : ''}</div></article>`;
    }

    function empty(title, text) { return `<div class="empty-state">${icon('bag')}<h3>${escape(title)}</h3><p>${escape(text)}</p><button class="primary" data-section="todos">Explorar catálogo ${icon('arrow')}</button></div>`; }
    function title() {
        const names = {todos:'Descubrí la colección', ofertas:'Favoritos a precios especiales', premium:'La colección premium', destacados:'Nuestra selección', favoritos:'Tus favoritos', recientes:'Vistos recientemente'};
        $('#catalogue-title').textContent = $('#search').value.trim() ? `Resultados para “${$('#search').value.trim()}”` : names[section];
        $('#catalogue-kicker').textContent = section === 'premium' ? 'UN POQUITO MÁS ESPECIAL' : 'ENCONTRÁ TU PRÓXIMO FAVORITO';
        $('#hero').hidden = section !== 'todos' || category !== '' || $('#search').value.trim() !== '';
        document.querySelectorAll('.navigation [data-section]').forEach((b) => b.classList.toggle('active', b.dataset.section === section));
        document.querySelectorAll('[data-category]').forEach((b) => { b.classList.toggle('active', b.dataset.category === category); b.setAttribute('aria-pressed', b.dataset.category === category); });
    }
    async function products(scroll = false) {
        const myGeneration = ++generation;
        title();
        if (scroll) $('#catalogue').scrollIntoView({behavior:'smooth', block:'start'});
        $('#products').setAttribute('aria-busy', 'true');
        $('#products').innerHTML = Array(4).fill('<div class="skeleton" aria-hidden="true"></div>').join('');
        $('#pagination').innerHTML = ''; $('#result-count').textContent = 'Buscando tus próximos favoritos…';
        const params = new URLSearchParams({page: String(page), orden: $('#sort').value});
        if ($('#search').value.trim()) params.set('buscar', $('#search').value.trim());
        if (category) params.set('categoria', category);
        if (['favoritos', 'recientes'].includes(section)) {
            const ids = section === 'favoritos' ? data.favorites : data.recent;
            if (!ids.length) {
                $('#products').innerHTML = empty(section === 'favoritos' ? 'Acá van tus favoritos' : 'Tu inspiración empieza acá', section === 'favoritos' ? 'Tocá el corazón de un producto para guardarlo y volver después.' : 'Los productos que visites aparecerán en esta sección.');
                $('#result-count').textContent = '0 productos'; $('#products').setAttribute('aria-busy', 'false'); return;
            }
            ids.forEach((id) => params.append('ids[]', id));
        } else params.set('seccion', section);
        try {
            const result = await api(`/productos?${params}`);
            if (myGeneration !== generation) return;
            totalPages = result.meta.paginas;
            $('#products').innerHTML = result.data.length ? result.data.map(card).join('') : empty('No encontramos productos', 'Probá con otra búsqueda o categoría. Las novedades aparecen aquí cuando se publican.');
            $('#result-count').textContent = `${result.meta.total} ${result.meta.total === 1 ? 'producto' : 'productos'} · Página ${result.meta.pagina} de ${result.meta.paginas}`;
            if (totalPages > 1) $('#pagination').innerHTML = `<button data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>← Anterior</button><span>${page} / ${totalPages}</span><button data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''}>Siguiente →</button>`;
            if (section === 'todos' && !category && !$('#search').value.trim()) {
                result.data.filter((p) => p.imagen && p.destacado).slice(0, 2).forEach((p, index) => { $(`#hero-image-${index ? 'two' : 'one'}`).innerHTML = `<img src="${escape(p.imagen)}" alt="">`; });
            }
        } catch (e) {
            if (myGeneration !== generation) return;
            $('#products').innerHTML = `<div class="empty-state"><h3>No pudimos cargar el catálogo</h3><p>${escape(e.message)}</p><button class="primary" data-action="reintentar">Volver a intentar</button></div>`;
            $('#result-count').textContent = 'Catálogo no disponible';
        } finally { if (myGeneration === generation) $('#products').setAttribute('aria-busy', 'false'); }
    }
    function selectSection(next) {
        if ($('#panel').open) $('#panel').close();
        section = next; page = 1; category = ''; $('#search').value = ''; products(true);
    }
    function add(id) {
        const p = productCache.get(id);
        if (!p || p.stock < 1) return toast('Este producto no está disponible.');
        let item = data.cart.find((i) => i.id === id);
        if (item && item.quantity >= Math.min(1000, Math.floor(p.stock))) return toast('Alcanzaste la disponibilidad actual de este producto.');
        if (!item && data.cart.length >= 50) return toast('Podés incluir hasta 50 productos diferentes por pedido.');
        if (item) { item.quantity++; item.product = p; } else data.cart.push({id, quantity:1, product:p});
        save(); updateCount(); toast('Agregado a tu carrito ♡');
    }
    function favorite(id) {
        const index = data.favorites.indexOf(id);
        if (index >= 0) data.favorites.splice(index, 1);
        else { data.favorites.unshift(id); data.favorites = data.favorites.slice(0, 24); }
        save();
        document.querySelectorAll(`[data-favorite="${id}"]`).forEach((b) => { b.classList.toggle('saved', index < 0); b.setAttribute('aria-pressed', index < 0); });
        toast(index < 0 ? 'Guardado en tus favoritos' : 'Quitado de tus favoritos');
        if (section === 'favoritos') products();
    }
    function openPanel(name, html) { $('#panel-title').textContent = name; $('#panel-content').innerHTML = html; if (!$('#panel').open) $('#panel').showModal(); $('#panel-content').scrollTop = 0; }
    async function detail(id) {
        const revision = ++detailGeneration;
        openPanel('Un nuevo favorito', '<div class="skeleton" aria-label="Cargando producto"></div>');
        try {
            const {data:p} = await api(`/productos/${id}`);
            if (!$('#panel').open || revision !== detailGeneration) return;
            productCache.set(p.id, p);
            data.recent = [p.id, ...data.recent.filter((n) => n !== p.id)].slice(0, 24); save();
            const html = `<article class="product-detail">${image(p)}<div class="detail-tags">${p.premium ? '<span>✧ Premium</span>' : ''}${p.descuento ? `<span>−${p.descuento}% de descuento</span>` : ''}<span>${escape(p.categoria || 'Colección')}</span></div><h3>${escape(p.nombre)}</h3><div class="price">${money(p.precio)}${p.precio_anterior ? `<span class="old-price">${money(p.precio_anterior)}</span>` : ''}</div><p>${escape(p.descripcion || 'Consultanos por WhatsApp para conocer todos los detalles de este producto.')}</p><div class="detail-tags">${[p.talle && `Talle ${p.talle}`,p.color,p.marca,p.codigo && `Código ${p.codigo}`].filter(Boolean).map((v) => `<span>${escape(v)}</span>`).join('')}</div><span class="muted-note">${p.stock >= 1 ? 'Disponible. Confirmamos la entrega al recibir tu pedido.' : 'Por el momento no hay disponibilidad.'}</span><button class="primary full-width" data-add="${p.id}" ${p.stock < 1 ? 'disabled' : ''}>${icon('bag')} Agregar al carrito</button><button class="text-button full-width" data-favorite="${p.id}">♡ ${data.favorites.includes(p.id) ? 'Quitar de favoritos' : 'Guardar para después'}</button></article><h3 style="margin-top:32px">También te puede gustar</h3><div id="related" class="related-list"></div>`;
            openPanel('Detalle del producto', html);
            const result = await api(`/productos?${new URLSearchParams(p.categoria_id ? {categoria:p.categoria_id} : {seccion:'todos'})}`);
            if ($('#related') && revision === detailGeneration) $('#related').innerHTML = result.data.filter((other) => other.id !== p.id).slice(0, 2).map(card).join('');
        } catch (e) { if (revision === detailGeneration && $('#panel').open) openPanel('Producto', `<p class="notice error">${escape(e.message)}</p>`); }
    }
    async function refreshCart() {
        const current = data.cart.map((i) => i.id);
        const results = [];
        for (let start = 0; start < current.length; start += 24) {
            const params = new URLSearchParams(); current.slice(start, start + 24).forEach((id) => params.append('ids[]', id));
            // Catalog pages contain 12 entries. Fetch both pages of each batch.
            const first = await api(`/productos?${params}`); results.push(...first.data);
            if (first.meta.paginas > 1) results.push(...(await api(`/productos?${params}&page=2`)).data);
        }
        const byId = new Map(results.map((p) => [p.id, p]));
        data.cart.forEach((i) => { const p = byId.get(i.id); i.available = !!p; if (p) { i.product = p; productCache.set(p.id, p); } });
        save();
    }
    async function cart() {
        const revision = ++detailGeneration;
        if (!data.cart.length) { openPanel('Mi carrito', empty('Tu carrito está esperando', 'Explorá la colección y agregá lo que más te guste.')); return; }
        openPanel('Mi carrito', '<p class="notice">Actualizando precios y disponibilidad…</p>');
        try { await refreshCart(); if ($('#panel').open && revision === detailGeneration) renderCart(); }
        catch (e) { if ($('#panel').open && revision === detailGeneration) openPanel('Mi carrito', `<p class="notice error">${escape(e.message)}</p><button class="primary" data-action="carrito">Reintentar</button>`); }
    }
    function renderCart() {
        if (!data.cart.length) { openPanel('Mi carrito', empty('Tu carrito está vacío', 'Tu próximo favorito te espera en el catálogo.')); return; }
        const invalid = data.cart.some((i) => !i.available || i.quantity > i.product.stock);
        let html = data.cart.map((i) => `<div class="cart-item">${image(i.product)}<div><strong>${escape(i.product.nombre)}</strong><span class="muted-note">${money(i.product.precio)} c/u</span><div class="quantity"><button data-quantity="${i.id}" data-delta="-1" aria-label="Disminuir cantidad">−</button><span>${i.quantity}</span><button data-quantity="${i.id}" data-delta="1" aria-label="Aumentar cantidad" ${!i.available || i.quantity >= Math.min(1000, Math.floor(i.product.stock)) ? 'disabled' : ''}>+</button><button class="text-button" data-remove="${i.id}" aria-label="Quitar ${escape(i.product.nombre)}">Quitar</button></div>${!i.available || i.quantity > i.product.stock ? '<span class="notice error">Revisá la cantidad: no hay disponibilidad suficiente.</span>' : ''}</div><span class="price">${money(i.quantity * i.product.precio)}</span></div>`).join('');
        html += `<div class="cart-total"><span>Total de productos</span><span>${money(data.cart.reduce((n, i) => n + i.quantity * i.product.precio, 0))}</span></div><p class="muted-note">El costo de envío se coordina por WhatsApp. El pedido está sujeto a confirmación de disponibilidad.</p>`;
        if (!config.pedidos_habilitados) html += '<p class="notice">Estamos preparando la atención por WhatsApp. Podés guardar tus favoritos y volver a visitarnos.</p>';
        else html += `<form id="checkout" class="checkout-form"><h3>¿A nombre de quién preparamos el pedido?</h3><label>Tu nombre<input name="nombre" required maxlength="100" autocomplete="name"></label><label>Tu teléfono<input name="telefono" required type="tel" pattern="[+0-9 ()-]{6,25}" maxlength="25" autocomplete="tel" placeholder="0981 123 456"></label><label>¿Cómo lo recibís?<select name="entrega" id="delivery"><option value="retiro">Retiro en el local</option><option value="envio">Coordinar envío</option></select></label><label id="address-label" hidden>Dirección de entrega<input name="direccion" maxlength="250" autocomplete="street-address" disabled></label><label>¿Algo que debamos saber? <span class="subtle">(opcional)</span><textarea name="observaciones" maxlength="500" rows="2" placeholder="Indicaciones para tu pedido"></textarea></label><p class="muted-note">Guardaremos estos datos para atender tu pedido. Al continuar se abrirá WhatsApp con el mensaje preparado; vos elegís enviarlo.</p><div id="checkout-error" class="notice error" role="alert" hidden></div><button type="submit" class="primary full-width" ${invalid ? 'disabled' : ''}>${icon('chat')} Preparar pedido por WhatsApp</button></form>`;
        openPanel('Mi carrito', html);
    }
    function uuid() {
        if (crypto.randomUUID) return crypto.randomUUID();
        const bytes = new Uint8Array(16); crypto.getRandomValues(bytes); bytes[6] = (bytes[6] & 15) | 64; bytes[8] = (bytes[8] & 63) | 128;
        return Array.from(bytes, (b, i) => ([4,6,8,10].includes(i) ? '-' : '') + b.toString(16).padStart(2,'0')).join('');
    }
    async function checkout(form) {
        if (busy) return;
        const v = Object.fromEntries(new FormData(form));
        v.items = data.cart.map((i) => ({id:i.id, cantidad:i.quantity, precio:i.product.precio}));
        const signature = JSON.stringify(v);
        if (!pendingAttempt || pendingAttempt.signature !== signature) pendingAttempt = {signature, referencia:uuid(), token:uuid() + uuid()};
        v.referencia = pendingAttempt.referencia; v.token = pendingAttempt.token;
        busy = true; const button = form.querySelector('[type="submit"]'); button.disabled = true; button.textContent = 'Guardando tu pedido…';
        $('#checkout-error').hidden = true;
        try {
            const result = await api('/pedidos', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(v)});
            const stored = {pedido:result.pedido, token:v.token};
            data.orders = [stored, ...data.orders.filter((o) => o.pedido.uuid !== result.pedido.uuid)].slice(0, 24);
            data.cart = []; save(); updateCount(); pendingAttempt = null;
            const safeLink = /^https:\/\/wa\.me\/[0-9]+\?text=/.test(result.whatsapp_url) ? result.whatsapp_url : '';
            openPanel('Tu pedido está listo', `<div class="empty-state">${icon('chat')}<h3>¡Gracias por elegirnos!</h3><p>Guardamos tu pedido <strong>#${result.pedido.id}</strong> por ${money(result.pedido.total)}. Abrí WhatsApp y enviá el mensaje para coordinarlo con nosotros.</p>${safeLink ? `<a class="primary full-width" href="${escape(safeLink)}" target="_blank" rel="noopener noreferrer">${icon('chat')} Enviar pedido por WhatsApp</a>` : ''}<p class="muted-note">El pedido sigue pendiente hasta que lo confirmemos. Podés consultarlo desde «Mis pedidos» en este dispositivo.</p><button class="text-button" data-section="todos">Seguir explorando</button></div>`);
        } catch (e) { const error = $('#checkout-error'); if (error) { error.textContent = e.message; error.hidden = false; } button.disabled = false; button.innerHTML = `${icon('chat')} Preparar pedido por WhatsApp`; }
        finally { busy = false; }
    }
    function orders() {
        ++detailGeneration;
        openPanel('Mis pedidos', data.orders.length ? `<p class="muted-note">Pedidos de este navegador. «Pendiente» significa que todavía debemos confirmar tu solicitud.</p>${data.orders.map((o, index) => `<article class="order-card" id="order-${index}"><h3>Pedido #${o.pedido.id} · ${date(o.pedido.created_at)}</h3><span class="status-badge">${escape(o.pedido.estado)}</span><p>${o.pedido.items.map((i) => `${i.cantidad} × ${escape(i.nombre)}`).join('<br>')}</p><strong>${money(o.pedido.total)}</strong><div class="order-actions"><button class="text-button" data-status="${index}">Consultar estado</button><button class="text-button" data-repeat="${index}">Volver a pedir</button></div></article>`).join('')}` : empty('Tu primera historia empieza acá', 'Cuando prepares un pedido, aparecerá aquí para consultar su estado o volver a pedirlo.'));
    }
    async function status(index) {
        const order = data.orders[index]; if (!order) return;
        try { const result = await api(`/pedidos/${encodeURIComponent(order.pedido.uuid)}`, {headers:{'X-Pedido-Token':order.token}}); order.pedido = result.pedido; save(); const badge = $(`#order-${index} .status-badge`); if (badge) badge.textContent = result.pedido.estado; toast('Estado actualizado'); }
        catch (e) { toast(e.message); }
    }
    async function repeat(index) {
        const order = data.orders[index]; if (!order) return;
        try {
            let current = [];
            for (let start = 0; start < order.pedido.items.length; start += 24) {
                const params = new URLSearchParams(); order.pedido.items.slice(start, start + 24).forEach((i) => params.append('ids[]', i.id));
                const first = await api(`/productos?${params}`); current = current.concat(first.data);
                if (first.meta.paginas > 1) current = current.concat((await api(`/productos?${params}&page=2`)).data);
            }
            current.forEach((p) => { productCache.set(p.id, p); if (p.stock >= 1 && !data.cart.some((i) => i.id === p.id) && data.cart.length < 50) data.cart.push({id:p.id, quantity:Math.min(1000, Math.floor(p.stock), order.pedido.items.find((i) => i.id === p.id).cantidad), product:p}); });
            save(); updateCount(); await cart(); toast('Agregamos los productos disponibles con sus precios actuales. Revisá tu carrito.');
        } catch (e) { toast(e.message); }
    }
    document.addEventListener('click', (event) => {
        const button = event.target.closest('button,a'); if (!button || button.disabled) return;
        if (button.dataset.section) return selectSection(button.dataset.section);
        if (button.dataset.category !== undefined) { category = button.dataset.category; page = 1; return products(); }
        if (button.dataset.page) { page = Number(button.dataset.page); return products(true); }
        if (button.dataset.favorite) return favorite(Number(button.dataset.favorite));
        if (button.dataset.product) return detail(Number(button.dataset.product));
        if (button.dataset.add) return add(Number(button.dataset.add));
        if (button.dataset.remove) { data.cart = data.cart.filter((i) => i.id !== Number(button.dataset.remove)); save(); updateCount(); return renderCart(); }
        if (button.dataset.quantity) { const item = data.cart.find((i) => i.id === Number(button.dataset.quantity)); if (!item) return; item.quantity += Number(button.dataset.delta); if (item.quantity <= 0) data.cart = data.cart.filter((i) => i !== item); save(); updateCount(); return renderCart(); }
        if (button.dataset.status !== undefined) return status(Number(button.dataset.status));
        if (button.dataset.repeat !== undefined) return repeat(Number(button.dataset.repeat));
        switch (button.dataset.action) {
            case 'cerrar': ++detailGeneration; $('#panel').close(); break;
            case 'carrito': cart(); break;
            case 'pedidos': orders(); break;
            case 'explorar': $('#catalogue').scrollIntoView({behavior:'smooth'}); break;
            case 'reintentar': products(); break;
            case 'privacidad': openPanel('Sobre tus datos', '<p class="muted-note">El carrito, favoritos, vistos recientemente y accesos a tus pedidos se guardan en este navegador y se separan por tienda. No se sincronizan con otros dispositivos.</p><p class="muted-note">Al preparar un pedido, guardamos tu nombre, teléfono, dirección si corresponde y productos en el sistema de esta tienda para atenderlo. Si abrís WhatsApp, esos datos se incluyen en el mensaje preparado. Solo se envía el mensaje cuando lo confirmás en WhatsApp.</p><button class="text-button" data-action="borrar-local">Borrar mis datos de este navegador</button>'); break;
            case 'borrar-local': data = {cart:[],favorites:[],recent:[],orders:[]}; pendingAttempt = null; save(); updateCount(); $('#panel').close(); toast('Datos de este navegador borrados. Los pedidos recibidos por la tienda se conservan.'); if (['favoritos','recientes'].includes(section)) products(); break;
        }
    });
    document.addEventListener('submit', (e) => {
        if (e.target.id === 'search-form') { e.preventDefault(); section = 'todos'; category = ''; page = 1; products(true); }
        if (e.target.id === 'checkout') { e.preventDefault(); checkout(e.target); }
    });
    $('#sort').addEventListener('change', () => { page = 1; products(); });
    document.addEventListener('change', (e) => { if (e.target.id === 'delivery') { const address = $('#address-label'); const enabled = e.target.value === 'envio'; address.hidden = !enabled; address.querySelector('input').required = enabled; address.querySelector('input').disabled = !enabled; } });
    $('#panel').addEventListener('click', (e) => { if (e.target === $('#panel') && e.clientX < $('#panel').getBoundingClientRect().left) $('#panel').close(); });
    document.addEventListener('error', (e) => { if (e.target instanceof HTMLImageElement && e.target.classList.contains('product-image')) { const box = document.createElement('div'); box.className = 'product-image'; box.innerHTML = '<svg aria-hidden="true"><use href="#i-shirt"/></svg>'; box.setAttribute('role','img'); box.setAttribute('aria-label', 'Imagen no disponible'); e.target.replaceWith(box); } }, true);
    window.addEventListener('storage', (e) => { if (e.key === storageKey) { loadSaved(); updateCount(); } });

    async function init() {
        try {
            config = await api('/inicio'); const shop = config.tienda;
            storageKey = `controlventa-tienda:${new URL(apiBase, location.href).href}:${shop.identificador}`;
            loadSaved(); updateCount();
            document.title = `${shop.nombre} · Tu próximo favorito`;
            $('#brand-name').textContent = shop.nombre; $('#footer-brand').textContent = shop.nombre;
            $('#announcement').textContent = shop.aviso; $('#brand-description').textContent = shop.descripcion;
            $('.hero .eyebrow').innerHTML = `<span class="little-line"></span> BIENVENIDOS A ${escape(shop.nombre.toUpperCase())}`;
            if (/^#[0-9a-f]{6}$/i.test(shop.color)) document.documentElement.style.setProperty('--green', shop.color);
            $('#categories').innerHTML = `<button class="category-chip active" data-category="" aria-pressed="true">Todo</button>${config.categorias.map((c) => `<button class="category-chip" data-category="${c.id}" aria-pressed="false">${escape(c.nombre)}</button>`).join('')}`;
            await products();
        } catch (e) { $('#global-error').hidden = false; $('#global-error').textContent = e.message; $('#result-count').textContent = 'Tienda no disponible'; $('#products').setAttribute('aria-busy','false'); $('#hero').hidden = true; }
    }
    init();
})();
