<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreVideoPostRequest;
use App\Http\Requests\Api\UpdateVideoPostRequest;
use App\Http\Resources\Api\CommentResource;
use App\Http\Resources\Api\VideoPostResource;
use App\Models\VideoPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $videoPosts = VideoPost::latest()->paginate(15);

        return VideoPostResource::collection($videoPosts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVideoPostRequest $request): JsonResponse
    {
        $videoPost = VideoPost::create($request->validated());

        return response()->json([
            'data' => new VideoPostResource($videoPost),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, VideoPost $videoPost): JsonResponse
    {
        $cursor = $request->query('cursor');
        $limit = (int) $request->query('limit', 20);

        $commentsQuery = $videoPost->comments()
            ->with('user')
            ->orderBy('created_at', 'asc');

        if ($cursor) {
            try {
                $cursorDate = \Carbon\Carbon::parse($cursor);
                $commentsQuery->where('created_at', '>', $cursorDate);
            } catch (\Exception $e) {
                // Invalid cursor format, ignore it
            }
        }

        $comments = $commentsQuery->limit($limit + 1)->get();
        $hasMore = $comments->count() > $limit;

        if ($hasMore) {
            $comments = $comments->take($limit);
        }

        $nextCursor = $comments->isNotEmpty() 
            ? $comments->last()->created_at->toISOString() 
            : null;

        return response()->json([
            'data' => new VideoPostResource($videoPost),
            'comments' => [
                'data' => CommentResource::collection($comments),
                'meta' => [
                    'next_cursor' => $nextCursor,
                    'has_more' => $hasMore,
                ],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVideoPostRequest $request, VideoPost $videoPost): JsonResponse
    {
        $videoPost->update($request->validated());

        return response()->json([
            'data' => new VideoPostResource($videoPost->fresh()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VideoPost $videoPost): JsonResponse
    {
        $videoPost->delete();

        return response()->json([
            'message' => 'Video post deleted successfully',
        ]);
    }
}
