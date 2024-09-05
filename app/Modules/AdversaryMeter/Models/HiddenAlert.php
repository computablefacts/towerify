<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HiddenAlert extends Model
{
    use HasFactory;

    protected $table = 'hidden_alerts';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'uid',
        'type',
        'title',
    ];
}
