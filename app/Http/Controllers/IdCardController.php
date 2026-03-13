<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Intervention\Image\Facades\Image; 
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use ZipArchive;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class IdCardController extends Controller
{
    
    private function drawText($img, $text, $x, $y, $size, $color = '#000', $align = 'center', $valign = 'top')
    {
        $fontPathBold = public_path('fonts/arialbd.ttf');
        $fontPathRegular = public_path('fonts/arial.ttf');

        // If bold font exists, use it
        if (file_exists($fontPathBold)) {
            $img->text($text, $x, $y, function ($font) use ($fontPathBold, $size, $color, $align, $valign) {
                $font->file($fontPathBold);
                $font->size($size);
                $font->color($color);
                $font->align($align);
                $font->valign($valign);
            });
        } else {
            // Otherwise draw text several times for a bolder effect
            foreach ([[-1,0], [1,0], [0,-1], [0,1]] as [$ox, $oy]) {
                $img->text($text, $x + $ox, $y + $oy, function ($font) use ($fontPathRegular, $size, $color, $align, $valign) {
                    $font->file($fontPathRegular);
                    $font->size($size);
                    $font->color($color);
                    $font->align($align);
                    $font->valign($valign);
                });
            }

            // Center text (main pass)
            $img->text($text, $x, $y, function ($font) use ($fontPathRegular, $size, $color, $align, $valign) {
                $font->file($fontPathRegular);
                $font->size($size);
                $font->color($color);
                $font->align($align);
                $font->valign($valign);
            });
        }
    }

    public function front($id)
    {
        // Load the student
        $student = Student::findOrFail($id);

        // Load template (background)
        $img = Image::make(base_path('images/id_templates/front.png'));

        // --- Profile Picture ---
        if ($student->profile_picture && file_exists(base_path($student->profile_picture))) {
            $profile = Image::make(base_path($student->profile_picture))
                ->resize(1100, 1100);
        
            // Insert profile picture with rounded corners
            $img->insert($profile, 'center', 20, -400);
        }
        // --- FULL NAME ---
        $fullName = trim("{$student->firstname} {$student->lastname}");
        $nameLength = strlen($fullName);
        
        // Base font size
        $fontSize = 200;
        
        // Auto-resize based on length
        if ($nameLength > 15 && $nameLength <= 20) {
            $fontSize = 180;
        } elseif ($nameLength > 20 && $nameLength <= 25) {
            $fontSize = 160;
        } elseif ($nameLength > 25) {
            $fontSize = 140;
        }
        
        $img->text($fullName, 1100, 2065, function ($font) use ($fontSize) {
            $font->file(public_path('fonts/arial.ttf'));
            $font->size($fontSize);
            $font->color('#05014a');
            $font->align('center');
            $font->valign('top');
        });
        
        
        // --- COURSE ---
        if ($student->course) {
        
            $course = trim($student->course);
            $courseLength = strlen($course);
        
            // Base size for course text
            $courseFontSize = 150;
        
            if ($courseLength > 20 && $courseLength <= 30) {
                $courseFontSize = 135;
            } elseif ($courseLength > 30 && $courseLength <= 40) {
                $courseFontSize = 120;
            } elseif ($courseLength > 40) {
                $courseFontSize = 105;
            }
        
            $img->text($course, 1100, 2370, function ($font) use ($courseFontSize) {
                $font->file(public_path('fonts/arial.ttf'));
                $font->size($courseFontSize);
                $font->color('#05014a');
                $font->align('center');
                $font->valign('top');
            });
        }


        // Return final image
        return $img->response('png');
    }
    
    public function back($id)
    {
        $student = Student::findOrFail($id);

        // Background
        $img = Image::make(base_path('images/id_templates/back.png'));

        // QR Code
        $qrPng = QrCode::format('png')
            ->size(935)
            ->margin(0)
            ->generate($student->qrcode);
        $qrImage = Image::make((string) $qrPng);

        // Birth date
        if ($student->birthday) {
            $formattedDate = Carbon::parse($student->birthday)->format('m-d-Y');
            $this->drawText($img, $formattedDate, 3000, 800, 300, '#000');
        }

        // Signature
        if ($student->student_signature && file_exists(base_path($student->student_signature))) {
            $signature = Image::make(base_path($student->student_signature))->resize(1300, 600);
            $img->insert($signature, 'center', 0, 1150);
        }

        // QR code
        $img->insert($qrImage, 'top-left', 630, 400);

        return $img->response('png');
    }

    public function download($id)
    {
        $student = Student::findOrFail($id);

        // Generate both sides
        $front = $this->front($id)->getContent();
        $back = $this->back($id)->getContent();

        // Paths
        $zipPath = storage_path("app/temp_id_{$id}.zip");
        $frontPath = storage_path("app/front_{$id}.png");
        $backPath = storage_path("app/back_{$id}.png");

        // Save temporary images
        file_put_contents($frontPath, $front);
        file_put_contents($backPath, $back);

        // Create zip
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($frontPath, "{$student->lastname}_{$student->firstname}_front.png");
            $zip->addFile($backPath, "{$student->lastname}_{$student->firstname}_back.png");
            $zip->close();
        }

        // Clean up
        unlink($frontPath);
        unlink($backPath);

        // Download
        return response()->download($zipPath, "{$student->lastname}_{$student->firstname}_ID.zip")
                         ->deleteFileAfterSend(true);
    }
}
