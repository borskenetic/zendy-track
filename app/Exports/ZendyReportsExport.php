<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ZendyReportsExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        return [
            new ZendyReportSummarySheet($this->data),
            new ZendyReportByCourseSheet($this->data['submissionsByCourse']),
            new ZendyReportByCampusSheet($this->data['submissionsByCampus']),
            new ZendyReportByActionSheet($this->data['submissionsByAction']),
            new ZendyReportOverTimeSheet($this->data['submissionsOverTime']),
        ];
    }
}

class ZendyReportSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private array $data) {}

    public function collection(): Collection
    {
        $avgDuration = $this->data['avgDuration'];

        return collect([
            ['Total Launches', $this->data['totalLaunches']],
            ['Unique Users', $this->data['uniqueUsers']],
            ['Estimated Returns', $this->data['estimatedReturns']],
            ['Average Time Away', $avgDuration ? gmdate('H:i:s', (int) $avgDuration) : '—'],
            ['Generated At', now()->timezone('Asia/Manila')->format('Y-m-d h:i A')],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

class ZendyReportByCourseSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            'course' => $row->course ?? '—',
            'total' => $row->total,
        ]);
    }

    public function headings(): array
    {
        return ['Course', 'Total'];
    }

    public function title(): string
    {
        return 'By Course';
    }
}

class ZendyReportByCampusSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            'campus' => $row->campus ?? '—',
            'total' => $row->total,
        ]);
    }

    public function headings(): array
    {
        return ['Campus', 'Total'];
    }

    public function title(): string
    {
        return 'By Campus';
    }
}

class ZendyReportByActionSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            'action' => str_replace('_', ' ', ucfirst((string) $row->action)),
            'total' => $row->total,
        ]);
    }

    public function headings(): array
    {
        return ['Event Type', 'Total'];
    }

    public function title(): string
    {
        return 'By Event';
    }
}

class ZendyReportOverTimeSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            'date' => $row->date,
            'total' => $row->total,
        ]);
    }

    public function headings(): array
    {
        return ['Date', 'Launches'];
    }

    public function title(): string
    {
        return 'Over Time';
    }
}
