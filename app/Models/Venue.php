<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $fillable = [
        'name',
        'description',
        'facility_type',
        'area',
        'address',
        'phone',
        'website',
        'opening_hours',
        'lat',
        'lng',
        'likes_count',
        'source',
        'source_ref',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function costReports()
    {
        return $this->hasMany(CostReport::class);
    }
}
