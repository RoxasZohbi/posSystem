<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    protected $fillable = ['bill_id', 'type', 'service_id', 'deal_id', 'name', 'price'];
    protected $casts = ['price' => 'decimal:2'];

    public function bill(): BelongsTo { return $this->belongsTo(Bill::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function deal(): BelongsTo { return $this->belongsTo(Deal::class); }
}
