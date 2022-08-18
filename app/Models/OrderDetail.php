<?php
namespace App\Models;


use App\Enums\EStatus;

class OrderDetail extends BaseModel {
    protected $table = 'order_detail';

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
 }
