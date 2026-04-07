<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UdemyProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

final class UdemyProject extends Model
{
    /** @use HasFactory<UdemyProjectFactory> */
    use HasFactory;

    #[Override]
    protected $fillable = ['title', 'description', 'due_date'];

    /** @return HasMany<UdemyTask, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(UdemyTask::class, 'project_id');
    }
}
