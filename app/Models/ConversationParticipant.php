<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ConversationParticipant
 * 
 * @property Conversation $conversation
 * @property User $user
 *
 * @package App\Models
 */
class ConversationParticipant extends Model
{
    protected $table = 'conversation_participants';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_read_at',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}