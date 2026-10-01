<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    protected $fillable = [
        'is_published',
        'meta_description',
        'title',
        'banner_text',
        'banner_image',
        'intro_heading',
        'intro_body',
        'intro_image',
        'highlights',
        'team',
        'reviews',
        'faqs',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'highlights' => 'array',
        'team' => 'array',
        'reviews' => 'array',
        'faqs' => 'array',
    ];

    public static function current(): self
    {
        return static::firstOrNew();
    }
}
