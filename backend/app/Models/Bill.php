<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use SoftDeletes;

    protected $fillable = ['uuid', 'customer_name', 'customer_phone', 'staff_id', 'billed_by', 'total', 'status', 'synced_at'];
    protected $casts = ['total' => 'decimal:2', 'synced_at' => 'datetime'];

    public function items(): HasMany { return $this->hasMany(BillItem::class); }
    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function billedBy(): BelongsTo { return $this->belongsTo(User::class, 'billed_by'); }
}
