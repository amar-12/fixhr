<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'html_type',
        'attributes',
        'requires_options',
        'is_active'
    ];

    protected $casts = [
        'attributes' => 'array',
        'requires_options' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function assetTypeFields(): HasMany
    {
        return $this->hasMany(AssetTypeField::class);
    }

    public function componentFields(): HasMany
    {
        return $this->hasMany(ComponentField::class);
    }

    public function getHtmlAttributes(): string
    {
        if (!$this->attributes) {
            return '';
        }

        $attrs = [];
        foreach ($this->attributes as $key => $value) {
            $attrs[] = $key . '="' . $value . '"';
        }

        return implode(' ', $attrs);
    }
}