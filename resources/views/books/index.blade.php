@extends('layouts.main')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/css/books/index.css') }}">
    
@endsection

@section('content')

<div class="row mb-3 g-2 align-items-center custom-margin">
    <div class="col-md-8">
        <form action="{{ route('book.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Search books..."
                value="{{ request('search') }}" />
            <button class="btn btn-search">Search</button>
        </form>
    </div>

    <div class="col-md-4 button_div text-end">
        <a href="{{ route('book.create') }}" class="btn btn-addbook">Cataloging</a>
        <a href="https://area51lmslibrary.com/user-account/" class="btn btn-51_learn" target="_blank" rel="noopener noreferrer" hidden>
            51 Learned
        </a>
    </div>
</div>

<!-- Top Filters + Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
    <form id="filterForm" action="{{ route('book.index') }}" method="GET" class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            <select id="program" name="program" class="form-select">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" {{ request('program') == $program->id ? 'selected' : '' }}>
                        {{ $program->program_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="year_filter" class="form-select">
                <option value="">Year Filter</option>
                <option value="exact" {{ request('year_filter')=='exact' ? 'selected' : '' }}>Exact</option>
                <option value="before" {{ request('year_filter')=='before' ? 'selected' : '' }}>Before</option>
                <option value="after" {{ request('year_filter')=='after' ? 'selected' : '' }}>After</option>
                <option value="between" {{ request('year_filter')=='between' ? 'selected' : '' }}>Between</option>
            </select>
        </div>

        <div class="col-md-2">
            <input type="number" name="year1" class="form-control" placeholder="Year 1" value="{{ request('year1') }}">
        </div>

        <div class="col-md-2" id="year2Field" style="{{ request('year_filter') == 'between' ? '' : 'display:none;' }}">
            <input type="number" name="year2" class="form-control" placeholder="Year 2" value="{{ request('year2') }}">
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-filter">Apply Filters</button>
        </div>
    </form>
</div>

<!-- Status + Utility Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap2" style="margin-top: 50px;">
    <div class="btn-status">
        <a href="{{ route('book.index') }}" class="btn btn-all">📚 All</a>
        <a href="{{ route('book.index', ['status' => 'Available']) }}" class="btn btn-available">✅ Available</a>
        <a href="{{ route('book.index', ['status' => 'Borrowed']) }}" class="btn btn-borrowed">❌ Borrowed</a>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('ebooks.index') }}" class="btn btn-e-book">View E-Resources</a>
    </div>
</div>


<!-- Book Table -->
<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year Published</th>
                    <th>Resource Type</th>
                    <th>Copies</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($books as $book)
                    <tr>
                        <td>{{ $book->title_statement }}</td>
                        <td>{{ $book->main_author }}</td>
                        <td>{{ $book->pub_year }}</td>
                        <td>{{ $book->content_type }}</td>
                        <td>{{ $book->copies }}</td>

                        @if($book->copies == 1)
                            @php $copy = \App\Models\Book::find($book->sample_id); @endphp
                            <td class="{{ $copy->availability === 'Available' ? 'text-success' : 'text-danger' }}">
                                {{ $copy->availability }}
                            </td>
                            <td>
                                <div class="dropdown1">
                                    <button class="dropdown1-button">Actions</button>
                                    <div class="dropdown1-content">
                                        <a href="{{ route('book.show', $copy->id) }}" class="dropdown-item1">View</a>
                                        <a href="{{ route('book.edit', $copy->id) }}" class="dropdown-item2">Edit</a>
                                        <button class="dropdown-item3" type="button" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $copy->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <!-- Modal -->
                                <div class="modal fade" id="deleteModal{{ $copy->id }}" tabindex="-1"
                                    aria-labelledby="deleteModalLabel{{ $copy->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-3 shadow-lg">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $copy->id }}">Confirm Delete</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete <strong>{{ $copy->title_statement }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('books.destroy', $copy->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        @else
                            <td></td>
                            <td>
                                <div class="dropdown1">
                                    <button class="dropdown1-button">Actions</button>
                                    <div class="dropdown1-content">
                                        <a href="{{ route('books.copies', [
                                            'title' => $book->title_statement,
                                            'author' => $book->main_author,
                                            'year' => $book->pub_year
                                        ]) }}" class="btn btn-sm btn-primary">View Copies</a>
                                    </div>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No books found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $books->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>


<!-- Import / Export Section -->
<div class="card mt-4 p-4">
    <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-center">
        @csrf
        <div class="col-md-6">
            <input type="file" name="file" class="form-control" required accept=".csv,.xlsx" />
        </div>
        <div class="col-md-6 d-flex gap-2">
            <button class="btn btn-import w-50">Import Books</button>
            <a href="{{ route('export.books', request()->query()) }}" class="btn btn-export w-50">Export Books</a>
        </div>
    </form>
</div>


<script>
    document.querySelector('[name="year_filter"]').addEventListener('change', function () {
        document.getElementById('year2Field').style.display = (this.value === 'between') ? '' : 'none';
    });
</script>
@endsection
@section('footer')

    <footer>
        <div class="a51-footer">
            <h4 style="color: white; font-size:15px">Pantas © 2025. All Rights Reserved.</h4>
        </div>
    </footer>
@endsection


