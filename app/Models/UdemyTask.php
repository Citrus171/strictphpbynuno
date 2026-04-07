<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UdemyTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

final class UdemyTask extends Model
{
    /** @use HasFactory<UdemyTaskFactory> */
    use HasFactory;

    #[Override]
    protected $fillable = ['title', 'description', 'due_date', 'status', 'project_id'];

    /** @return BelongsTo<UdemyProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(UdemyProject::class, 'project_id');
    }
}
