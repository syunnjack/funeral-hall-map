<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostReport extends Model
{
    protected $fillable = [
        'venue_id',
        'funeral_type',
        'attendee_count',
        'total_cost',
        'comment',
        'nickname',
        'ip_hash',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
