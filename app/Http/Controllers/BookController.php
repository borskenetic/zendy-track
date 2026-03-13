<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Prospectus;
use App\Models\Program;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
   public function index(Request $request)
    {
        // --- Get programs for filter dropdown ---
        $programs = Program::orderBy('program_name')->get();
    
        // --- Base filtered query ---
        $filteredQuery = Book::query();
    
        // Status filter
        if ($request->has('status') && in_array($request->status, ['Available', 'Borrowed'])) {
            $filteredQuery->where('availability', $request->status);
        }
    
        // Program filter
        if ($request->filled('program')) {
            $filteredQuery->whereHas('programs', function ($q) use ($request) {
                $q->where('programs.id', $request->program);
            });
        }
    
        // Year filter
        if ($request->filled('year_filter') && $request->filled('year1')) {
            $year1 = (int) $request->year1;
            $year2 = (int) $request->year2;
    
            switch ($request->year_filter) {
                case 'exact':
                    $filteredQuery->where('pub_year', $year1);
                    break;
                case 'before':
                    $filteredQuery->where('pub_year', '<=', $year1);
                    break;
                case 'after':
                    $filteredQuery->where('pub_year', '>=', $year1);
                    break;
                case 'between':
                    if ($request->filled('year2')) {
                        $filteredQuery->whereBetween('pub_year', [$year1, $year2]);
                    }
                    break;
            }
        }
    
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('title_statement', 'like', "%$search%")
                  ->orWhere('rfid', 'like', "%$search%")
                  ->orWhere('call_number', 'like', "%$search%")
                  ->orWhere('main_author', 'like', "%$search%")
                  ->orWhere('control_no', 'like', "%$search%")
                  ->orWhere('course', 'like', "%$search%")
                  ->orWhere('year', 'like', "%$search%")
                  ->orWhere('barcode', 'like', "%$search%");
            });
        }
    
        // --- Dynamic dropdowns for course/year ---
        $courses = Book::when($request->program, fn($q) => $q->whereHas('programs', fn($p) => $p->where('program_name', $request->program)))
            ->select('course')->distinct()->orderBy('course')->pluck('course');
    
        $years = Book::when($request->program, fn($q) => $q->whereHas('programs', fn($p) => $p->where('program_name', $request->program)))
            ->when($request->course, fn($q) => $q->where('course', $request->course))
            ->select('year')->distinct()->orderBy('year')->pluck('year');
    
        // --- Aggregate on filtered query ---
        $books = DB::table(DB::raw("({$filteredQuery->toSql()}) as sub"))
            ->mergeBindings($filteredQuery->getQuery())
            ->select(
                'main_author',
                'title_statement',
                'pub_year',
                'content_type', // add this
                DB::raw('COUNT(*) as copies'),
                DB::raw('MIN(id) as sample_id')
            )
            ->groupBy('main_author', 'title_statement', 'pub_year', 'content_type') // add content_type here
            ->orderBy('title_statement')
            ->paginate(10)
            ->withQueryString();

    
        return view('books.index', compact('books', 'programs', 'courses', 'years'));
    }
    
