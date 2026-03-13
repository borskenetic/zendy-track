<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenLibraryService;
use App\Models\Book;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class OpenLibraryCopyCatalogController extends Controller
{
    protected OpenLibraryService $service;

    public function __construct(OpenLibraryService $service)
    {
        $this->service = $service;
    }

    public function searchForm()
    {
        return view('catalog.copy.openlibrary-search');
    }

    public function search(Request $request)
    {
        $request->validate([
            'isbn' => 'required|string',
        ]);

        $record = $this->service->lookupByIsbn($request->isbn);

        if (!$record) {
            return back()->with('error', 'No record found for that ISBN.');
        }

        return view('catalog.copy.openlibrary-review', compact('record'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'control_no'          => 'nullable|string',
            'date_time_stamp'     => 'nullable|string',
            'fixed_length_data'   => 'nullable|string',
            'isbn'                => 'required|string',
            'price'               => 'nullable|string',
            'cataloging_source_a' => 'nullable|string',
            'cataloging_source_b' => 'nullable|string',
            'cataloging_source_e' => 'nullable|string',
            'main_author'         => 'nullable|string',
            'title_statement'     => 'required|string',
            'title_author'        => 'nullable|string',
            'edition'             => 'nullable|string',
            'pub_place'           => 'nullable|string',
            'publisher'           => 'nullable|string',
            'pub_year'            => 'nullable|string',
            'pages'               => 'nullable|string',
            'illustrations'       => 'nullable|string',
            'size'                => 'nullable|string',
            'volume'              => 'nullable|string',
            'content_type'        => 'nullable|string',
            'media_type'          => 'nullable|string',
            'carrier_type'        => 'nullable|string',
            'series_title'        => 'nullable|string',
            'general_note'        => 'nullable|string',
            'bibliography_note'   => 'nullable|string',
            'source_vendor'       => 'nullable|string',
            'source_date'         => 'nullable|date',
            'subject_topic'       => 'nullable|string',
            'subject_form'        => 'nullable|string',
            'genre'               => 'nullable|string',
            'library_name'        => 'nullable|string',
            'section'             => 'nullable|string',
            'call_number'         => 'nullable|string',
            'accession_no'        => 'nullable|string',
            'barcode'             => 'nullable|string',
            'rfid'                => 'nullable|string',
            'year'                => 'nullable|string',
            'course'              => 'nullable|string',
            'cover_image'         => 'nullable|string', // Open Library URL
        ]);
    
        $coverPath = null;
    
        // Download cover image from Open Library if provided
        if ($request->cover_image) {
            try {
                $imageContents = Http::get($request->cover_image)->body();
    
                $extension = pathinfo(parse_url($request->cover_image, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = $request->isbn ? $request->isbn . '.' . $extension : uniqid() . '.' . $extension;
    
                Storage::disk('public')->put('books/' . $filename, $imageContents);
    
                $coverPath = 'books/' . $filename;
            } catch (\Exception $e) {
                \Log::error("Cover download failed: " . $e->getMessage());
            }
        }
    
        // Gather all data from request
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
            'media_type',
            'carrier_type',
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
            'barcode',
            'rfid',
            'year',
            'course',
        ]);
    
        // Add downloaded cover path
        $data['cover_image'] = $coverPath;
    
        // Save to DB
        \App\Models\Book::create($data);
    
        return back()->with('success', 'Book successfully added!');
    }


}
