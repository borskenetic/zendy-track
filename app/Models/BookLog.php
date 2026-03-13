<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\FineSetting;

class BookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'patron_name',
        'status',
        'timestamp',
        'due_date',
        'returned_date',
        'fine_incurred',
    ];

    protected $casts = [
        'timestamp'     => 'datetime',
        'due_date'      => 'date',
        'returned_date' => 'datetime',
        'fine_incurred' => 'decimal:2',
    ];

    protected $appends = [
        'is_overdue',
        'days_overdue',
        'total_fine',
    ];

    /* ==========================
     | Relationships
     ==========================*/
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    /* ==========================
     | Accessors
     ==========================*/

    public function getTimestampManilaAttribute()
    {
        return $this->timestamp
            ? $this->timestamp->timezone('Asia/Manila')->format('Y-m-d h:i A')
            : null;
    }

    /**
     * Is overdue?
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->due_date) return false;

        $compareDate = $this->returned_date
            ? Carbon::parse($this->returned_date)
            : Carbon::now('Asia/Manila');

        return $compareDate->gt($this->due_date);
    }

    /**
     * Days overdue (uses grace period)
     */
    public function getDaysOverdueAttribute()
    {
        $settings = FineSetting::latest('effective_from')->first();

        if (!$this->due_date || !$settings) {
            return 0;
        }

        $compareDate = $this->returned_date
            ? Carbon::parse($this->returned_date)
            : Carbon::now('Asia/Manila');

        $graceEnd = Carbon::parse($this->due_date)
            ->addDays($settings->grace_period_days);

        if ($compareDate->lte($graceEnd)) {
            return 0;
        }

        return $graceEnd->diffInDays($compareDate);
    }

    /**
     * TOTAL FINE (THIS IS THE KEY FIX)
     *
     * - If returned → use fine_incurred (frozen)
     * - If not returned → compute live (preview)
     */
    public function getTotalFineAttribute()
    {
        // ✅ BOOK ALREADY RETURNED → TRUST DATABASE
        if ($this->returned_date) {
            return (float) $this->fine_incurred;
        }

        // ⏳ NOT YET RETURNED → COMPUTE LIVE
        $settings = FineSetting::latest('effective_from')->first();

        if (!$settings || $this->days_overdue === 0) {
            return 0;
        }

        $fine = $this->days_overdue * $settings->fine_per_day;

        if (!is_null($settings->max_fine)) {
            $fine = min($fine, $settings->max_fine);
        }

        return round($fine, 2);
    }
    


}
