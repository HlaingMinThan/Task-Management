<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the columns for the project.
     */
    public function columns()
    {
        return $this->hasMany(Column::class);
    }

    /**
     * Get the project members.
     */
    public function members()
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * Get the pending project invites.
     */
    public function invites()
    {
        return $this->hasMany(ProjectInvite::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Project $project) {
            $project->columns()->createMany([
                ['title' => 'To Do', 'position' => 1],
                ['title' => 'Doing', 'position' => 2],
                ['title' => 'Done', 'position' => 3],
            ]);
        });
    }
}
