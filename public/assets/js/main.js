/* AgroLink™ — interacciones globales */
document.addEventListener('DOMContentLoaded', () => {

    /* ---------- Menú móvil ---------- */
    const navbar = document.querySelector('.navbar');
    const nav = document.querySelector('.navbar nav');
    if (navbar && nav) {
        const toggle = document.createElement('button');
        toggle.className = 'nav-toggle';
        toggle.setAttribute('aria-label', 'Abrir menú');
        toggle.innerHTML = '☰';
        navbar.insertBefore(toggle, nav);
        toggle.addEventListener('click', () => {
            nav.classList.toggle('nav-abierto');
            toggle.innerHTML = nav.classList.contains('nav-abierto') ? '✕' : '☰';
        });
    }

    /* ---------- Auto-cierre de mensajes flash ---------- */
    document.querySelectorAll('.flash').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4500);
    });

    /* ---------- Confirmación + prevención de doble envío (un solo handler) ---------- */
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', e => {
            if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
                e.preventDefault();
                return;
            }
            const btn = form.querySelector('button[type="submit"], button:not([type])');
            if (btn && !btn.disabled) {
                btn.dataset.textoOriginal = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span> Procesando...';
                // Salvaguarda: si algo falla y la página no navega, reactivar el botón.
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.textoOriginal;
                }, 8000);
            }
        });
    });

    /* ---------- Vista previa de imagen antes de subir ---------- */
    const inputImagen = document.querySelector('input[type="file"][name="imagen"]');
    if (inputImagen) {
        const preview = document.createElement('div');
        preview.className = 'preview-imagen';
        inputImagen.parentElement.appendChild(preview);
        inputImagen.addEventListener('change', () => {
            const archivo = inputImagen.files[0];
            preview.innerHTML = '';
            if (!archivo) return;
            if (!['image/jpeg', 'image/png'].includes(archivo.type)) {
                preview.innerHTML = '<span class="meta" style="color:var(--rojo)">Formato no permitido (usa JPG o PNG).</span>';
                inputImagen.value = '';
                return;
            }
            if (archivo.size > 5 * 1024 * 1024) {
                preview.innerHTML = '<span class="meta" style="color:var(--rojo)">La imagen supera 5 MB.</span>';
                inputImagen.value = '';
                return;
            }
            const img = document.createElement('img');
            img.src = URL.createObjectURL(archivo);
            preview.appendChild(img);
        });
    }

    /* ---------- Subtotal en vivo (detalle de producto) ---------- */
    const cantidadInput = document.querySelector('[data-precio-kg]');
    if (cantidadInput) {
        const precioKg = parseFloat(cantidadInput.dataset.precioKg);
        const salida = document.getElementById('subtotal-vivo');
        const actualizar = () => {
            const cantidad = parseFloat(cantidadInput.value) || 0;
            const subtotal = cantidad * precioKg;
            if (salida) salida.textContent = formatoColones(subtotal);
        };
        cantidadInput.addEventListener('input', actualizar);
        actualizar();
    }

    /* ---------- Selector de estrellas interactivo ---------- */
    document.querySelectorAll('.star-rating').forEach(widget => {
        const input = widget.querySelector('input[type="hidden"]');
        const estrellas = Array.from(widget.querySelectorAll('.estrella'));
        const pintar = (valor) => {
            estrellas.forEach(e => {
                e.classList.toggle('activa', Number(e.dataset.valor) <= valor);
            });
        };
        estrellas.forEach(e => {
            e.addEventListener('click', () => {
                input.value = e.dataset.valor;
                pintar(Number(e.dataset.valor));
            });
            e.addEventListener('mouseenter', () => pintar(Number(e.dataset.valor)));
        });
        widget.addEventListener('mouseleave', () => pintar(Number(input.value)));
        pintar(Number(input.value || 5));
    });

    /* ---------- Validación en vivo: precio/cantidad > 0 ---------- */
    document.querySelectorAll('input[data-min-mayor-cero]').forEach(input => {
        input.addEventListener('input', () => {
            const valido = parseFloat(input.value) > 0;
            input.classList.toggle('input-invalido', input.value !== '' && !valido);
        });
    });
});

function formatoColones(monto) {
    return '₡' + Math.round(monto).toLocaleString('es-CR');
}
