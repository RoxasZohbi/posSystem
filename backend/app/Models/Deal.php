<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deal extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'price', 'is_active', 'created_by'];
    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function services(): BelongsToMany { return $this->belongsToMany(Service::class, 'deal_service'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
