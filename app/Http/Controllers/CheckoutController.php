<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\BookLog;
use App\Models\FineSetting;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        Log::info('Checkout request data: ', $request->all());

        try {
            // ✅ 1. VALIDATION (supports both single + cart)
            $request->validate([
                'student_id' => 'required|string',
                'book_id'    => 'nullable|integer',
                'books'      => 'nullable|array',
                'books.*.id' => 'required_with:books|integer',
            ]);

            // ✅ 2. FIND STUDENT
            $student = Student::where('id_number', $request->student_id)->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID not found.'
                ]);
            }

            // ✅ 3. CHECK OVERDUE
            $hasOverdue = BookLog::where('patron_name', "{$student->lastname}, {$student->firstname}")
                ->where('status', 'Checked Out')
                ->whereDate('due_date', '<', now())
                ->exists();

            if ($hasOverdue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout blocked: student has overdue book(s).'
                ]);
            }

            // ✅ 4. NORMALIZE INPUT (THIS FIXES YOUR ERROR)
            $bookIds = [];

            if ($request->book_id) {
                $bookIds[] = $request->book_id;
            }

            if ($request->books) {
                foreach ($request->books as $b) {
                    $bookIds[] = $b['id'];
                }
            }

            if (empty($bookIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No books provided.'
                ]);
            }

            // ✅ 5. SETTINGS
            $fineSetting = FineSetting::orderBy('created_at', 'desc')->first();

            if (!$fineSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fine settings not configured.'
                ]);
            }

            $borrowedAt = Carbon::now();
            $dueDate = null;
            $processedBooks = [];

            // ✅ 6. LOOP THROUGH BOOKS
            foreach ($bookIds as $bookId) {

                $book = Book::find($bookId);

                if (!$book) {
                    continue;
                }

                if ($book->availability !== 'Available') {
                    continue;
                }

                $dueDate = $borrowedAt->copy()->addDays($fineSetting->loan_duration_days);

                // CREATE LOG
                BookLog::create([
                    'book_id'       => $book->id,
                    'patron_name'   => "{$student->lastname}, {$student->firstname}",
                    'status'        => 'Checked Out',
                    'timestamp'     => $borrowedAt,
                    'due_date'      => $dueDate,
                    'fine_incurred' => 0
                ]);

                // UPDATE BOOK
                $book->update(['availability' => 'Borrowed']);

                $processedBooks[] = [
                    'id' => $book->id,
                    'title' => $book->title_statement
                ];
            }

            // ✅ 7. FINAL RESPONSE
            return response()->json([
                'success' => true,
                'student' => [
                    'name' => "{$student->lastname}, {$student->firstname}",
                    'id_number' => $student->id_number,
                    'course' => $student->course
                ],
                'books' => $processedBooks,
                'due_date' => $dueDate ? $dueDate->format('Y-m-d') : null
            ]);

        } catch (\Exception $e) {

            Log::error('Checkout Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}