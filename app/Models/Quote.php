<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending' => 'En espera',
        'accepted' => 'Aceptado',
        'rejected' => 'Rechazado',
        'expired' => 'Vencido',
    ];

    protected $fillable = [
        'client_id',
        'project_name',
        'issue_date',
        'expiry_date',
        'description',
        'labor_amount',
        'materials_amount',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'labor_amount' => 'decimal:2',
            'materials_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Quote $quote): void {
            $quote->total_amount = (float) $quote->labor_amount + (float) ($quote->materials_amount ?? 0);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(QuoteTask::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
