<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'price', 'is_active', 'created_by'];
    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
