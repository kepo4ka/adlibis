<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreNewsRequest;
use App\Http\Requests\Api\UpdateNewsRequest;
use App\Http\Resources\Api\CommentResource;
use App\Http\Resources\Api\NewsResource;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $news = News::latest()->paginate(15);

        return NewsResource::collection($news);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $news = News::create($request->validated());

        return response()->json([
            'data' => new NewsResource($news),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, News $news): JsonResponse
    {
        $cursor = $request->query('cursor');
        $limit = (int) $request->query('limit', 20);

        $commentsQuery = $news->comments()
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
            'data' => new NewsResource($news),
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
    public function update(UpdateNewsRequest $request, News $news): JsonResponse
    {
        $news->update($request->validated());

        return response()->json([
            'data' => new NewsResource($news->fresh()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news): JsonResponse
    {
        $news->delete();

        return response()->json([
            'message' => 'News deleted successfully',
        ]);
    }
}
