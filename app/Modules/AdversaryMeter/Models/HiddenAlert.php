<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HiddenAlert extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'hidden_alerts';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'uid',
        'type',
        'title',
    ];
}
