<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id',
        'project_id',
        'title',
        'description',
        'priority',
        'assigned_user_id',
        'due_date',
        'position',
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Scope a query to search title/description.
     */
    public function scopeSearch($query, $term)
    {
        if (! $term) {
            return $query;
        }

        $like = "%{$term}%";

        return $query->where(function ($q) use ($like) {
            $q->where('title', 'like', $like)
              ->orWhere('description', 'like', $like);
        });
    }

    public function scopeAssignedTo($query, $userId)
    {
        if (! isset($userId)) {
            return $query;
        }

        return $query->where('assigned_user_id', $userId);
    }

    public function scopeDueBetween($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('due_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('due_date', '<=', $to);
        }

        return $query;
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now())
            ->whereHas('column', function ($q) {
                $q->where('title', '!=', 'Done');
            });
    }
}
