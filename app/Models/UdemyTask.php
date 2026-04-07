<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

final class UdemyTask extends Model
{
    use HasFactory;

    //

    #[Override]
    protected $fillable = ['title', 'description', 'due_date', 'status', 'project_id'];

    /**
     * Get the project that owns the task.
     */
    public function project()
    {
        return $this->belongsTo(UdemyProject::class, 'project_id');
    }
}
