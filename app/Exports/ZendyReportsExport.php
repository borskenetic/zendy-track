<?php

namespace App\Exports;

use App\Models\ZendyLog;
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
            new ZendyReportDashboardSheet($this->data),
            new ZendyReportSummarySheet($this->data),
            new ZendyReportByCourseSheet($this->data['submissionsByCourse']),
            new ZendyReportByCampusSheet($this->data['submissionsByCampus']),
            new ZendyReportByActionSheet($this->data['submissionsByAction']),
            new ZendyReportOverTimeSheet($this->data['submissionsOverTime']),
            new ZendyReportLogsSheet($this->data['exportLogs']),
        ];
    }
}

class ZendyReportDashboardSheet implements FromCollection, WithTitle
{
    public function __construct(private array $data) {}

    public function collection(): Collection
    {
        $rows = collect();
        $avgDuration = $this->data['avgDuration'];

        $rows->push(['Zendy Usage Report']);
        $rows->push(['Generated At', now()->timezone('Asia/Manila')->format('Y-m-d h:i A')]);
        $rows->push(['']);

        $rows->push(['Summary']);
        $rows->push(['Total Launches', $this->data['totalLaunches']]);
        $rows->push(['Unique Users', $this->data['uniqueUsers']]);
        $rows->push(['Estimated Returns', $this->data['estimatedReturns']]);
        $rows->push(['Average Time Away', $avgDuration ? gmdate('H:i:s', (int) $avgDuration) : '—']);
        $rows->push(['']);

        $rows->push(['Launches by Course']);
        $rows->push(['Course', 'Total']);
        foreach ($this->data['submissionsByCourse'] as $row) {
            $rows->push([$row->course ?? '—', $row->total]);
        }
        $rows->push(['']);

        $rows->push(['Launches by Campus']);
        $rows->push(['Campus', 'Total']);
        foreach ($this->data['submissionsByCampus'] as $row) {
            $rows->push([$row->campus ?? '—', $row->total]);
        }
        $rows->push(['']);

        $rows->push(['By Event Type']);
        $rows->push(['Event Type', 'Total']);
        foreach ($this->data['submissionsByAction'] as $row) {
            $rows->push([ZendyLog::labelForAction($row->action), $row->total]);
        }
        $rows->push(['']);

        $rows->push(['Launches Over Time']);
        $rows->push(['Date', 'Launches']);
        foreach ($this->data['submissionsOverTime'] as $row) {
            $rows->push([$row->date, $row->total]);
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Dashboard';
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
        return ['Course', 'Launches'];
    }

    public function title(): string
    {
        return 'Launches by Course';
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
        return ['Campus', 'Launches'];
    }

    public function title(): string
    {
        return 'Launches by Campus';
    }
}

class ZendyReportByActionSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            'action' => ZendyLog::labelForAction($row->action),
            'total' => $row->total,
        ]);
    }

    public function headings(): array
    {
        return ['Event Type', 'Total'];
    }

    public function title(): string
    {
        return 'By Event Type';
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
        return 'Launches Over Time';
    }
}

class ZendyReportLogsSheet extends ZendyLogsExport implements WithTitle
{
    public function title(): string
    {
        return 'Activity Logs';
    }
}
