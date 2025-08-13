<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 * 
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property string $comment
 * @property Carbon $created_at
 * 
 * @property User $user
 * @property Task $task
 *
 * @package App\Models
 */
class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'task_id',
        'comment',
    ];

    public $timestamps = true;
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function reactions()
    { 
        return $this->hasMany(CommentReaction::class); 
    }
}