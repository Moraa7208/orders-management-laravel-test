<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Warehouse extends Model
{
        use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get all orders stored in this warehouse.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all products available in this warehouse.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'stocks')
            ->withPivot('stock')
            ->withTimestamps();
    }

        /**
     * Get the stock records for the warehouse.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
