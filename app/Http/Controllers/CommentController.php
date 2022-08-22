<?php

namespace App\Http\Controllers;

use App\Contracts\CommentServiceInterface;
use App\Services\DataRequests\Comment\CreateCommentRequest;
use App\Services\DataRequests\Comment\DeleteCommentRequest;
use App\Services\DataRequests\Comment\QueryCommentRequest;
use App\Services\DataRequests\Comment\UpdateCommentRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CommentController extends Controller
{
    private CommentServiceInterface $commentService;

    public function __construct(CommentServiceInterface $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param QueryCommentRequest $request
     * @return JsonResponse
     */
    public function index(QueryCommentRequest $request): JsonResponse
    {
        try {
            $data = $this->commentService->query($request);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($data, ResponseAlias::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateCommentRequest $request
     * @return JsonResponse
     */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        try {
            $data = $this->commentService->create($request);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([$data], ResponseAlias::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param QueryCommentRequest $request
     * @return JsonResponse
     */
    public function show(QueryCommentRequest $request): JsonResponse
    {
        try {
            $data = $this->commentService->query($request);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($data, ResponseAlias::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCommentRequest $request
     * @return JsonResponse
     */
    public function update(UpdateCommentRequest $request): JsonResponse
    {
        try {
            $data = $this->commentService->update($request);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([$data], ResponseAlias::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCommentRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteCommentRequest $request): JsonResponse
    {
        try {
            $this->commentService->delete($request);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'comment has been deleted successfully!'], ResponseAlias::HTTP_NO_CONTENT);
    }
}
