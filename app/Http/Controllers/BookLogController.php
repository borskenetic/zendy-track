<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\BookLog;
use App\Models\Student;
use App\Models\FineSetting;
use Carbon\Carbon;

class BookLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = BookLog::with('book');

        if ($request->filled('patron_name')) {
            $logs->where('patron_name', $request->patron_name);
        }

        if ($request->filled('book_title')) {
            $logs->whereHas('book', function ($query) use ($request) {
                $query->where('title_statement', $request->book_title);
            });
        }

        if ($request->filled('start_date')) {
            $logs->whereDate('timestamp', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $logs->whereDate('timestamp', '<=', $request->end_date);
        }

        $logs = $logs->latest()->paginate(10);

        $patronNames = BookLog::pluck('patron_name')->unique();
        $bookTitles  = Book::pluck('title_statement')->unique();

        return view('books.logs', compact('logs', 'patronNames', 'bookTitles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfid'        => 'required|string',
            'status'      => 'required|string|in:checked_out,checked_in',
            'patron_name' => 'required|string|max:255',
        ]);

        $book = Book::where('rfid', $request->rfid)->first();

        if (!$book) {
            return back()->with('error', 'Book not found.');
        }

        $action = $request->status;

        /** 🔒 STRONG DOUBLE CHECK-IN / CHECK-OUT GUARD */
        $lastLog = BookLog::where('book_id', $book->id)
            ->latest('timestamp')
            ->first();

        if ($action === 'checked_out' && $lastLog && $lastLog->status === 'Checked Out') {
            return back()->with('error', 'This book is already checked out.');
        }

        if ($action === 'checked_in' && (!$lastLog || $lastLog->status !== 'Checked Out')) {
            return back()->with('error', 'This book is already checked in.');
        }

        $newStatus = $action === 'checked_out' ? 'Checked Out' : 'Checked In';
        $book->availability = $action === 'checked_out' ? 'Borrowed' : 'Available';

        $settings = FineSetting::latest('created_at')->first();

        $dueDate       = null;
        $returnedDate  = null;
        $fineIncurred  = null;

        /** 📤 CHECK OUT */
        if ($action === 'checked_out') {
            $loanDays = $settings->loan_duration_days ?? 7;
            $dueDate  = Carbon::now('Asia/Manila')->addDays($loanDays);
        }

        /** 📥 CHECK IN + FINE COMPUTATION */
    
        if ($action === 'checked_in') {
            $returnedDate = Carbon::now('Asia/Manila');
        
            if ($lastLog && $lastLog->due_date) {
                // Use the due date from the latest checkout
                $dueDate = $lastLog->due_date;
        
                $gracePeriod = $settings->grace_period_days ?? 0;
                $finePerDay  = $settings->fine_per_day ?? 0;
                $maxFine     = $settings->max_fine;
        
                // FLOOR to ensure whole number
                $overdueDays = $dueDate->diffInDays($returnedDate, false);
                $overdueDays = max(0, floor($overdueDays) - $gracePeriod);
        
                $fineIncurred = $overdueDays * $finePerDay;
                
                // Set modal session if overdue
                if ($overdueDays > 0) {
                    session()->flash('overdue_modal', [
                    'book_title' => $book->title_statement,
                    'patron_name'=> $request->patron_name,
                    'days_late' => $overdueDays,
                    'fine' => $fineIncurred,
                    'breakdown' => "{$overdueDays} day(s) × ₱".number_format($finePerDay,2)." = ₱".number_format($fineIncurred,2),
                    ]);
                }
        
                if (!is_null($maxFine)) {
                    $fineIncurred = min($fineIncurred, $maxFine);
                }
            }
        }
        
        BookLog::create([
            'book_id'       => $book->id,
            'patron_name'   => $request->patron_name,
            'status'        => $newStatus,
            'timestamp'     => Carbon::now('Asia/Manila'),
            'due_date'      => $dueDate,       // ✅ now correct for check-in
            'returned_date' => $returnedDate,
            'fine_incurred' => $fineIncurred,
]);

        $book->save();

        return back()->with('success', "Book has been {$newStatus} successfully!");
    }

    public function patronSuggestions(Request $request)
    {
        $search = $request->get('query', '');

        $suggestions = Student::where(function ($q) use ($search) {
            $q->where('firstname', 'LIKE', "%{$search}%")
              ->orWhere('lastname', 'LIKE', "%{$search}%")
              ->orWhereRaw(
                  "LOWER(CONCAT(firstname, ' ', lastname)) LIKE ?",
                  ["%".strtolower($search)."%"]
              );
        })
        ->limit(10)
        ->get()
        ->map(function ($s) {
            return [
                'id'   => $s->id,
                'name' => trim($s->firstname . ' ' . $s->lastname),
            ];
        });

        return response()->json($suggestions);
    }

    public function bookSuggestions(Request $request)
    {
        $search = $request->get('query', '');

        $books = Book::where(function ($q) use ($search) {
                $q->where('title_statement', 'LIKE', "%{$search}%")
                  ->orWhere('main_author', 'LIKE', "%{$search}%")
                  ->orWhere('rfid', 'LIKE', "%{$search}%")
                  ->orWhere('barcode', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json(
            $books->map(function ($b) {
                return [
                    'id'      => $b->id,
                    'title'   => $b->title_statement,
                    'author'  => $b->main_author,
                    'barcode' => $b->barcode,
                    'rfid'    => $b->rfid,
                ];
            })
        );
    }
}
