<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class UdemyTask extends Model
{
    //

    protected $fillable = ['title', 'description', 'due_date', 'status', 'project_id'];

    /**
     * Get the project that owns the task.
     */
    public function project()
    {
        return $this->belongsTo(UdemyProject::class, 'project_id');
    }
}
