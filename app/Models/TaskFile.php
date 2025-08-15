<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TaskFile
 * 
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property string $path
 * @property int $size_bytes
 * @property Carbon $created_at
 * 
 * @property User $user
 * @property Comment $task
 *
 * @package App\Models
 */
class TaskFile extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'path',
        'size_bytes'
    ];

    public $timestamps = true;
    const UPDATED_AT = null;
    protected $casts = ['value'=>'bool'];

    public function uploaded_by()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}