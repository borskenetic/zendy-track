@extends('layouts.zen')

@section('page_title', 'Reports')
@section('page_subtitle', 'Usage analytics and trends')

@section('content')
@php
    $excludedCourses = collect(request('exclude_courses', []))
        ->filter(fn ($course) => is_string($course) && trim($course) !== '')
        ->map(fn ($course) => trim($course))
        ->values();
    $excludedLabels = $excludedCourses
        ->map(fn ($course) => $course === '__none__' ? 'No Course' : $course)
        ->all();
@endphp
<form action="{{ route('zendy.reports') }}" method="GET" class="card-surface filter-card" style="margin-bottom: 20px;">
    <div class="filter-bar">
        <input type="date" name="from_date" class="form-control-app" value="{{ request('from_date') }}">
        <input type="date" name="to_date" class="form-control-app" value="{{ request('to_date') }}">
        <select name="search_campus" id="filter_campus" class="form-control-app">
            <option value="">All campuses</option>
            @foreach(\App\Models\User::campusOptions() as $campus)
                <option value="{{ $campus }}" {{ request('search_campus') === $campus ? 'selected' : '' }}>{{ $campus }}</option>
            @endforeach
        </select>
        <select name="search_grade" id="filter_grade" class="form-control-app">
            <option value="">All year levels</option>
        </select>
        <select name="search_course" id="filter_course" class="form-control-app">
            <option value="">All courses</option>
        </select>
        <div class="multi-select" id="excludeCourses">
            <button type="button" class="form-control-app multi-select-toggle" aria-haspopup="listbox" aria-expanded="false">
                <span class="multi-select-label">Exclude courses</span>
                <span aria-hidden="true">▾</span>
            </button>
            <div class="multi-select-menu" hidden>
                <input type="search" class="multi-select-search" placeholder="Search courses" aria-label="Search courses" autocomplete="off">
                <div class="multi-select-actions">
                    <button type="button" class="multi-select-clear">Clear</button>
                </div>
                <div class="multi-select-list">
                    @foreach($courseChoices as $course)
                        <label>
                            <input type="checkbox" name="exclude_courses[]" value="{{ $course }}" data-label="{{ $course }}" @checked($excludedCourses->contains($course))>
                            <span>{{ $course }}</span>
                        </label>
                    @endforeach
                    <label>
                        <input type="checkbox" name="exclude_courses[]" value="__none__" data-label="No Course" @checked($excludedCourses->contains('__none__'))>
                        <span>No Course</span>
                    </label>
                </div>
            </div>
        </div>
        <button type="submit" class="btn-app btn-primary-app">Filter</button>
        <a href="{{ route('zendy.reports') }}" class="btn-app btn-outline-app">Reset</a>
        <a href="{{ route('zendy.reports.export', request()->query()) }}" class="btn-app btn-outline-app">Download Excel</a>
    </div>
    <p class="form-hint">Checked courses are left out of the summary numbers and every chart. Click Filter to apply.</p>
    @if($excludedLabels !== [])
        <p class="form-hint">Currently excluding {{ implode(', ', $excludedLabels) }}.</p>
    @endif
</form>

