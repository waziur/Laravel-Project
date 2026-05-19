<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'title',
        'image_url',
        'short_description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function imageSrc(): string
    {
        if (Str::startsWith($this->image_url, ['http://', 'https://', '//'])) {
            return $this->image_url;
        }

        return asset($this->image_url ?: 'img/feature.jpg');
    }
}
