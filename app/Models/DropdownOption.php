<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'value',
        'label',
        'metadata',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];

    public static function getOptionsForCategory(string $category): array
    {
        return static::where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->pluck('label', 'value')
            ->toArray();
    }

    public static function getCategorizedOptions(string $category): array
    {
        return static::where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(function ($option) {
                return [
                    'value' => $option->value,
                    'label' => $option->label,
                    'metadata' => $option->metadata
                ];
            })
            ->toArray();
    }

    public static function getCategories(): array
    {
        return static::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }
}