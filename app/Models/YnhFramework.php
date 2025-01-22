<?php

namespace App\Models;

use App\Modules\CyberBuddy\Models\Collection;
use App\Modules\CyberBuddy\Models\File;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string description
 * @property string locale
 * @property string copyright
 * @property string version
 * @property string provider
 * @property string file
 */
class YnhFramework extends Model
{
    use HasFactory;

    protected $table = 'ynh_frameworks';

    protected $fillable = [
        'name',
        'description',
        'locale',
        'copyright',
        'version',
        'provider',
        'file',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function collectionName(): string
    {
        /** @var Auth $user */
        $user = Auth::user();
        return Str::lower("{$this->provider}-{$user->tenant_id}-{$this->locale}");
    }

    public function collection(): ?Collection
    {
        return Collection::where('is_deleted', false)
            ->where('name', $this->collectionName())
            ->first();
    }

    public function file(): ?File
    {
        $collection = $this->collection();
        return $collection ? File::where('is_deleted', false)
            ->where('collection_id', $collection->id)
            ->where('name', trim(basename($this->file, '.jsonl')))
            ->where('extension', 'jsonl')
            ->first() : null;
    }

    public function loaded(): bool
    {
        return $this->file() !== null;
    }

    public function path(): string
    {
        return database_path($this->file);
    }
}