public function viewCopies(Request $request)
    {
        // Validate that nullable params exist
        if (!$request->filled('title') || !$request->filled('author') || !$request->filled('year')) {
            abort(404, 'Missing book group information.');
        }
    
        $title = $request->title;
        $author = $request->author;
        $year = $request->year;
    
        // Get all copies matching the same group
        $copies = Book::where('title_statement', $title)
            ->where('main_author', $author)
            ->where('pub_year', $year)
            ->orderBy('accession_no', 'asc')
            ->paginate(10)
            ->withQueryString(); // Keep URL parameters when switching pages
    
        return view('books.copies', compact('copies', 'title', 'author', 'year'));
    }

    public function landingPage(Request $request)
    {
        // ----------------------
        // 1) Base Eloquent query (used for applying filters & search)
        // ----------------------
        $query = Book::query();
    
        // Filters
        if ($request->filled('course') && $request->course !== 'all') {
            $query->where('course', $request->course);
        }
    
        if ($request->filled('subject_topic') && $request->subject_topic !== 'All') {
            $query->where('subject_topic', $request->subject_topic);
        }
    
        if ($request->filled('genre') && $request->genre !== 'All') {
            $query->where('genre', $request->genre);
        }
        
        if ($request->filled('content_type') && $request->content_type !== 'All') {
            $query->where('content_type', $request->content_type);
        }
    
        if ($request->filled('section') && $request->section !== 'All') {
            $query->where('section', $request->section);
        }
    
        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_statement', 'like', "%$search%")
                    ->orWhere('rfid', 'like', "%$search%")
                    ->orWhere('call_number', 'like', "%$search%")
                    ->orWhere('main_author', 'like', "%$search%")
                    ->orWhere('course', 'like', "%$search%")
                    ->orWhere('year', 'like', "%$search%")
                    ->orWhere('barcode', 'like', "%$search%");
            });
        }
    
        // ----------------------
        // 2) Carousel (recent books, always unfiltered)
        // ----------------------
        $carouselBooks = Book::orderBy('created_at', 'desc')->take(12)->get();
    
        // ----------------------
        // 3) Grouped subquery (count copies, get a sample id, detect availability)
        // ----------------------
        $grouped = $query->getQuery()->clone()
            ->select(
                'title_statement',
                'main_author',
                'pub_year',
                DB::raw('COUNT(*) AS copies'),
                DB::raw('MIN(id) AS sample_id'),
                DB::raw("MAX(CASE WHEN availability = 'Available' THEN 1 ELSE 0 END) AS is_available")
            )
            ->groupBy('title_statement', 'main_author', 'pub_year');
    
        // Note: grouped is a Query\Builder (not Eloquent), we will use it as a subquery
        // ----------------------
        // 4) Join grouped subquery back to books to grab sample fields
        // ----------------------
        $books = DB::table(DB::raw("({$grouped->toSql()}) as grouped"))
            ->mergeBindings($grouped) // bring the bindings from grouped into this query
            ->join('books', 'books.id', '=', 'grouped.sample_id')
            ->select(
                'grouped.title_statement',
                'grouped.main_author',
                'grouped.pub_year',
                'grouped.copies',
                'grouped.sample_id as id',
                'grouped.is_available',
                'books.call_number',
                'books.general_note',
                'books.cover_image',
                'books.rfid',
                'books.barcode',
                'books.content_type',
                'books.fixed_length_data',
                'books.library_name' 
            )
            ->orderBy('grouped.title_statement')
            ->paginate(20)
            ->withQueryString();
    
        // ----------------------
        // 5) Distinct dropdown sources (always from full table)
        // ----------------------
        $subjectTopics = Book::select('subject_topic')
            ->distinct()
            ->whereNotNull('subject_topic')
            ->orderBy('subject_topic')
            ->pluck('subject_topic');
    
        $genres = Book::select('genre')
            ->distinct()
            ->whereNotNull('genre')
            ->orderBy('genre')
            ->pluck('genre');
        
        $content_type = Book::select('content_type')
            ->distinct()
            ->whereNotNull('content_type')
            ->orderBy('content_type')
            ->pluck('content_type');
            
        $sections = Book::select('section')
            ->distinct()
            ->whereNotNull('section')
            ->orderBy('section')
            ->pluck('section');
    
        $courses = Book::select('course')
            ->distinct()
            ->whereNotNull('course')
            ->orderBy('course')
            ->pluck('course');
    
        // ----------------------
        // 6) Return view
        // ----------------------
        return view('books.landing', compact('books', 'carouselBooks', 'subjectTopics', 'genres', 'sections', 'courses', 'content_type'));
    }


    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('book.index')->with('success', 'Book deleted successfully!');
    }

    public function create()
    {
        $courses = Prospectus::select('course')->distinct()->orderBy('course')->pluck('course');
        $programs = Program::orderBy('program_name')->get();
        return view('books.create', compact('courses', 'programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'control_no' => 'nullable|string|max:255',
            'date_time_stamp' => 'nullable|string|max:255',
            'fixed_length_data' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'cataloging_source_a' => 'nullable|string|max:255',
            'cataloging_source_b' => 'nullable|string|max:255',
            'cataloging_source_e' => 'nullable|string|max:255',
            'main_author' => 'nullable|string|max:255',
            'title_statement' => 'nullable|string|max:255',
            'title_author' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',
            'pub_place' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'pub_year' => 'nullable|string|max:255',
            'pages' => 'nullable|string|max:255',
            'illustrations' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'volume' => 'nullable|string|max:255',
            'content_type' => 'nullable|string|max:255',
            'content_code' => 'nullable|string|max:255',
            'media_type' => 'nullable|string|max:255',
            'media_code' => 'nullable|string|max:255',
            'carrier_type' => 'nullable|string|max:255',
            'carrier_code' => 'nullable|string|max:255',
            'series_title' => 'nullable|string|max:255',
            'general_note' => 'nullable|string|max:255',
            'bibliography_note' => 'nullable|string|max:255',
            'source_vendor' => 'nullable|string|max:255',
            'source_date' => 'nullable|string|max:255',
            'subject_topic' => 'nullable|string|max:255',
            'subject_form' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'library_name' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'call_number' => 'nullable|string|max:255',
            'accession_no' => 'nullable|string|max:255',
            'created_at' => 'nullable|string|max:255',
            'updated_at' => 'nullable|string|max:255',
            'barcode' => 'nullable|unique:books,barcode',
            'rfid' => 'nullable|unique:books,rfid',
            'availability' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Handle cover image
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
            \File::copy(
                storage_path('app/public/' . $coverPath),
                public_path('storage/' . $coverPath)
            );
        }

        // Create book (NO program field here)
        $book = Book::create([
            'control_no' => $request->control_no,
            'date_time_stamp' => $request->date_time_stamp,
            'fixed_length_data' => $request->fixed_length_data,
            'isbn' => $request->isbn,
            'price' => $request->price,
            'cataloging_source_a' => $request->cataloging_source_a,
            'cataloging_source_b' => $request->cataloging_source_b,
            'cataloging_source_e' => $request->cataloging_source_e,
            'main_author' => $request->main_author,
            'title_statement' => $request->title_statement,
            'title_author' => $request->title_author,
            'edition' => $request->edition,
            'pub_place' => $request->pub_place,
            'publisher' => $request->publisher,
            'pub_year' => $request->pub_year,
            'pages' => $request->pages,
            'illustrations' => $request->illustrations,
            'size' => $request->size,
            'volume' => $request->volume,
            'content_type' => $request->content_type,
            'content_code' => $request->content_code,
            'media_type' => $request->media_type,
            'media_code' => $request->media_code,
            'carrier_type' => $request->carrier_type,
            'carrier_code' => $request->carrier_code,
            'series_title' => $request->series_title,
            'general_note' => $request->general_note,
            'bibliography_note' => $request->bibliography_note,
            'source_vendor' => $request->source_vendor,
            'source_date' => $request->source_date,
            'subject_topic' => $request->subject_topic,
            'subject_form' => $request->subject_form,
            'genre' => $request->genre,
            'library_name' => $request->library_name,
            'section' => $request->section,
            'call_number' => $request->call_number,
            'accession_no' => $request->accession_no,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'barcode' => $request->barcode,
            'rfid' => $request->rfid,
            'availability' => 'Available',
            'year' => $request->year,
            'course' => $request->course,
            'cover_image' => $coverPath,
        ]);

        // Attach programs via pivot
        if (!empty($request->program_ids)) {
            $book->programs()->attach($request->program_ids);
        }

        return redirect()->route('book.index')->with('success', 'Book added successfully!');
    }


    public function show($id)
    {
        $book = Book::findOrFail($id);
        return view('books.show', compact('book'));
    }

    public function edit($id)
    {
        $book = Book::with('programs')->findOrFail($id);
        $programs = Program::all(); // list for dropdown
        return view('books.edit', compact('book', 'programs'));
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'control_no' => 'nullable|string|max:255',
            'date_time_stamp' => 'nullable|string|max:255',
            'fixed_length_data' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'cataloging_source_a' => 'nullable|string|max:255',
            'cataloging_source_b' => 'nullable|string|max:255',
            'cataloging_source_e' => 'nullable|string|max:255',
            'main_author' => 'nullable|string|max:255',
            'title_statement' => 'nullable|string|max:255',
            'title_author' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',
            'pub_place' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'pub_year' => 'nullable|string|max:255',
            'pages' => 'nullable|string|max:255',
            'illustrations' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'volume' => 'nullable|string|max:255',
            'content_type' => 'nullable|string|max:255',
            'content_code' => 'nullable|string|max:255',
            'media_type' => 'nullable|string|max:255',
            'media_code' => 'nullable|string|max:255',
            'carrier_type' => 'nullable|string|max:255',
            'carrier_code' => 'nullable|string|max:255',
            'series_title' => 'nullable|string|max:255',
            'general_note' => 'nullable|string|max:255',
            'bibliography_note' => 'nullable|string|max:255',
            'source_vendor' => 'nullable|string|max:255',
            'source_date' => 'nullable|string|max:255',
            'subject_topic' => 'nullable|string|max:255',
            'subject_form' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'library_name' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'call_number' => 'nullable|string|max:255',
            'accession_no' => 'nullable|string|max:255',
            'created_at' => 'nullable|string|max:255',
            'updated_at' => 'nullable|string|max:255',
            'barcode' => 'nullable|unique:books,barcode,' . $id,
            'rfid' => 'nullable|unique:books,rfid,' . $id,
            'year' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
            // ❌ remove single program validation (we use many-to-many now)
            // 'program' => 'nullable|string|max:255',
            'program_ids' => 'nullable|array',
            'program_ids.*' => 'exists:programs,id',
        ]);

        $data = $request->only([
            'control_no',
            'date_time_stamp',
            'fixed_length_data',
            'isbn',
            'price',
            'cataloging_source_a',
            'cataloging_source_b',
            'cataloging_source_e',
            'main_author',
            'title_statement',
            'title_author',
            'edition',
            'pub_place',
            'publisher',
            'pub_year',
            'pages',
            'illustrations',
            'size',
            'volume',
            'content_type',
            'content_code',
            'media_type',
            'media_code',
            'carrier_type',
            'carrier_code',
            'series_title',
            'general_note',
            'bibliography_note',
            'source_vendor',
            'source_date',
            'subject_topic',
            'subject_form',
            'genre',
            'library_name',
            'section',
            'call_number',
            'accession_no',
            'created_at',
            'updated_at',
            'barcode',
            'rfid',
            'year',
            'course',
            // ❌ don’t include program single field
        ]);

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');

            \File::copy(
                storage_path('app/public/' . $coverPath),
                public_path('storage/' . $coverPath)
            );

            $data['cover_image'] = $coverPath;
        }

        // ✅ First update book fields
        $book->update($data);

        if (!empty($request->program_ids)) {
            // Replace existing programs with the new ones
            $book->programs()->sync($request->program_ids);
        } else {
            // No program selected → detach all
            $book->programs()->detach();
        }

        return redirect()->route('book.index')->with('success', 'Book updated successfully!');
    }


    public function getYears(Request $request)
    {
        $program = $request->program;
        $years = Book::where('program', $program)
            ->select('year')->distinct()->orderBy('year')->pluck('year');
        return response()->json($years);
    }

    public function getCourses(Request $request)
    {
        $program = $request->program;
        $year = $request->year;
        $courses = Book::where('program', $program)
            ->where('year', $year)
            ->select('course')->distinct()->orderBy('course')->pluck('course');
        return response()->json($courses);
    }

    public function downloadBookReport()
    {
        // Count total books per title
        $booksByTitle = Book::select('title_statement')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('title_statement')
            ->orderBy('title_statement')
            ->get();

        $totalBooks = $booksByTitle->sum('total');

        // Get all subjects grouped by course
        $books = DB::table('books')
            ->select('course', 'title_statement')
            ->groupBy('course', 'title_statement')
            ->orderBy('course')
            ->orderBy('title_statement')
            ->get();

        $groupedBooks = $books->groupBy('course');

        // Pass both variables to the view
        $pdf = Pdf::loadView('pdf.book_report', compact('booksByTitle', 'totalBooks', 'groupedBooks'));

        return $pdf->download('book_report.pdf');
    }
}