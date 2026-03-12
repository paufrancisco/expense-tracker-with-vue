<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /** @var array */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'amount',
        'category',
        'description',
        'transaction_date',
    ];

    /** @var array */
    protected $casts = [
        'transaction_date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
}