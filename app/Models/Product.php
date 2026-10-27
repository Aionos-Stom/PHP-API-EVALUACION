<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Product extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'Price', 'currency_id', 'tax_cost', 'manufacturing_cost'];

    protected function casts(): array
    {
        return [
            'Price' => 'decimal:2',
            'tax_cost' => 'decimal:2',
            'manufacturing_cost' => 'decimal:2',
            'currency_id' => 'integer',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
