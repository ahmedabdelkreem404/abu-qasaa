<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCollectionItem extends Model
{
    protected $fillable = ['product_collection_id', 'product_id', 'sort_order', 'is_featured'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_featured' => 'boolean'];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(ProductCollection::class, 'product_collection_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
