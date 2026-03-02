<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    // These fields can be saved via form
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'amount',
        'category',
        'description',
        'transaction_date',
    ];

    // Auto-convert these to correct PHP types
    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // Relationship: each transaction belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: scope to only income transactions
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    // Helper: scope to only expense transactions
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }
}