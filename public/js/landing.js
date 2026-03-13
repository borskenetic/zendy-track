/* =========================================
   GLOBAL STATE
========================================= */

let currentUser = {
    name: window.AUTH_USER_NAME || ''
};

let selectedBook = null;
let selectedStudent = null;

const track = document.getElementById('carouselTrack');
let scrollAmount = 0;

/* =========================================
   BOOK MODAL
========================================= */

function showBookDetails(
    img, title, author, generalNote, callNumber,
    bookId, availability, copies = 1, pubYear = '',
    contentType = '', fixedData = '', libraryName = ''
) {
    document.getElementById('modalImg').src = img;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalAuthor').textContent = author;
    document.getElementById('modalCallNo').textContent = callNumber;
    document.getElementById('modalContentType').textContent = contentType;
    document.getElementById('modalFixed').textContent = fixedData;
    document.getElementById('modalLibrary').textContent = libraryName;

    document.getElementById('modalAbstract').textContent =
        generalNote && generalNote.trim() !== ""
            ? generalNote
            : "No abstract available.";

    const checkoutBtn = document.getElementById('checkoutBtn');
    const addToCartBtn = document.getElementById('addToCartBtn');

    if (availability === 'Available') {
        checkoutBtn.style.display = 'block';
        addToCartBtn.style.display = 'block';
    } else {
        checkoutBtn.style.display = 'none';
        addToCartBtn.style.display = 'none';
    }

    document.getElementById('bookModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('bookModal').style.display = 'none';
}

function openBookCard(card) {
    selectedBook = {
        id: card.dataset.id,
        title: card.dataset.title,
        author: card.dataset.author,
        availability: card.dataset.availability
    };

    showBookDetails(
        card.dataset.img,
        card.dataset.title,
        card.dataset.author,
        card.dataset.note ?? "",
        card.dataset.call,
        card.dataset.id,
        card.dataset.availability
    );
}

/* =========================================
   ADD TO CART (Updated)
========================================= */

function addToCart() {
    if (!selectedBook) return;

    if (selectedBook.availability !== 'Available') {
        showToast("Book is not available.", "error");
        return;
    }

    addToCartUniversal(
        selectedBook.id,
        selectedBook.title,
        selectedBook.author
    );

    closeModal();
}

/* =========================================
   CAROUSEL
========================================= */

function slide(direction) {
    if (!track) return;

    const bookWidth = 130;
    scrollAmount += direction * bookWidth * 2;

    if (scrollAmount < 0) scrollAmount = 0;
    const maxScroll = track.scrollWidth - track.clientWidth;
    if (scrollAmount > maxScroll) scrollAmount = maxScroll;

    track.style.transform = `translateX(-${scrollAmount}px)`;
}

/* =========================================
   TOAST
========================================= */

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast-card toast-${type}`;

    toast.innerHTML = `
        <span>${message}</span>
        <span class="toast-close" onclick="this.parentElement.remove()">×</span>
    `;

    container.appendChild(toast);

    setTimeout(() => toast.remove(), 4000);
}
