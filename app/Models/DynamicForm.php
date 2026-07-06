<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicForm extends Model
{
    use HasFactory;

    protected $fillable = ['form_name'];

    public function sections()
    {
        return $this->hasMany(FormSection::class);
    }
}
