/**
 * JavaScript para Carrito de Compras
 * SPA Erika Meza
 */

document.addEventListener('DOMContentLoaded', function() {
    initCart();
});

function initCart() {
    updateCartTotal();
    setupCartEventListeners();
}

function setupCartEventListeners() {
    // Botones de remover servicio
    const removeButtons = document.querySelectorAll('.remove-from-cart');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const serviceId = this.dataset.serviceId;
            removeFromCart(serviceId);
        });
    });
    
    // Botón de limpiar carrito
    const clearButton = document.getElementById('clearCartBtn');
    if (clearButton) {
        clearButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('¿Estás seguro de vaciar el carrito?')) {
                clearCart();
            }
        });
    }
}

function addToCart(serviceId, serviceName, servicePrice, serviceDuration) {
    // Crear formulario y enviar
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?action=add-to-cart';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'service_id';
    input.value = serviceId;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function removeFromCart(serviceId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?action=remove-from-cart';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'service_id';
    input.value = serviceId;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function clearCart() {
    window.location.href = 'index.php?action=clear-cart';
}

function updateCartTotal() {
    const cartItems = document.querySelectorAll('.cart-item');
    let total = 0;
    let totalDuration = 0;
    
    cartItems.forEach(item => {
        const price = parseFloat(item.dataset.price || 0);
        const duration = parseInt(item.dataset.duration || 0);
        total += price;
        totalDuration += duration;
    });
    
    // Actualizar total en UI
    const totalElement = document.getElementById('cartTotal');
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
    }
    
    const durationElement = document.getElementById('totalDuration');
    if (durationElement) {
        durationElement.textContent = `${totalDuration} minutos`;
    }
    
    // Actualizar contador de items en navbar
    updateCartBadge(cartItems.length);
}

function updateCartBadge(count) {
    let badge = document.querySelector('.cart-badge');
    
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge bg-danger cart-badge position-absolute top-0 start-100 translate-middle';
            const cartLink = document.querySelector('a[href*="cart"]');
            if (cartLink) {
                cartLink.style.position = 'relative';
                cartLink.appendChild(badge);
            }
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(price);
}

function proceedToCheckout() {
    const cartItems = document.querySelectorAll('.cart-item');
    
    if (cartItems.length === 0) {
        alert('El carrito está vacío');
        return;
    }
    
    // Redirigir a página de reserva
    window.location.href = 'index.php?page=book-appointment';
}

// Animación al agregar al carrito
function animateAddToCart(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i> Agregado';
    button.classList.add('btn-success');
    button.classList.remove('btn-primary');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-primary');
    }, 2000);
}

// Guardar carrito en localStorage (backup)
function saveCartToLocalStorage() {
    const cartItems = [];
    document.querySelectorAll('.cart-item').forEach(item => {
        cartItems.push({
            id: item.dataset.serviceId,
            nombre: item.dataset.serviceName,
            precio: item.dataset.price,
            duracion: item.dataset.duration
        });
    });
    localStorage.setItem('spa_cart', JSON.stringify(cartItems));
}

// Cargar carrito desde localStorage (backup)
function loadCartFromLocalStorage() {
    const savedCart = localStorage.getItem('spa_cart');
    if (savedCart) {
        return JSON.parse(savedCart);
    }
    return [];
}

// Exportar funciones para uso global
window.CartUtils = {
    addToCart,
    removeFromCart,
    clearCart,
    updateCartTotal,
    proceedToCheckout,
    animateAddToCart
};