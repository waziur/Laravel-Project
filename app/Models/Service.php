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
        'detail_overview',
        'included_services',
        'delivery_steps',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delivery_steps' => 'array',
            'included_services' => 'array',
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

    public function overview(): string
    {
        return $this->detail_overview ?: $this->short_description;
    }

    /**
     * @return array<int, string>
     */
    public function includedServiceList(): array
    {
        return $this->cleanList($this->included_services);
    }

    /**
     * @return array<int, string>
     */
    public function deliveryStepList(): array
    {
        return $this->cleanList($this->delivery_steps);
    }

    /**
     * @param  mixed  $items
     * @return array<int, string>
     */
    private function cleanList(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item): bool => is_string($item) && trim($item) !== '')
            ->map(fn (string $item): string => trim($item))
            ->values()
            ->all();
    }
}
