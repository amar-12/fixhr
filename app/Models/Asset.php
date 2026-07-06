<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_tag',
        'assets_b_id',
        'asset_type_id',
        'serial_number',
        'model_number',
        'specifications',
        'status',
        'issue_by',
        'assigned_by',
        'employee_id',
        'replaced_by',
        'assigned_at',
        'scrapped_at',
        'notes',
        'purchase_value',
        'purchase_date',
        'warranty_months',
        'invoice_no',
        'invoice_date',
        'invoice_file',
        'scrap_value',
        'service_date',
        'amc_expiry_date',
        'service_return_date',
        'service_notes',
        'po_no',
        'vendor_name',
        'scrap_reason',
        'replace_date',
        'scraped_by'

    ];


    protected $casts = [
        'specifications' => 'array',
        'assigned_at' => 'date',
        'scrapped_at' => 'date',
        'purchase_date' => 'date',
        'service_date' => 'date',
        'service_return_date' => 'date',
    ];



    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'replaced_by');
    }

    public function fh_replacedBy(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'replaced_by');
    }


    public function assetType()
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_id');
    }

    public function user()
    {
        return $this->belongsTo(Employee::class, 'issue_by', 'emp_id');
    }

     public function assignedby()
    {
        return $this->belongsTo(Employee::class, 'assigned_by', 'emp_id');
    }



    public function fh_service()
    {
        return $this->hasOne(AssetService::class, 'asset_id', 'id');
    }

    public function fh_scraped()
    {
        return $this->belongsTo(Employee::class, 'scraped_by', 'emp_id');
    }

    public function histories()
    {
        return $this->hasMany(AssetHistory::class, 'asset_id');
    }

    public function scopeStock($query)
    {
        return $query->where('status', 'stock');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeScrap($query)
    {
        return $query->where('status', 'scrap');
    }

    public function scopeReplaced($query)
    {
        return $query->where('status', 'replaced');
    }

    public function scopeService($query)
    {
        return $query->where('status', 'service');
    }


    public function getWarrantyStatusAttribute()
    {
        if (!$this->purchase_date || !$this->warranty_months) {
            return 'Unknown';
        }

        $warrantyEndDate = $this->purchase_date->copy()->addMonths($this->warranty_months);

        if (now()->greaterThan($warrantyEndDate)) {
            return 'Expired';
        } elseif (now()->diffInDays($warrantyEndDate) <= 30) {
            return 'Expiring Soon';
        }

        return 'Active';
    }

    public function getWarrantyEndDateAttribute()
    {
        if (!$this->purchase_date || !$this->warranty_months) {
            return null;
        }

        return $this->purchase_date->copy()->addMonths($this->warranty_months);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_tag)) {
                $asset->asset_tag = static::generateNewFormatAssetTag($asset);
            }
        });
    }


    private static function generateNewFormatAssetTag($asset): string
    {
        // Get asset type for device code
        $assetType = AssetType::find($asset->asset_type_id);
        $device = $assetType ? strtoupper(substr($assetType->name, 0, 3)) : 'GEN';

        // Default division/location
        $division = self::getDivisionCode($asset->division ?? 'LIPL');
        $location = self::getLocationCode($asset->branch ?? 'RAIPUR');
        $year = date('Y');

        $baseTag = "KES/{$division}/{$location}/{$device}/{$year}/";
        $sequence = static::getNextSequence($baseTag);

        return $baseTag . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }


    private static function generateAssetTag(): string
    {
        do {
            $tag = 'AST-' . strtoupper(uniqid());
        } while (static::where('asset_tag', $tag)->exists());

        return $tag;
    }


    public static function getDivisionCode($division): string
    {
        $division = strtoupper($division);

        $divisionCodes = [
            'LIPL' => 'LIPL',
            'AJAX' => 'AJAX',
            'KTPL' => 'KTPL',
            'SNDK' => 'SNDK',
            'FD'   => 'FD',
            'WBCO' => 'WBCO',
        ];

        return $divisionCodes[$division] ?? 'LIPL';
    }

    public static function getLocationCode($branch): string
    {
        $branch = strtoupper($branch);

        $locationCodes = [
            'RAIPUR'     => 'RAI',
            'RAIGARH'    => 'RAG',
            'AMBIKAPUR'  => 'AMB',
            'JAGDALPUR'  => 'JAG',
            'KORBA'      => 'KOR',
            'BILASPUR'   => 'BIL',
        ];

        return $locationCodes[$branch] ?? 'RAI';
    }

    public static function getNextSequence($baseTag): int
    {
        $lastAsset = static::where('asset_tag', 'LIKE', $baseTag . '%')
            ->orderBy('asset_tag', 'desc')
            ->first();

        if (!$lastAsset) {
            return 1;
        }

        // Extract sequence number from last asset tag
        $lastTag = $lastAsset->asset_tag;
        $lastSequence = (int) substr($lastTag, strrpos($lastTag, '/') + 1);

        return $lastSequence + 1;
    }

    public function services()
    {
        return $this->hasMany(AssetService::class);
    }
}
