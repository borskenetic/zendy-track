<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Copies</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('public/css/books/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/books/index.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (needed for modals) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

<div class="container mt-4">

    <!-- Back Button -->
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">← Back</a>

    <h3>
        Copies of: <strong>{{ $title }}</strong><br>
        <small>{{ $author }} — {{ $year }}</small>
    </h3>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Accession No</th>
                <th>Barcode</th>
                <th>RFID</th>
                <th>Availability</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($copies as $copy)
            <tr>
                <td>{{ $copy->accession_no }}</td>
                <td>{{ $copy->barcode }}</td>
                <td>{{ $copy->rfid }}</td>

                <td class="{{ $copy->availability === 'Available' ? 'text-success' : 'text-danger' }}">
                    {{ $copy->availability }}
                </td>

                <td>{{ $copy->created_at?->format('Y-m-d') }}</td>

                <td>
                    @if($copy->availability === 'Available')
                        <button 
                            class="btn btn-primary btn-sm"
                            onclick="openStudentCheckout({{ $copy->id }})">
                            Self Check-Out
                        </button>
                        <button 
                            class="btn btn-primary btn-sm"
                            onclick="selectBookAndAddToCart({{ $copy->id }}, '{{ addslashes($copy->title_statement) }}', '{{ addslashes($author) }}')">
                            Add to Cart
                        </button>

                        
                    @else
                        <span class="text-danger">Not Available</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    {{ $copies->links('pagination::bootstrap-5') }}

</div>

<div class="modal fade" id="studentCheckoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">

            <div class="modal-header">
                <h5 class="modal-title">Self Check-Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="selectedCopyId">

                <label class="form-label">Student ID</label>
                <input type="text" id="copyStudentId" class="form-control">

                <div id="copyError" class="text-danger mt-2 d-none"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" onclick="confirmCopyCheckout()">
                    Confirm Checkout
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal" id="cartModal">
        <div class="modal-content cart-modal-clean">
            <span class="close" onclick="closeCartModal()">&times;</span>

            <div class="cart-header">
                <h2>Borrow Cart</h2>
                <p>Maximum of 5 books allowed</p>
            </div>

            <div id="cartBody" class="cart-body">
                <ul id="cartList" class="cart-list"></ul>

                <div id="emptyCart" class="empty-cart" style="display:none;">
                    Your cart is empty.
                </div>
            </div>

            <div class="cart-footer">
                <div class="cart-count">
                    Total Books: <strong id="cartTotal">0</strong>
                </div>

                <button class="btn btn-dark px-5" onclick="openStudentModalFromCart()">
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>



<button id="cartButton"
    onclick="openCartModal()"
    style="position:fixed; bottom:30px; right:30px; z-index:999;
           padding:12px 20px; border-radius:50px;"
    class="btn btn-dark">
    Cart (<span id="cartCount">0</span>)
</button>
<script src="{{ asset('public/js/landings.js') }}"></script>
<script src="{{ asset('public/js/cart.js') }}"></script>

</body>
</html>
