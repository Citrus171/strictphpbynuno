<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class UdemyProject extends Model
{
    //

    protected $fillable = ['title', 'description', 'due_date'];

    /**
     * Get the tasks for the project.
     */
    public function tasks()
    {
        return $this->hasMany(UdemyTask::class, 'project_id');
    }
}
