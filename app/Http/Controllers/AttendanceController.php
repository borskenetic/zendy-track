<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookLog;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function showScanner()
    {
        return view('attendance.scan');
    }

    public function scan(Request $request)
    {
        $request->validate(['qrcode' => 'required|string']);
        $rfid = $request->qrcode;
    
        // Try student first
        $student = Student::where('qrcode', $rfid)->first();
        if ($student) {

            $lastLog = AttendanceLog::where('student_id', $student->id)->latest()->first();
            $newStatus = $lastLog && $lastLog->status === 'IN' ? 'OUT' : 'IN';
        
            $log = AttendanceLog::create([
                'student_id' => $student->id,
                'status' => $newStatus,
                'scanned_at' => Carbon::now(),
            ]);
    
            // =============================
            // SEND SMS
            // =============================
        
            if ($student->mobile_number) {
        
                $number = $student->mobile_number;
        
                if(substr($number,0,1) == "0"){
                    $number = "+63" . substr($number,1);
                }
        
                $template = Setting::where('key','scan_sms')->value('value');

                if(!$template){
                    $template = "Hello {name}, you scanned {status} at the library.";
                }
                
                $name = $student->firstname . " " . $student->lastname;

                $time = Carbon::now()->format('F j, Y, g:i A');
                
                $message = str_replace(
                    ['{name}','{status}','{time}'],
                    [$name,$newStatus,$time],
                    $template
                );
                $payload = [[
                    'number' => $number,
                    'message' => $message
                ]];
        
                try {
        
                    Http::withHeaders([
                        'X-API-KEY' => 'library123'
                    ])
                    ->timeout(5) // do not slow down the scanner
                    ->post("https://sedulous-karis-waterlog.ngrok-free.dev/send-sms", $payload);
        
                } catch (\Exception $e) {
                    // ignore errors so scanning stays fast
                }
            }
        
            // =============================
        
            return response()->json([
                'type' => 'student',
                'student' => [
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'profile_picture' => $student->profile_picture,
                ],
                'status' => $newStatus,
                'log' => [
                    'scanned_at' => $log->scanned_at->format('Y-m-d h:i:s A'),
                ],
            ]);
        }
    
        // Try book
        $book = Book::where('rfid', $rfid)->first();
        if ($book) {
            $lastLog = BookLog::where('book_id', $book->id)->latest()->first();
            $bookStatus = (!$lastLog || $lastLog->status === 'Checked In')
                ? 'NOT CHECKED OUT'
                : 'CHECKED OUT';
    
            return response()->json([
                'type' => 'book',
                'book' => [
                    'title_statement' => $book->title_statement,
                ],
                'bookStatus' => $bookStatus,
            ]);
        }
    
        // Neither
        return response()->json([
            'type' => 'error',
            'message' => 'RFID not recognized.'
        ]);
    }

    
    // Show the change video page
    public function showChangeVideo() {
        return view('attendance.change_video');
    }
    
    // Handle video upload
    public function uploadVideo(Request $request) {
        $request->validate([
            'video' => 'required|file|mimes:mp4|max:512000', // 500MB
        ]);
    
        $video = $request->file('video');
        $filename = 'area51_product_slideshow.mp4'; // overwrite existing
        $video->move(base_path('videos'), $filename);
    
        return redirect()->route('attendance.changeVideo')->with('success', 'Video uploaded successfully!');
    }

}
