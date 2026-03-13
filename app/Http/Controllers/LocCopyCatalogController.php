<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocSruService;

class LocCopyCatalogController extends Controller
{
    protected LocSruService $sru;

    public function __construct(LocSruService $sru)
    {
        $this->sru = $sru;
    }

    /**
     * Show search form
     */
    public function searchForm()
    {
        return view('catalog.copy.loc-search');
    }

    /**
     * Handle search request
     */
    public function search(Request $request)
    {
        $request->validate([
            'isbn'  => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        $record = $this->sru->search(
            isbn: $request->isbn,
            title: $request->title
        );

        if (!$record) {
            return back()->with('error', 'No record found in Library of Congress.');
        }

        return view('catalog.copy.loc-review', compact('record'));
    }

    /**
     * Save selected record
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'author'      => 'nullable|string',
            'publisher'   => 'nullable|string',
            'year'        => 'nullable|string',
            'isbn'        => 'nullable|string',
            'call_number' => 'nullable|string',
        ]);

        // Example save
        // \App\Models\Book::create($request->only([
        //     'title', 'author', 'publisher', 'year', 'isbn', 'call_number'
        // ]));

        return back()->with('success', 'Record successfully copied from LoC!');
    }
}
