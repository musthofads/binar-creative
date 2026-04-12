<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoboothSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'customer_name',
        'package_type',
        'qr_code_path',
        'qr_code_url',
        'user_id',
        'photo_count',
        'strip_generated',
        'strip_path',
        'metadata',
        'last_activity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'strip_generated' => 'boolean',
            'photo_count' => 'integer',
            'last_activity' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SinglePhoto::class, 'session_id', 'session_id');
    }

    public function stripPhotos(): HasMany
    {
        return $this->hasMany(Photo::class, 'session_id', 'session_id');
    }
}
