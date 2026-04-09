<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function dm(Request $request)
    {
        try {
            $me = $request->user();

            $otherId = $request->input('user_id');
            if (empty($otherId) || !is_numeric($otherId)) {
                return response()->json(['message' => 'Invalid user_id'], 422);
            }

            $otherId = (int) $otherId;
            if ((int)$me->id === $otherId) {
                return response()->json(['message' => 'Cannot chat with yourself'], 422);
            }

            $otherExists = User::where('id', $otherId)->exists();
            if (!$otherExists) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $conversationId = DB::table('conversation_participants as cp')
                ->join('conversations as c', 'c.id', '=', 'cp.conversation_id')
                ->where('c.type', 'dm')
                ->whereIn('cp.user_id', [$me->id, $otherId])
                ->groupBy('cp.conversation_id')
                ->havingRaw('COUNT(*) = 2')
                ->value('cp.conversation_id');

            if ($conversationId) {
                return response()->json(['conversation_id' => (int)$conversationId]);
            }

            $conv = DB::transaction(function () use ($me, $otherId): Conversation {
                $conv = Conversation::create(['type' => 'dm']);

                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $me->id,
                    'last_read_at' => null,
                ]);

                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $otherId,
                    'last_read_at' => null,
                ]);

                return $conv;
            });

            return response()->json(['conversation_id' => (int)$conv->id], 201);            
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('chat dm error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function conversations(Request $request)
    {
        try {
            $me = $request->user();

            $conversations = Conversation::query()
                ->whereHas('participants', fn($q) => $q->where('user_id', $me->id))
                ->where('type', 'dm')
                ->with([
                    'participants.user:id,first_name,last_name,profile_picture',
                    'messages' => fn($q) => $q->latest('id')->limit(1),
                    'messages.sender:id,first_name,last_name,profile_picture',
                ])
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get();

            $out = $conversations->map(function (Conversation $c) use ($me) {
                $other = $c->participants
                    ->map(fn($p) => $p->user)
                    ->first(fn($u) => $u && (string)$u->id !== (string)$me->id);

                $last = $c->messages->first();

                return [
                    'id' => $c->id,
                    'type' => $c->type,
                    'other_user' => $other ? [
                        'id' => $other->id,
                        'first_name' => $other->first_name,
                        'last_name' => $other->last_name,
                        'profile_picture' => $other->profile_picture,
                    ] : null,
                    'last_message' => $last ? [
                        'id' => $last->id,
                        'body' => $last->body,
                        'sender_id' => $last->sender_id,
                        'created_at' => $last->created_at,
                        'delivered_at' => $last->delivered_at,
                        'seen_at' => $last->seen_at,
                    ] : null,
                    'updated_at' => $c->updated_at,
                ];
            });

            return response()->json(['conversations' => $out]);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('chat conversations error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function messages(Request $request, int $conversation_id)
    {
        try {
            $me = $request->user();

            $isParticipant = ConversationParticipant::where('conversation_id', $conversation_id)
                ->where('user_id', $me->id)
                ->exists();

            if (!$isParticipant) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $afterId = $request->query('after_id');
            $afterId = is_numeric($afterId) ? (int)$afterId : null;

            $now = now();

            $deliverQ = Message::where('conversation_id', $conversation_id)
                ->where('sender_id', '!=', $me->id)
                ->whereNull('delivered_at');

            if ($afterId) {
                $deliverQ->where('id', '>', $afterId);
            }

            $deliverQ->update(['delivered_at' => $now]);

            $q = Message::query()
                ->where('conversation_id', $conversation_id)
                ->with(['sender:id,first_name,last_name,profile_picture'])
                ->orderBy('id', 'asc');

            if ($afterId) {
                $q->where('id', '>', $afterId)->limit(200);
            } else {
                $q->limit(50);
            }

            $messages = $q->get()->map(fn(Message $m) => [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'sender_id' => $m->sender_id,
                'body' => $m->body,
                'created_at' => $m->created_at,
                'delivered_at' => $m->delivered_at,
                'seen_at' => $m->seen_at,
                'sender' => $m->sender ? [
                    'id' => $m->sender->id,
                    'first_name' => $m->sender->first_name,
                    'last_name' => $m->sender->last_name,
                    'profile_picture' => $m->sender->profile_picture,
                ] : null,
            ]);

            return response()->json(['messages' => $messages]);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('chat messages error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function send(Request $request, int $conversation_id)
    {
        try {
            $me = $request->user();

            $isParticipant = ConversationParticipant::where('conversation_id', $conversation_id)
                ->where('user_id', $me->id)
                ->exists();

            if (!$isParticipant) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $body = (string) $request->input('body', '');
            $body = trim($body);

            if ($body === '') {
                return response()->json(['message' => 'Empty message'], 422);
            }

            if (mb_strlen($body) > 2000) {
                return response()->json(['message' => 'Message too long'], 422);
            }

            $msg = Message::create([
                'conversation_id' => $conversation_id,
                'sender_id' => $me->id,
                'body' => $body,
                'delivered_at' => null,
                'seen_at' => null,
            ]);

            Conversation::where('id', $conversation_id)->update(['updated_at' => now()]);

            $msg->load(['sender:id,first_name,last_name,profile_picture']);

            return response()->json([
                'message' => [
                    'id' => $msg->id,
                    'conversation_id' => $msg->conversation_id,
                    'sender_id' => $msg->sender_id,
                    'body' => $msg->body,
                    'created_at' => $msg->created_at,
                    'delivered_at' => $msg->delivered_at,
                    'seen_at' => $msg->seen_at,
                    'sender' => $msg->sender ? [
                        'id' => $msg->sender->id,
                        'first_name' => $msg->sender->first_name,
                        'last_name' => $msg->sender->last_name,
                        'profile_picture' => $msg->sender->profile_picture,
                    ] : null,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('chat send error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function seen(Request $request, int $conversation_id)
    {
        try {
            $me = $request->user();

            $isParticipant = ConversationParticipant::where('conversation_id', $conversation_id)
                ->where('user_id', $me->id)
                ->exists();

            if (!$isParticipant) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $now = now();

            Message::where('conversation_id', $conversation_id)
                ->where('sender_id', '!=', $me->id)
                ->whereNull('seen_at')
                ->update(['seen_at' => $now]);

            ConversationParticipant::where('conversation_id', $conversation_id)
                ->where('user_id', $me->id)
                ->update(['last_read_at' => $now]);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('chat seen error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}