/* =========================================
   GLOBAL CART SYSTEM (SHARED)
========================================= */

// Use window.cart globally so all pages share the same cart
window.cart = window.cart || JSON.parse(localStorage.getItem('borrowCart')) || [];

/* =========================================
   TOAST (ALWAYS EXISTS)
========================================= */

function showToast(message, type = 'info') {
    let container = document.getElementById('toastContainer');

    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-card toast-${type}`;
    toast.style.background = '#222';
    toast.style.color = '#fff';
    toast.style.padding = '12px 16px';
    toast.style.marginBottom = '10px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';
    toast.style.minWidth = '200px';

    toast.innerHTML = `
        <span>${message}</span>
        <span style="float:right; cursor:pointer; margin-left:10px;"
              onclick="this.parentElement.remove()">×</span>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

/* =========================================
   UTILITIES
========================================= */

function saveCart() {
    localStorage.setItem('borrowCart', JSON.stringify(window.cart));
}

function updateCartUI() {
    const countEls = document.querySelectorAll('#cartCount');
    countEls.forEach(el => el.textContent = window.cart.length);
    saveCart();
}

/* =========================================
   ADD TO CART (Unified)
========================================= */

function addToCart() {
    if (!window.selectedBook || !window.selectedBook.id) {
        showToast("No book selected.", "error");
        return;
    }

    if (window.cart.length >= 5) {
        showToast("Maximum of 5 books allowed.", "error");
        return;
    }

    if (window.cart.some(item => item.id == window.selectedBook.id)) {
        showToast("Book already in cart.", "info");
        return;
    }

    window.cart.push({
        id: window.selectedBook.id,
        title: window.selectedBook.title,
        author: window.selectedBook.author || ''
    });

    updateCartUI();
    showToast("Added to cart.", "success");
}

/* =========================================
   UNIVERSAL HELPER FOR COPIES.BLADE
========================================= */

function selectBookAndAddToCart(id, title, author = '') {
    window.selectedBook = { id, title, author };
    addToCart();
}

/* =========================================
   REMOVE
========================================= */

function removeFromCart(index) {
    window.cart.splice(index, 1);
    updateCartUI();
    openCartModal();
}

/* =========================================
   CART MODAL
========================================= */

function openCartModal() {
    const list = document.getElementById('cartList');
    const emptyState = document.getElementById('emptyCart');
    const totalEl = document.getElementById('cartTotal');
    const modal = document.getElementById('cartModal');

    if (!list || !modal) return;

    list.innerHTML = '';

    if (window.cart.length === 0) {
        if (emptyState) emptyState.style.display = 'block';
    } else {
        if (emptyState) emptyState.style.display = 'none';

        window.cart.forEach((book, index) => {
            const li = document.createElement('li');
            li.className = "cart-item";

            li.innerHTML = `
                <div>
                    <strong>${book.title}</strong><br>
                    <small>${book.author ?? ''}</small>
                </div>
                <button class="remove-btn" onclick="removeFromCart(${index})">
                    Remove
                </button>
            `;

            list.appendChild(li);
        });
    }

    if (totalEl) totalEl.textContent = window.cart.length;
    modal.style.display = 'flex';
}

function closeCartModal() {
    const modal = document.getElementById('cartModal');
    if (modal) modal.style.display = 'none';
}

/* =========================================
   INIT
========================================= */

document.addEventListener("DOMContentLoaded", function () {
    updateCartUI();
});