<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoPrice extends Model {
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function crypto(): BelongsTo {
        return $this->belongsTo(Crypto::class);
    }
}
