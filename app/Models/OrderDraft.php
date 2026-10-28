<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDraft extends Model
{
    protected $table = 'order_drafts';

    protected $fillable = [
        'sender_phone',
        'sender_name',
        'items',
        'total',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Cek apakah draft masih aktif dan belum kedaluwarsa.
     */
    public function isValid(): bool
    {
        return $this->status === 'menunggu_konfirmasi' && now()->lessThanOrEqualTo($this->expires_at);
    }
}
