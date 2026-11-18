<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const METHODS = [
        'transfer' => 'Transferencia',
        'cash' => 'Efectivo',
        'deposit' => 'Depósito',
        'card' => 'Tarjeta',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'quote_id',
        'amount',
        'payment_date',
        'method',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
