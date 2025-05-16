<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'stock',
    ];
    protected $casts = [
        'stock' => 'integer',
    ];

    public $timestamps = false;



    /**
     * Disable auto-incrementing of IDs (since we are using composite keys)
     */
    public $incrementing = false;

    /**
     * Set composite primary key (product_id and warehouse_id)
     */
    protected $primaryKey = ['product_id', 'warehouse_id'];

    /**
     * Get the product related to this stock record
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse related to this stock record
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

        /**
     * Set the keys for a save update query.
     * Override this method to handle composite primary keys.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('product_id', $this->product_id)
                     ->where('warehouse_id', $this->warehouse_id);
    }
}

