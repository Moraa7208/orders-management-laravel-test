<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Order extends Model
{   
     use HasFactory;

    public $timestamps = false; 

    protected $fillable = [
        'customer',
        'status',
        'created_at',
        'completed_at',
        'warehouse_id',
    ];

    protected $casts = [
    'created_at' => 'datetime',
    'completed_at' => 'datetime',
    ];
 

    /**
    * Get the warehouse where this order is stored.
    */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }


    /**
     * Get all products included in this order.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('count')
            ->withTimestamps();
    }

        /**
     * Get the items (product and count) included in this order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
