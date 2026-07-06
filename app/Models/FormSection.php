<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSection extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'section_name'];

    public function fields()
    {
        return $this->hasMany(FormField::class, 'section_id');
    }
}
