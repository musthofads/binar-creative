<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SinglePhoto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'url',
        'user_id',
        'session_id',
        'storage_path',
        'filename',
        'thumbnail_path',
        'package_id',
        'paid',
        'amount_paid',
        'metadata',
        'queue_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid' => 'boolean',
            'amount_paid' => 'decimal:2',
            'metadata' => 'array',
            'queue_number' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the single photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
