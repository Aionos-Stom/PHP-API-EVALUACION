<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Currency extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'symbol', 'exchange_rate'];

    protected function casts(): array
    {
        return ['exchange_rate' => 'decimal:6'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
