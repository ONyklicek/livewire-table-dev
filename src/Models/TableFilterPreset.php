<?php

namespace NyonCode\LivewireTable\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableFilterPreset extends Model
{
    protected $fillable = [
        'user_id',
        'table_name',
        'name',
        'filters',
        'is_default',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
