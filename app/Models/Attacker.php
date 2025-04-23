<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property Carbon first_contact
 * @property Carbon last_contact
 */
class Attacker extends Model
{
    use HasFactory;

    protected $table = 'am_attackers';

    protected $fillable = [
        'name',
        'first_contact',
        'last_contact',
    ];

    protected $casts = [
        'first_contact' => 'datetime',
        'last_contact' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(HoneypotEvent::class, 'attacker_id', 'id');
    }

    public function humans(): HasMany
    {
        return $this->events()->where('human', true);
    }

    public function targeted(): HasMany
    {
        return $this->events()->where('targeted', true);
    }

    public function ips(): Collection
    {
        return HoneypotEvent::where('attacker_id', $this->id)
            ->get()
            ->pluck('ip')
            ->unique()
            ->sort()
            ->values();
    }

    public function tools(): Collection
    {
        return $this->events()
            ->where('event', 'tool_detected')
            ->get()
            ->pluck('details')
            ->unique()
            ->sort()
            ->values();
    }

    public function cves(): Collection
    {
        return $this->events()
            ->where('event', 'cve_tested')
            ->get()
            ->pluck('details')
            ->unique()
            ->sort()
            ->values();
    }

    public function aggressiveness(?int $totalNumberOfEvents = null): string
    {
        if ($totalNumberOfEvents == null) {
            $totalNumberOfEvents = HoneypotEvent::count();
        }
        $numberOfEvents = HoneypotEvent::where('attacker_id', $this->id)->count();
        $ratio = $numberOfEvents / $totalNumberOfEvents * 100;
        if ($ratio <= 33) {
            return 'low';
        }
        if ($ratio <= 66) {
            return 'medium';
        }
        return 'high';
    }
}
