<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string thread_id
 * @property string dom
 * @property boolean autosaved
 * @property int created_by
 * @property int format
 * @property ?string description
 */
class Conversation extends Model
{
    use HasFactory, HasTenant;

    const int FORMAT_V0 = 0; // dom contains DOM
    const int FORMAT_V1 = 1; // dom contains JSON

    protected $table = 'cb_conversations';

    protected $fillable = [
        'thread_id',
        'dom',
        'autosaved',
        'created_by',
        'format',
        'description',
    ];

    protected $casts = [
        'autosaved' => 'boolean',
        'format' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function thread(): string|array
    {
        return match ($this->format) {
            self::FORMAT_V1 => json_decode($this->dom, true),
            default => $this->dom,
        };
    }

    public function lightThread(): string|array
    {
        return match ($this->format) {
            self::FORMAT_V1 => collect(json_decode($this->dom, true))
                ->filter(fn(array $message) => $message['role'] === RoleEnum::USER->value || $message['role'] === RoleEnum::ASSISTANT->value)
                ->values()
                ->toArray(),
            default => $this->dom,
        };
    }
}
