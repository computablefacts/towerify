<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attacker extends Model
{
    use HasFactory;

    protected $table = 'attackers';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'name',
        'first_contact',
        'last_contact',
    ];
    
    public function events(): HasMany
    {
        return $this->hasMany(HoneypotEvent::class, 'attacker_id', 'id');
    }
}
