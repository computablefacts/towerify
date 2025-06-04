<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string key
 * @property ?string value
 * @property boolean is_encrypted
 */
class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->is_encrypted ? decrypt($value) : $value,
            set: fn(string $value) => $this->is_encrypted ? encrypt($value) : $value,
        );
    }
}