<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'handled_at',
        'admin_notes',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('handled_at');
    }

    public function isHandled(): bool
    {
        return $this->handled_at !== null;
    }
}
