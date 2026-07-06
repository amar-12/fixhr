<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UniformItem extends Model
{
    use HasFactory;

    protected $table = 'uniform_items';
    protected $primaryKey = 'uit_id';

    // Add new fields to fillable
    protected $fillable = [
        'uit_emp_id',
        'uit_b_id',
        'uit_issue_id',
        'uit_material_id',
        'uit_price',
        'uit_quantity',
        'uit_payable',
        'uit_discount_type',
        'uit_discount_value',
        'uit_total_price',
        'uit_note',
        'uit_issues_date',
    ];
    public function fh_stock()
    {
        return $this->belongsTo(KitStock::class, 'uit_material_id', 'id');
    }

    public function outfit()
    {
        return $this->belongsTo(MasterTable::class, 'uit_description_id', 'm_id');
    }

    public function size()
    {
        return $this->belongsTo(MasterTable::class, 'uit_size', 'm_id');
    }
}
