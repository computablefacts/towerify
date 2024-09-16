<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property ?string uid
 * @property ?string type
 * @property ?string title
 * @property int created_by
 */
class HiddenAlert extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'am_hidden_alerts';

    protected $fillable = [
        'uid',
        'type',
        'title',
        'created_by',
    ];
}
