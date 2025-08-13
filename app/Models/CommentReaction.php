<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommentReaction
 * 
 * @property int $id
 * @property int $user_id
 * @property int $comment_id
 * @property bool $value
 * @property Carbon $created_at
 * 
 * @property User $user
 * @property Comment $task
 *
 * @package App\Models
 */
class CommentReaction extends Model
{
    protected $fillable = [
        'user_id',
        'comment_id',
        'value',
    ];

    public $timestamps = true;
    const UPDATED_AT = null;
    protected $casts = ['value'=>'bool'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}