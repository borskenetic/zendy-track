<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\Http;

class SmsController extends Controller
{

    public function index()
    {
        $courses = \App\Models\Student::select('course')
            ->whereNotNull('course')
            ->distinct()
            ->orderBy('course')
            ->pluck('course');
    
        return view('sms.blast', [
            'courses' => $courses
        ]);
    }

    public function scanMessage()
    {
        $setting = Setting::where('key','scan_sms')->first();
    
        return view('sms.scan_message',[
            'message' => $setting ? $setting->value : 'Hello {name}, you scanned {status} at the library.'
        ]);
    }
    
    public function updateScanMessage(Request $request)
    {
        $request->validate([
            'message' => 'required'
        ]);
    
        Setting::updateOrCreate(
            ['key'=>'scan_sms'],
            ['value'=>$request->message]
        );
    
        return back()->with('success','Scan SMS updated');
    }
    
    public function count(Request $request)
    {
    
        $query = Student::whereNotNull('mobile_number');
    
        if ($request->year) {
            $query->where('year', $request->year);
        }
    
        if ($request->course) {
            $query->where('course', $request->course);
        }
    
        return response()->json([
            'count' => $query->count()
        ]);
    
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);
    
        $students = Student::whereNotNull('mobile_number')->get();
    
        $payload = [];
    
        foreach ($students as $student) {
            $name = $student->firstname . ' ' . $student->lastname;
            $message = str_replace('{name}', $name, $request->message);
            $number = $student->mobile_number;
    
            if(substr($number,0,1) == "0"){
                $number = "+63" . substr($number,1);
            }
    
            $payload[] = [
                'number' => $number,
                'message' => $message
            ];
        }
    
        // send to your local Python server
        $python_server = "https://sedulous-karis-waterlog.ngrok-free.dev/send-sms"; // your ngrok URL
        $api_key = "library123"; // must match Python server
        
        $response = Http::withHeaders([
            'X-API-KEY' => $api_key
        ])->timeout(300) 
        ->post($python_server, $payload);
            
        return back()->with('success','SMS sent successfully');
    }
}