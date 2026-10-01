<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'trip',
        'name',
        'email',
        'phone',
        'travellers',
        'departure_date',
        'notes',
        'status',
    ];

    protected $attributes = [
        'status' => 'new',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'status' => BookingStatus::class,
    ];
}
