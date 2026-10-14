<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'page_type', 'status', 'translations', 'blocks'];

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'blocks' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function translation(string $locale): array
    {
        return $this->translations[$locale]
            ?? $this->translations['en']
            ?? [];
    }
}
