<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteTask extends Model
{
    use HasFactory;

    public const UNIT_OPTIONS = [
        'm2' => 'm²',
        'linear_meter' => 'm lineal',
        'unit' => 'unidad',
    ];

    protected $fillable = [
        'description',
        'pricing_mode',
        'quantity',
        'unit',
        'unit_price',
        'labor_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'labor_amount' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
