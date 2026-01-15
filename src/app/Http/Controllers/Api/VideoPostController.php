<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreVideoPostRequest;
use App\Http\Requests\Api\UpdateVideoPostRequest;
use App\Http\Resources\Api\VideoPostResource;
use App\Models\VideoPost;
use Illuminate\Http\JsonResponse;
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
    public function show(VideoPost $videoPost): JsonResponse
    {
        return response()->json([
            'data' => new VideoPostResource($videoPost),
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
