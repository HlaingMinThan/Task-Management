<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'position',
    ];

    /**
     * Get the project that owns the column.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
