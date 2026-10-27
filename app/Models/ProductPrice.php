<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductPrice extends Model
{
    public $timestamps = false;

    protected $fillable = ['currency_id', 'price'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'product_id' => 'integer', 'currency_id' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
