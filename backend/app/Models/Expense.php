<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = ['date', 'amount', 'note', 'logged_by'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];

    public function loggedBy(): BelongsTo { return $this->belongsTo(User::class, 'logged_by'); }
}