<div class="card-grid">
    <div class="stat-card">
        <div class="stat-label">Total Launches</div>
        <div class="stat-value">{{ number_format($totalLaunches) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Unique Users</div>
        <div class="stat-value">{{ number_format($uniqueUsers) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Estimated Returns</div>
        <div class="stat-value">{{ number_format($estimatedReturns) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Avg. Time Away</div>
        <div class="stat-value" style="font-size: 1.4rem;">
            @if($avgDuration)
                {{ gmdate('H:i:s', (int) $avgDuration) }}
            @else
                —
            @endif
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 20px;">
    <div class="chart-card">
        <h4>Launches by Course</h4>
        <canvas id="courseChart" height="200"></canvas>
    </div>
    <div class="chart-card">
        <h4>Launches by Campus</h4>
        <canvas id="campusChart" height="200"></canvas>
    </div>
    <div class="chart-card">
        <h4>By Event Type</h4>
        <canvas id="actionChart" height="200"></canvas>
    </div>
</div>

<div class="chart-card">
    <h4>Launches Over Time</h4>
    <canvas id="timeChart" height="100"></canvas>
</div>
@endsection

@push('styles')
<style>
    .multi-select {
        position: relative;
        flex: 0 0 220px;
        min-width: 220px;
    }
    .multi-select-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        text-align: left;
        cursor: pointer;
        font: inherit;
    }
    .multi-select-menu[hidden] {
        display: none !important;
    }
    .multi-select-menu {
        position: absolute;
        z-index: 40;
        top: calc(100% + 6px);
        left: 0;
        display: flex;
        flex-direction: column;
        width: 280px;
        max-height: 340px;
        padding: 8px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16);
    }
    .multi-select-search {
        width: 100%;
        margin-bottom: 6px;
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.85rem;
    }
    .multi-select-actions {
        display: flex;
        justify-content: flex-end;
        padding: 0 2px 6px;
    }
    .multi-select-clear {
        border: none;
        background: transparent;
        color: #2563eb;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        padding: 2px 4px;
    }
    .multi-select-list {
        display: flex;
        flex-direction: column;
        gap: 2px;
        overflow: auto;
    }
    .multi-select-list label {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        margin: 0;
        padding: 7px 8px;
        border-radius: 8px;
        font-size: 0.88rem;
        line-height: 1.3;
        cursor: pointer;
        white-space: nowrap;
    }
    .multi-select-list label:hover {
        background: #f1f5f9;
    }
    .multi-select-list label.is-hidden {
        display: none;
    }
    @media (max-width: 576px) {
        .multi-select { flex: 1 1 100%; width: 100%; }
        .multi-select-menu { width: 100%; }
    }
</style>
@endpush

@section('footer')
@include('partials.affiliation-dropdowns-boot')
<script>
initAffiliationFilters({
    campusId: 'filter_campus',
    yearId: 'filter_grade',
    courseId: 'filter_course',
    initialCampus: @json(request('search_campus', '')),
    initialYear: @json(request('search_grade', '')),
    initialCourse: @json(request('search_course', '')),
});

(function () {
    const root = document.getElementById('excludeCourses');
    if (!root) return;

    const toggle = root.querySelector('.multi-select-toggle');
    const menu = root.querySelector('.multi-select-menu');
    const label = root.querySelector('.multi-select-label');
    const search = root.querySelector('.multi-select-search');
    const boxes = () => Array.from(root.querySelectorAll('input[type="checkbox"]'));

    function syncLabel() {
        const checked = boxes().filter((box) => box.checked).map((box) => box.dataset.label);
        if (checked.length === 0) {
            label.textContent = 'Exclude courses';
        } else if (checked.length <= 2) {
            label.textContent = 'Excluding ' + checked.join(', ');
        } else {
            label.textContent = 'Excluding ' + checked.length + ' courses';
        }
    }

    function setOpen(open) {
        if (open) menu.removeAttribute('hidden');
        else menu.setAttribute('hidden', '');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    toggle.addEventListener('click', () => {
        const open = menu.hasAttribute('hidden');
        setOpen(open);
        if (open) search.focus();
    });
    search.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        root.querySelectorAll('.multi-select-list label').forEach((row) => {
            const text = row.textContent.trim().toLowerCase();
            row.classList.toggle('is-hidden', query !== '' && !text.includes(query));
        });
    });
    search.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') event.preventDefault();
    });
    root.querySelector('.multi-select-clear').addEventListener('click', () => {
        boxes().forEach((box) => { box.checked = false; });
        syncLabel();
    });
    boxes().forEach((box) => box.addEventListener('change', syncLabel));
    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
    syncLabel();
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartColors = ['#2563eb', '#7c3aed', '#16a34a', '#d97706', '#dc2626', '#0891b2'];

    new Chart(document.getElementById('courseChart'), {
        type: 'bar',
        data: {
            labels: @json($submissionsByCourse->pluck('course')),
            datasets: [{
                data: @json($submissionsByCourse->pluck('total')),
                backgroundColor: chartColors,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('campusChart'), {
        type: 'doughnut',
        data: {
            labels: @json($submissionsByCampus->pluck('campus')),
            datasets: [{ data: @json($submissionsByCampus->pluck('total')), backgroundColor: chartColors }]
        },
        options: { responsive: true }
    });

    new Chart(document.getElementById('actionChart'), {
        type: 'bar',
        data: {
            labels: @json($submissionsByAction->map(fn ($row) => \App\Models\ZendyLog::labelForAction($row->action))->values()),
            datasets: [{
                data: @json($submissionsByAction->pluck('total')),
                backgroundColor: chartColors,
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('timeChart'), {
        type: 'line',
        data: {
            labels: @json($submissionsOverTime->pluck('date')),
            datasets: [{
                label: 'Launches',
                data: @json($submissionsOverTime->pluck('total')),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>
@endsection
