<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Landing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/books/landing.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/qz-tray/qz-tray.js"></script>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="logo" style="margin-left: 100px;">
            <img src="{{ asset('images/d.png') }}" alt="Library Logo">
        </div>
        <form action="{{ route('logout') }}" method="POST" class="mb-0" hidden>
            @csrf
            <button type="submit" class="logout-btn" onclick="logout()" style="margin-right: 60px;">Logout</button>
        </form>
    </header>

    <!-- Hero Banner -->
    <section class="hero-text">
        <img src="{{ asset('images/Bannernew.jpg') }}" alt="Banner" class="banner-img">
    </section>

    <h1 style="text-align: center; margin-bottom: 30px; margin-top: 30px;">New Arrival Books</h1>

    <!-- Carousel -->
    <div class="carousel">
        <div class="carousel-container">
            <div class="arrow left" onclick="slide(-1)">
                <svg viewBox="0 0 20 20">
                    <path d="M12.5 3L5 10l7.5 7" stroke="#5b5e64" stroke-width="2.5" fill="none" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </div>

            <div class="carousel-track" id="carouselTrack">
                @foreach ($carouselBooks as $book)
                <div class="carosel"
                    data-img="{{ $book->cover_image ? asset('storage/' . $book->cover_image) : asset('images/defaultBook.png') }}"
                    data-title="{{ $book->title_statement }}" data-author="{{ $book->main_author }}"
                    data-note="{{ $book->general_note }}" data-call="{{ $book->call_number }}" data-id="{{ $book->id }}"
                    data-year="{{ $book->pub_year }}" data-availability="{{ $book->availability }}" data-copies="1"
                    data-content="{{ $book->content_type }}" data-fixed="{{ $book->fixed_length_data }}"
                    data-library="{{ $book->library_name }}" onclick="openBookCard(this)">

                    <img src="{{ $book->cover_image ? asset('storage/' . $book->cover_image) : asset('images/defaultBook.png') }}"
                        alt="{{ $book->title_statement }}">
                    <p>{{ $book->title_statement }}</p>
                </div>
                @endforeach
            </div>

            <div class="arrow right" onclick="slide(1)">
                <svg viewBox="0 0 20 20">
                    <path d="M7.5 3L15 10l-7.5 7" stroke="#5b5e64" stroke-width="2.5" fill="none" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Layout -->
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
          <h3>Courses</h3>
        
          <div class="courses-list">
              <a href="{{ route('landing', ['course' => 'all']) }}" class="{{ request('course', 'all') === 'all' ? 'active' : '' }}">
                  View All
              </a>
        
              @foreach ($courses as $course)
              <a href="{{ route('landing', ['course' => $course]) }}" class="{{ request('course') === $course ? 'active' : '' }}">
                  {{ $course }}
              </a>
              @endforeach
          </div>
        
          <button id="e-book" onclick="goToEBookPage()">E-Book</button>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Filters -->
            <div class="filters">
                <div class="search">
                    <form method="GET" action="{{ route('landing') }}">
                        <input id="searchBar" type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search book title...">

                        <div class="lahi">
                            <!-- Subject Topic -->
                            <select name="subject_topic" onchange="this.form.submit()">
                                <option value="All">All Subject Topics</option>
                                @foreach ($subjectTopics as $topic)
                                <option value="{{ $topic }}" {{ request('subject_topic')==$topic ? 'selected' : '' }}>
                                    {{ $topic }}
                                </option>
                                @endforeach
                            </select>

                            <!-- Genre -->
                            <select name="content_type" onchange="this.form.submit()">
                                <option value="All">All Resources</option>
                                @foreach ($content_type as $content_type)
                                <option value="{{ $content_type }}" {{ request('content_type')==$content_type
                                    ? 'selected' : '' }}>
                                    {{ $content_type }}
                                </option>
                                @endforeach
                            </select>

                            <!-- Section -->
                            <select name="section" onchange="this.form.submit()">
                                <option value="All">All Sections</option>
                                @foreach ($sections as $section)
                                <option value="{{ $section }}" {{ request('section')==$section ? 'selected' : '' }}>
                                    {{ $section }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Book Grid -->
            <div class="book-grid" id="bookGrid">
                @foreach ($books as $book)
                <div class="book-card"
                    data-img="{{ $book->cover_image ? asset('storage/' . $book->cover_image) : asset('images/defaultBook.png') }}"
                    data-title="{{ $book->title_statement }}" data-author="{{ $book->main_author }}"
                    data-note="{{ $book->general_note }}" data-call="{{ $book->call_number }}" data-id="{{ $book->id }}"
                    data-year="{{ $book->pub_year }}" data-copies="{{ $book->copies }}"
                    data-availability="{{ $book->is_available == 1 ? 'Available' : 'Not Available' }}"
                    data-content="{{ $book->content_type }}" data-fixed="{{ $book->fixed_length_data }}"
                    data-library="{{ $book->library_name }}" onclick="openBookCard(this)">

                    <p class="{{ $book->is_available == 1 ? 'text-success' : 'text-danger' }}">
                        {{ $book->is_available == 1 ? 'Available' : 'Not Available' }}
                    </p>

                    <img
                        src="{{ $book->cover_image ? asset('storage/' . $book->cover_image) : asset('images/defaultBook.png') }}">

                    <p>{{ $book->title_statement }}</p>
                    <small>{{ $book->copies }} copies</small>
                </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $books->links('pagination::bootstrap-5') }}
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div class="modal" id="bookModal">
        <div class="modal-content modal-wide">
            <span class="close" onclick="closeModal()">&times;</span>

            <div class="modal-body-flex">
                <div class="modal-left">
                    <img id="modalImg" src="" alt="Book Image">
                </div>

                <div class="modal-right">
                    <h2 id="modalTitle"></h2>
                    <h4 id="modalAuthor"></h4>

                    <p><strong>Call Number:</strong> <span id="modalCallNo"></span></p>
                    <p><strong>Content Type:</strong> <span id="modalContentType"></span></p>
                    <p><strong>Material Form:</strong> <span id="modalFixed"></span></p>
                    <p><strong>Library Name:</strong> <span id="modalLibrary"></span></p>

                    <h5><strong>Abstract:</strong></h5>
                    <p id="modalAbstract"></p>

                    <button id="viewCopiesBtn" class="btn btn-secondary mt-3" style="display:none;">
                        View Copies
                    </button>

                    <button id="checkoutBtn" class="btn btn-primary mt-3" style="display:none;"
                        onclick="openStudentModal()">
                        Self Check-Out
                    </button>

                    <button id="addToCartBtn" class="btn btn-dark mt-2" style="display:none;" onclick="addToCart()">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Student ID Modal -->
    <div class="modal" id="studentModal">
        <div class="modal-content">
            <span class="close" onclick="closeStudentModal()">&times;</span>

            <h4>Self Check-Out</h4>

            <div class="mb-3">
                <label for="studentIdInput" class="form-label"><strong>Student ID</strong></label>
                <input type="text" id="studentIdInput" class="form-control" placeholder="Enter your Student ID">
            </div>

            <button class="btn btn-primary mt-3" onclick="confirmCheckout()">
                Confirm Checkout
            </button>

            <p id="studentError" class="text-danger mt-2" style="display:none;"></p>
        </div>
    </div>

    <button id="cartButton" onclick="openCartModal()" style="position:fixed; bottom:30px; right:30px; z-index:999;
                       padding:12px 20px; border-radius:50px;" class="btn btn-dark">
        Cart (<span id="cartCount">0</span>)
    </button>

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

    <div id="toastContainer" class="toast-container"></div>
    
    <script>
        window.CHECKOUT_URL = "{{ route('checkout.process') }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
    </script>

    <script src="{{ asset('js/landings.js') }}"></script>
    <script src="{{ asset('js/cart.js') }}"></script>
</body>

</html>