<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_provider',
        'email_address',
        'credentials',
        'is_active',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
    ];

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }
}
