<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AssetTypeField extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_type_id',
        'name',
        'slug',
        'field_type_id',
        'options_category',
        'dropdown_options',
        'validation_rules',
        'attributes',
        'condition_field_slug',
        'condition_operator',
        'condition_value',
        'sort_order',
        'is_required',
        'is_active'
    ];

    protected $casts = [
        'dropdown_options' => 'array',
        'validation_rules' => 'array',
        'attributes' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($field) {
            if (empty($field->slug)) {
                $field->slug = Str::slug($field->name);
            }
        });
    }

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    public function fieldType(): BelongsTo
    {
        return $this->belongsTo(FieldType::class);
    }

    public function getOptions(): array
    {
        if ($this->options_category) {
            return DropdownOption::getOptionsForCategory($this->options_category);
        }

        return $this->dropdown_options ?? [];
    }

    public function getValidationRulesString(): string
    {
        if (!$this->validation_rules) {
            return $this->is_required ? 'required' : '';
        }

        $rules = $this->validation_rules;
        if ($this->is_required && !in_array('required', $rules)) {
            array_unshift($rules, 'required');
        }

        return implode('|', $rules);
    }

    public function getHtmlAttributes(): string
    {
        $attrs = $this->attributes ?? [];
        
        if ($this->is_required) {
            $attrs['required'] = 'required';
        }

        $htmlAttrs = [];
        foreach ($attrs as $key => $value) {
            $htmlAttrs[] = $key . '="' . $value . '"';
        }

        return implode(' ', $htmlAttrs);
    }

    public function hasCondition(): bool
    {
        return !empty($this->condition_field_slug) && 
               !empty($this->condition_operator) && 
               !empty($this->condition_value);
    }

    public function getConditionData(): array
    {
        if (!$this->hasCondition()) {
            return [];
        }

        return [
            'field' => $this->condition_field_slug,
            'operator' => $this->condition_operator,
            'value' => $this->condition_value
        ];
    }
}