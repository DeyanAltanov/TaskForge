<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function comments(Request $request)
    {
        try {
            $taskId = $request->query('task_id');
            $userId = $request->query('user_id');
            $authId = $request->user()->id;

            if ($taskId) {
                $query = Comment::query()
                    ->where('task_id', $taskId)
                    ->with(['user:id,first_name,last_name,profile_picture'])
                    ->withCount([
                        'reactions as likes_count'    => fn($query)=>$query->where('value',1),
                        'reactions as dislikes_count' => fn($query)=>$query->where('value',0),
                    ])
                    ->with(['reactions' => fn($query)=>$query->select('id','comment_id','value','user_id')
                        ->where('user_id',$authId)]);

                $page = (int)($request->query('page') ?? 1);
                $result = $query->orderByDesc('id')->paginate(10, ['*'], 'page', $page);

                $result->getCollection()->transform(function($comments){
                    $comments->my_reaction = optional($comments->reactions->first())->value;
                    unset($comments->reactions);
                    return $comments;
                });

                return response()->json($result);
            }

            if ($userId) {
                $page = (int)($request->query('page') ?? 1);

                $comments = Comment::query()
                    ->where('user_id', $userId)
                    ->with(['task:id,title','user:id,first_name,last_name,profile_picture'])
                    ->withCount([
                        'reactions as likes_count'    => fn($query)=>$query->where('value',1),
                        'reactions as dislikes_count' => fn($query)=>$query->where('value',0),
                    ])
                    ->orderByDesc('id')
                    ->paginate(10, ['*'], 'page', $page);

                $reactions = CommentReaction::query()
                    ->where('user_id', $userId)
                    ->with(['comment:id,task_id,user_id,comment,created_at','comment.task:id,title'])
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get(['id','comment_id','value','created_at']);

                return response()->json([
                    'comments'  => $comments,
                    'reactions' => $reactions,
                ]);
            }

            return response()->json(['message' => 'task_id, or user_id is required!'], 400);
        } catch (\Throwable $error) {
            Log::channel('taskforge')->info('Error loading comments: ' . $error->getMessage());
            return response()->json(['message'=>'Error'], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $data = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'comment' => 'required|string|min:1|max:2000',
            ]);

            $comments = Comment::create([
                'task_id'  => $data['task_id'],
                'user_id'  => $request->user()->id,
                'comment'  => $data['comment'],
            ])->load(['user:id,first_name,last_name']);

            $comments->likes_count = 0;
            $comments->dislikes_count = 0;
            $comments->my_reaction = null;

            return response()->json($comments, 201);
        } catch (\Throwable $error) {
            Log::channel('taskforge')->info('Comment creation error: ' . $error->getMessage());
            return response()->json(['message'=>'Error'], 500);
        }
    }

    public function change(Request $request, Comment $comment)
    {
        try {
            $user = $request->user();
            $isOwner = $comment->user_id === $user->id;
            $isAdmin = property_exists($user,'is_admin') ? (bool)$user->is_admin : false;

            if (!$isOwner && !$isAdmin) {
                return response()->json(['message'=>'Forbidden'], 403);
            }

            $mode = $request->input('mode');

            if ($mode === 'delete') {
                $comment->delete();
                return response()->noContent();
            }

            if ($mode === 'update') {
                $data = $request->validate(['comment'=>'required|string|min:1|max:2000']);
                $comment->update(['comment'=>$data['comment']]);
                return response()->json(
                    $comment->refresh()->load(['user:id,first_name,last_name'])
                );
            }

            return response()->json(['message'=>'Invalid mode'], 422);
        } catch (\Throwable $error) {
            Log::channel('taskforge')->info('Error updating comment: ' . $error->getMessage());
            return response()->json(['message'=>'Error'], 500);
        }
    }

    public function reaction(Request $request, Comment $comment)
    {
        try {
            $data = $request->validate(['value'=>'required|in:0,1']);
            $user_id  = $request->user()->id;

            $existing = CommentReaction::where('comment_id',$comment->id)
                ->where('user_id',$user_id)->first();

            if ($existing && (int)$existing->value === (int)$data['value']) {
                $existing->delete();
            } else {
                CommentReaction::updateOrCreate(
                    ['comment_id'=>$comment->id,'user_id'=>$user_id],
                    ['value'=>$data['value']]
                );
            }

            $counts = CommentReaction::selectRaw("
                    SUM(value=1) as likes,
                    SUM(value=0) as dislikes
                ")->where('comment_id',$comment->id)->first();

            return response()->json([
                'likes'    => (int)($counts->likes ?? 0),
                'dislikes' => (int)($counts->dislikes ?? 0),
            ]);
        } catch (\Throwable $error) {
            Log::channel('taskforge')->info('Error updating reaction: ' . $error->getMessage());
            return response()->json(['message'=>'Error'], 500);
        }
    }
}