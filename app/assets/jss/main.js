// VulnShop — main.js

// Add to cart feedback
function addToCartFeedback(btn) {
    const original = btn.innerHTML;
    btn.innerHTML = '✓';
    btn.style.background = '#059669';
    setTimeout(() => {
        btn.innerHTML = original;
        btn.style.background = '';
    }, 1200);
    // Update cart count
    const badge = document.querySelector('.cart-count');
    if (badge) {
        badge.textContent = parseInt(badge.textContent || 0) + 1;
    } else {
        const cartBtn = document.querySelector('.cart-btn');
        if (cartBtn) {
            const span = document.createElement('span');
            span.className = 'cart-count';
            span.textContent = '1';
            cartBtn.appendChild(span);
        }
    }
}

// Star rating
function initStarRating() {
    document.querySelectorAll('.star-rating-input').forEach(container => {
        const stars = container.querySelectorAll('.star');
        const input = container.querySelector('input[type=hidden]');
        stars.forEach((star, idx) => {
            star.addEventListener('mouseover', () => {
                stars.forEach((s, i) => s.classList.toggle('filled', i <= idx));
            });
            star.addEventListener('click', () => {
                if (input) input.value = idx + 1;
                stars.forEach((s, i) => s.classList.toggle('filled', i <= idx));
            });
        });
        container.addEventListener('mouseleave', () => {
            const val = input ? parseInt(input.value) : 0;
            stars.forEach((s, i) => s.classList.toggle('filled', i < val));
        });
    });
}

// Image preview for upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById(previewId);
            if (preview) preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Quantity input
function changeQty(btn, delta) {
    const input = btn.closest('.qty-wrap').querySelector('.qty-input');
    const val   = parseInt(input.value) + delta;
    if (val >= 1 && val <= 99) input.value = val;
}

// Confirm delete
function confirmDelete(msg) {
    return confirm(msg || 'Êtes-vous sûr de vouloir supprimer cet élément ?');
}

document.addEventListener('DOMContentLoaded', () => {
    initStarRating();

    // Auto-hide flash after 5s
    const flash = document.querySelector('.flash');
    if (flash) setTimeout(() => flash.remove(), 5000);

    // Smooth scroll to anchor
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
        });
    });
});