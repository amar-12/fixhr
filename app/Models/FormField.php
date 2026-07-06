<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormField extends Model
{
    use HasFactory;
    protected $fillable = ['section_id', 'field_name', 'field_type', 'placeholder','colume_name', 'is_required'];

}
