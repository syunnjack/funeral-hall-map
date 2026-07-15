<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'line_user_id',
        'venue_id',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
        ];
    }

    public function lineUser()
    {
        return $this->belongsTo(LineUser::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
