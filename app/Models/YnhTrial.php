<?php

namespace App\Models;

use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string hash
 * @property ?string domain
 * @property ?array subdomains
 * @property boolean honeypots
 * @property ?string email
 */
class YnhTrial extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'hash',
        'domain',
        'subdomains',
        'honeypots',
        'email',
    ];

    protected $casts = [
        'subdomains' => 'array',
        'honeypots' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
