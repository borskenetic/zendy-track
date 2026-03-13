/* =========================================
   GLOBAL STATE
========================================= */

// Make these truly global
window.selectedBook = null;
window.selectedStudent = null;

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

    const viewCopiesBtn = document.getElementById('viewCopiesBtn');
    if (copies > 1) {
        viewCopiesBtn.style.display = 'block';
        viewCopiesBtn.onclick = () => {
            window.location.href =
                `/books/copies?title=${encodeURIComponent(title)}&author=${encodeURIComponent(author)}&year=${encodeURIComponent(pubYear)}`;
        };
    } else {
        viewCopiesBtn.style.display = 'none';
    }

    document.getElementById('bookModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('bookModal').style.display = 'none';
}

function openBookCard(card) {
    window.selectedBook = {
        id: card.dataset.id,
        title: card.dataset.title,
        author: card.dataset.author,
        availability: card.dataset.availability,
        copies: parseInt(card.dataset.copies) || 1,
        pubYear: card.dataset.year || ''
    };

    showBookDetails(
        card.dataset.img,
        card.dataset.title,
        card.dataset.author,
        card.dataset.note ?? "",
        card.dataset.call,
        card.dataset.id,
        card.dataset.availability,
        parseInt(card.dataset.copies) || 1,
        card.dataset.year || '',
        card.dataset.content || '',
        card.dataset.fixed || '',
        card.dataset.library || ''
    );
}


/* =========================================
   STUDENT MODAL + CHECKOUT
========================================= */

function openStudentModal() {
    document.getElementById('studentIdInput').value = '';
    document.getElementById('studentError').style.display = 'none';
    document.getElementById('studentModal').style.display = 'flex';
}

function closeStudentModal() {
    document.getElementById('studentModal').style.display = 'none';
}

function confirmCheckout() {
    const studentId = document.getElementById('studentIdInput').value.trim();

    if (!studentId) {
        showToast("Please enter your Student ID.", "error");
        return;
    }

    // 🔥 Decide source: cart or single book
    let booksToCheckout = [];

    if (window.cart && window.cart.length > 0) {
        booksToCheckout = window.cart;
    } else if (window.selectedBook) {
        booksToCheckout = [window.selectedBook];
    } else {
        showToast("No book selected.", "error");
        return;
    }

    fetch(window.CHECKOUT_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN
        },
        body: JSON.stringify({
            student_id: studentId,
            books: booksToCheckout // 🔥 IMPORTANT: send array
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {

            // ✅ Clear cart after checkout
            window.cart = [];
            localStorage.removeItem('borrowCart');
            updateCartUI();

            showToast("Checkout successful!", "success");

        } else {
            showToast("Checkout failed: " + data.message, "error");
        }
    })
    .catch(err => {
        console.error(err);
        showToast("Server error occurred.", "error");
    });
}


/* =========================================
   QZ TRAY PRINTING
========================================= */

if (typeof qz !== "undefined") {
    qz.security.setCertificatePromise(resolve => {
        resolve("-----BEGIN CERTIFICATE-----\nYOUR CERT HERE\n-----END CERTIFICATE-----");
    });

    qz.security.setSignaturePromise(() => resolve => {
        resolve("SIGNATURE");
    });
}

function connectQZ() {
    if (qz.websocket.isActive()) return Promise.resolve();
    return qz.websocket.connect();
}

function printReceipt() {
    if (!window.selectedStudent || !window.selectedBook) {
        alert("No checkout data available to print.");
        return;
    }

    connectQZ().then(() => {
        const config = qz.configs.create("GLPrint");
        const data = [
            '\x1B\x40',
            '\x1B\x61\x01',
            'USM KEPLRC\n',
            '\x1B\x61\x00',
            '--------------------------------\n',
            `Title: ${window.selectedBook.title}\n`,
            `Author: ${window.selectedBook.author}\n`,
            `Student: ${window.selectedStudent.name}\n`,
            `Student ID: ${window.selectedStudent.id_number}\n`,
            `Due Date: ${window.selectedStudent.due_date}\n`,
            `Time: ${window.selectedBook.time}\n`,
            '--------------------------------\n',
            '\n\nThank you!\n\n\n',
            '\x1D\x56\x01'
        ];

        return qz.print(config, [{
            type: 'raw',
            format: 'command',
            data: data.join('')
        }]);
    }).catch(err => {
        console.error('QZ Tray Error:', err);
        alert('Printing failed. Check QZ Tray.');
    });
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


function closeCartModal() {
    const modal = document.getElementById('cartModal');
    if (modal) modal.style.display = 'none';
}

function openStudentModalFromCart() {
    if (window.cart.length === 0) {
        showToast("Cart is empty.", "error");
        return;
    }
    window.selectedBook = null;
    closeCartModal();
    openStudentModal();
}

document.addEventListener("DOMContentLoaded", function () {
    updateCartUI();
});
