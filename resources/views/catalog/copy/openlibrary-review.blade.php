@extends('layouts.sec')
<link rel="stylesheet" href="{{ asset('public/css/books/index.css') }}">
@section('content')
<div class="container mt-5">
    <h2>Review Book Record</h2>

    @if(!empty($record['cover_image']))
        <div class="mb-3">
            <img src="{{ $record['cover_image'] }}" alt="Book Cover" style="max-width:200px;">
        </div>
    @endif

    <form method="POST" action="{{ route('book.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">

            <!-- 001 / Control No -->
            <div class="col-md-3 mb-3">
                <label for="control_no">001</label>
                <input type="text" name="control_no" class="form-control" placeholder="Control No."
                    value="{{ $record['control_no'] ?? '' }}">
            </div>

            <!-- 005 / Timestamp -->
            <div class="col-md-3 mb-3">
                <label for="marc_timestamp">005</label>
                <input type="date" name="date_time_stamp" class="form-control"
                    value="{{ $record['date_time_stamp'] ?? '' }}">
            </div>

            <!-- 008 / Form of Material -->
            <div class="col-md-3 mb-3">
                <label for="fixed_length_data">008</label>
                <select name="fixed_length_data" class="form-control">
                    <option value="">-- Select Form of Material --</option>
                    <option value="Printed" @if(($record['fixed_length_data'] ?? '')=='Printed') selected @endif>Printed</option>
                    <option value="Electronic" @if(($record['fixed_length_data'] ?? '')=='Electronic') selected @endif>Electronic</option>
                    <option value="CD" @if(($record['fixed_length_data'] ?? '')=='CD') selected @endif>CD</option>
                    <option value="Maps" @if(($record['fixed_length_data'] ?? '')=='Maps') selected @endif>Maps</option>
                </select>
            </div>

            <!-- 020 / ISBN -->
            <div class="col-md-3 mb-3">
                <label for="isbn">020 ‡a</label>
                <input type="text" name="isbn" class="form-control" placeholder="Enter ISBN"
                    value="{{ $record['isbn'] ?? '' }}">
            </div>

            <!-- 020 ‡c / Price -->
            <div class="col-md-3 mb-3">
                <label for="price">020 ‡c</label>
                <input type="text" name="price" class="form-control" placeholder="Enter Price"
                    value="{{ $record['price'] ?? '' }}">
            </div>

            <!-- 040 ‡a -->
            <div class="col-md-3 mb-3">
                <label for="cataloging_source">040 ‡a</label>
                <input type="text" name="cataloging_source_a" class="form-control"
                    value="{{ $record['cataloging_source_a'] ?? '' }}" placeholder="Enter Cataloging Source">
            </div>

            <!-- 040 ‡b -->
            <div class="col-md-3 mb-3">
                <label for="language">040 ‡b</label>
                <input type="text" name="cataloging_source_b" class="form-control"
                    value="{{ $record['cataloging_source_b'] ?? '' }}" placeholder="Enter Language">
            </div>

            <!-- 040 ‡e -->
            <div class="col-md-3 mb-3">
                <label for="description_conventions">040 ‡e</label>
                <input type="text" name="cataloging_source_e" class="form-control" value="rda"
                    placeholder="Enter Description Conventions">
            </div>

            <!-- 100 ‡a / Main Author -->
            <div class="col-md-3 mb-3">
                <label for="main_author">100 ‡a</label>
                <input type="text" name="main_author" class="form-control"
                    value="{{ $record['main_author'] ?? '' }}" placeholder="Enter Main Author">
            </div>

            <!-- 245 ‡a / Title -->
            <div class="col-md-3 mb-3">
                <label for="title">245 ‡a</label>
                <input type="text" name="title_statement" class="form-control"
                    value="{{ $record['title_statement'] ?? '' }}" placeholder="Enter Title">
            </div>

            <!-- 245 ‡c / Title Responsibility -->
            <div class="col-md-3 mb-3">
                <label for="title_responsibility">245 ‡c</label>
                <input type="text" name="title_author" class="form-control"
                    value="{{ $record['title_author'] ?? '' }}" placeholder="Enter Title Responsibility">
            </div>

            <!-- 250 / Edition -->
            <div class="col-md-3 mb-3">
                <label for="edition">250</label>
                <input type="text" name="edition" class="form-control"
                    value="{{ $record['edition'] ?? '' }}" placeholder="Enter Edition">
            </div>

            <!-- 264 ‡a / Pub Place -->
            <div class="col-md-3 mb-3">
                <label for="publication_place">264 ‡a</label>
                <input type="text" name="pub_place" class="form-control"
                    value="{{ $record['pub_place'] ?? '' }}" placeholder="Enter Publication Place">
            </div>

            <!-- 264 ‡b / Publisher -->
            <div class="col-md-3 mb-3">
                <label for="publisher">264 ‡b</label>
                <input type="text" name="publisher" class="form-control"
                    value="{{ $record['publisher'] ?? '' }}" placeholder="Enter Publisher">
            </div>

            <!-- 264 ‡c / Pub Year -->
            <div class="col-md-3 mb-3">
                <label for="publication_year">264 ‡c</label>
                <input type="text" name="pub_year" class="form-control"
                    value="{{ $record['pub_year'] ?? '' }}" placeholder="Enter Publication Year">
            </div>

            <!-- 300 ‡a / Pages -->
            <div class="col-md-3 mb-3">
                <label for="pages">300 ‡a</label>
                <input type="text" name="pages" class="form-control"
                    value="{{ $record['pages'] ?? '' }}" placeholder="Enter Pages">
            </div>

            <!-- 300 ‡b / Illustrations -->
            <div class="col-md-3 mb-3">
                <label for="illustrations">300 ‡b</label>
                <input type="text" name="illustrations" class="form-control"
                    value="{{ $record['illustrations'] ?? '' }}" placeholder="Enter Illustrations">
            </div>

            <!-- 300 ‡c / Size -->
            <div class="col-md-3 mb-3">
                <label for="size">300 ‡c</label>
                <input type="text" name="size" class="form-control" value="{{ $record['size'] ?? '' }}"
                    placeholder="Enter Size">
            </div>

            <!-- 300 ‡f / Volume -->
            <div class="col-md-3 mb-3">
                <label for="volume">300 ‡f</label>
                <input type="text" name="volume" class="form-control" value="{{ $record['volume'] ?? '' }}"
                    placeholder="Enter Volume">
            </div>

            <!-- 336 ‡a / Content Type -->
            <div class="col-md-3 mb-3">
                <label for="content_type">336 ‡a</label>
                <select name="content_type" class="form-control">
                    <option value="">-- Select Content Type --</option>
                    <option value="Manual" @if(($record['content_type']??'')=='Manual') selected @endif>Manual</option>
                    <option value="Journal" @if(($record['content_type']??'')=='Journal') selected @endif>Journal</option>
                    <option value="Magazine" @if(($record['content_type']??'')=='Magazine') selected @endif>Magazine</option>
                    <option value="Yearbook" @if(($record['content_type']??'')=='Yearbook') selected @endif>Yearbook</option>
                    <option value="Almanac" @if(($record['content_type']??'')=='Almanac') selected @endif>Almanac</option>
                    <option value="Gazetter" @if(($record['content_type']??'')=='Gazetter') selected @endif>Gazetter</option>
                    <option value="Dictionary" @if(($record['content_type']??'')=='Dictionary') selected @endif>Dictionary</option>
                </select>
            </div>

            <!-- 337 / Media Type -->
            <div class="col-md-3 mb-3">
                <label for="media_type">337 ‡a</label>
                <input type="text" name="media_type" class="form-control"
                    value="{{ $record['media_type'] ?? '' }}" placeholder="Enter Media Type">
            </div>

            <!-- 338 / Carrier Type -->
            <div class="col-md-3 mb-3">
                <label for="carrier_type">338 ‡a</label>
                <input type="text" name="carrier_type" class="form-control"
                    value="{{ $record['carrier_type'] ?? '' }}" placeholder="Enter Carrier Type">
            </div>

            <!-- 490 / Series Title -->
            <div class="col-md-3 mb-3">
                <label for="series_title">490 ‡a</label>
                <input type="text" name="series_title" class="form-control"
                    value="{{ $record['series_title'] ?? '' }}" placeholder="Enter Series Title">
            </div>

            <!-- 500 / General Note -->
            <div class="col-md-6 mb-3">
                <label for="general_note">500 ‡a</label>
                <textarea name="general_note" class="form-control"
                    placeholder="Enter General Note">{{ is_array($record['general_note']) ? implode('; ', $record['general_note']) : $record['general_note'] }}</textarea>
            </div>

            <!-- 504 / Bibliography Note -->
            <div class="col-md-6 mb-3">
                <label for="bibliography_note">504</label>
                <textarea name="bibliography_note" class="form-control"
                    placeholder="Enter Bibliography Note">{{ $record['bibliography_note'] ?? '' }}</textarea>
            </div>

            <!-- 541 / Acquisition Source & Date -->
            <div class="col-md-3 mb-3">
                <label for="source_vendor">541</label>
                <input type="text" name="source_vendor" class="form-control"
                    value="{{ $record['source_vendor'] ?? '' }}" placeholder="Enter Acquisition Source">
            </div>
            <div class="col-md-3 mb-3">
                <label for="source_date">541 ‡a</label>
                <input type="date" name="source_date" class="form-control"
                    value="{{ $record['source_date'] ?? '' }}" placeholder="Enter Acquisition Date">
            </div>

            <!-- 650 / Subjects -->
            <div class="col-md-3 mb-3">
                <label for="subject_topic">650 ‡a</label>
                <input type="text" name="subject_topic" class="form-control"
                    value="{{ is_array($record['subject_topic']) ? implode('; ', $record['subject_topic']) : $record['subject_topic'] }}"
                    placeholder="Enter Subject">
            </div>

            <div class="col-md-3 mb-3">
                <label for="subject_form">650 ‡v</label>
                <input type="text" name="subject_form" class="form-control"
                    value="{{ $record['subject_form'] ?? '' }}" placeholder="Enter Form">
            </div>

            <!-- 655 / Genre -->
            <div class="col-md-3 mb-3">
                <label for="genre">655 ‡a</label>
                <input type="text" name="genre" class="form-control" value="{{ $record['genre'] ?? '' }}"
                    placeholder="Enter Genre">
            </div>

            <!-- 852 / Library Name & Section & Call Number -->
            <div class="col-md-3 mb-3">
                <label for="library_name">852 ‡b</label>
                <select name="library_name" class="form-control">
                    <option value="">-- Select Library --</option>
                    <option value="Basic Education" @if(($record['library_name'] ?? '')=='Basic Education') selected @endif>Basic Education</option>
                    <option value="Academic Library" @if(($record['library_name'] ?? '')=='Academic Library') selected @endif>Academic Library</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="section">852 ‡c</label>
                <input type="text" name="section" class="form-control" value="{{ $record['section'] ?? '' }}"
                    placeholder="Enter Section">
            </div>
            <div class="col-md-3 mb-3">
                <label for="call_number">852 ‡h</label>
                <input type="text" name="call_number" class="form-control" value="{{ $record['call_number'] ?? '' }}"
                    placeholder="Enter Call Number">
            </div>

            <!-- 949 / Accession No -->
            <div class="col-md-3 mb-3">
                <label for="accession_no">949</label>
                <input type="text" name="accession_no" class="form-control" value="{{ $record['accession_no'] ?? '' }}"
                    placeholder="Enter Accession No.">
            </div>

            <!-- 876 / Barcode -->
            <div class="col-md-3 mb-3">
                <label for="barcode">876 ‡p</label>
                <input type="text" name="barcode" class="form-control" value="{{ $record['barcode'] ?? '' }}"
                    placeholder="Enter Barcode">
            </div>

            <!-- RFID -->
            <div class="col-md-3 mb-3">
                <label for="rfid">RFID</label>
                <input type="text" name="rfid" class="form-control" value="{{ $record['rfid'] ?? '' }}"
                    placeholder="Enter RFID">
            </div>

            <!-- Year -->
            <div class="col-md-3 mb-3">
                <label for="year">996 ‡e</label>
                <input type="text" name="year" class="form-control" value="{{ $record['year'] ?? '' }}"
                    placeholder="Enter Year">
            </div>

            <!-- Course -->
            <div class="col-md-3 mb-3">
                <label for="course">650 ‡a</label>
                <input type="text" name="course" class="form-control" value="{{ $record['course'] ?? '' }}"
                    placeholder="Enter Course">
            </div>

            <!-- Cover Image Upload -->
            <div class="col-md-12 mb-3">
                <label for="cover_image">856</label>
                <input type="file" name="cover_image" class="form-control">
            </div>

        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('book.index') }}" class="btn btn-secondary"> Go Back</a>
            <button type="submit" class="btn btn-success"> Save Book</button>
        </div>
    </form>
</div>
@endsection
