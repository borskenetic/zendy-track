<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ZendyLogsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Collection $logs,
        protected bool $simple = false,
    ) {}

    public function collection(): Collection
    {
        return $this->logs->map(function ($log) {
            if ($this->simple) {
                return [
                    'event' => $log->actionLabel(),
                    'date_time' => $log->created_at?->timezone('Asia/Manila')->format('Y-m-d h:i A') ?? '—',
                    'duration' => $log->durationLabel() ?? '—',
                ];
            }

            $actorName = trim(implode(' ', array_filter([
                optional($log->actor)->fname,
                optional($log->actor)->lname,
            ])));

            return [
                'id' => $log->id,
                'actor' => $actorName !== '' ? $actorName : ($log->email ?? '—'),
                'role' => $log->actor_role ?? optional($log->actor)->role ?? '—',
                'action' => $log->actionLabel(),
                'name' => trim(($log->first_name ?? '').' '.($log->last_name ?? '')) ?: '—',
                'email' => $log->email ?? '—',
                'course' => $log->course ?? '—',
                'department' => $log->department ?? '—',
                'campus' => $log->campus ?? '—',
                'duration' => $log->durationLabel() ?? '—',
                'ip_address' => $log->ip_address ?? '—',
                'time' => $log->created_at?->timezone('Asia/Manila')->format('Y-m-d H:i') ?? '—',
            ];
        });
    }

    public function headings(): array
    {
        if ($this->simple) {
            return ['Event', 'Date & Time', 'Duration'];
        }

        return [
            'ID',
            'Actor',
            'Role',
            'Action',
            'Name',
            'Email',
            'Course',
            'Department',
            'Campus',
            'Duration',
            'IP Address',
            'Time',
        ];
    }
}
