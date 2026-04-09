<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Conversation
 * 
 * @property Collection|ConversationParticipant[] $conversation_participants
 * @property Collection|Message[] $messages
 *
 * @package App\Models
 */
class Conversation extends Model
{
    protected $table = 'conversations';

    protected $fillable = [
        'type',
    ];

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
}