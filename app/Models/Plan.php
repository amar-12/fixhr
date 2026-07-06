<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    use SoftDeletes;
    protected $table = 'plans';
    protected $primaryKey = 'plan_id';
    protected $fillable = [
        'name',
        'code',
        'description',
        'included_modules',
        'sort_order',
        'is_active',
        'billing_cycle',
    ];
    protected $casts = [
        'included_modules' => 'array',
        'is_active' => 'boolean',
    ];
    public function priceSlabs(): HasMany
    {
        return $this->hasMany(PlanPriceSlab::class, 'plan_id', 'plan_id');
    }
    public function addons(): HasMany
    {
        return $this->hasMany(PlanAddon::class, 'plan_id', 'plan_id');
    }
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id', 'plan_id');
    }
    public function getPriceForEmployees(int $count): float
    {
        $slab = $this->priceSlabs()
            ->where(function ($query) use ($count) {
                $query->whereNull('min_employees')->orWhere('min_employees', '<=', $count);
            })
            ->where(function ($query) use ($count) {
                $query->whereNull('max_employees')->orWhere('max_employees', '>=', $count);
            })
            ->orderBy('min_employees', 'desc')
            ->first();
        return $slab ? $slab->price_per_user : 0.0;
    }

    public function webMenus(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Menu::class,
            'plan_web_menu',           // pivot table
            'plan_id',
            'menu_id'
        )
        ->select('menus.*')           // Explicitly select from menus only
        ->where('menus.menu_status', 1)
        ->orderBy('menus.menu_sequence', 'ASC');
    }

    /**
     * App Menus allowed for this plan
     */
    public function appMenus(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\AppMenu::class,
            'plan_app_menu',
            'plan_id',
            'menu_id'
        )
        ->select('app_menus.*')       // Explicitly select from app menus table
        ->where('app_menus.menu_status', true)
        ->orderBy('app_menus.menu_sequence', 'ASC');
    }

    // Optional: keep your helper for groups
    public function getMenuGroupsAttribute()
    {
        return $this->webMenus()
            ->distinct()
            ->pluck('menu_group')
            ->toArray();
    }


    
}