@extends('layouts.sec')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/books/index.css') }}">
    
@endsection
@section('content')
<div class="container">
    <h3>Fine Settings</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('fines.update') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Fine per Day (₱)</label>
            <input type="number" step="0.01" name="fine_per_day"
                   class="form-control"
                   value="{{ $settings->fine_per_day ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Maximum Fine (₱)</label>
            <input type="number" step="0.01" name="max_fine"
                   class="form-control"
                   value="{{ $settings->max_fine ?? '' }}">
        </div>
        
        <div class="mb-3">
            <label>Loan Duration (days)</label>
            <input type="number" name="loan_duration_days"
                   value="{{ $settings->loan_duration_days }}"
                   class="form-control">
        </div>


        <div class="mb-3">
            <label class="form-label">Grace Period (Days)</label>
            <input type="number" name="grace_period_days"
                   class="form-control"
                   value="{{ $settings->grace_period_days ?? 0 }}" required>
        </div>

        <button class="btn btn-primary">Save Fine Policy</button>
    </form>

    @if($settings)
        <p class="text-muted mt-3">
            Effective since: {{ $settings->effective_from }}
        </p>
    @endif
</div>
@endsection
