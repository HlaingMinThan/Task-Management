<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id',
        'project_id',
        'title',
        'description',
        'priority',
        'due_date',
        'position',
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(TaskUser::class);
    }
}
