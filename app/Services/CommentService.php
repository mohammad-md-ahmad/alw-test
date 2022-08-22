<?php

namespace App\Services;

use App\Contracts\CommentServiceInterface;
use App\Services\DataRequests\Comment\CreateCommentRequest;
use App\Services\DataRequests\Comment\DeleteCommentRequest;
use App\Services\DataRequests\Comment\QueryCommentRequest;
use App\Services\DataRequests\Comment\UpdateCommentRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\LaravelData\Data;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CommentService implements CommentServiceInterface
{
    public const MAX_COMMENT_LEVEL = 3;

    /**
     * Retrieve comments
     *
     * @param Data $data
     * @return array
     */
    public function query(Data $data): array
    {
        if (! $data instanceof QueryCommentRequest) {
            throw new InvalidArgumentException('CommentService::query needs to receive a QueryCommentRequest.');
        }

        if ($data->comment_id) {
            return $this->getCommentWithChildren($data->comment_id);
        }

        return $this->getAllCommentsWithChildren();
    }

    /**
     * Create a new comment or sub-comment
     *
     * @param Data $data
     * @return array
     */
    public function create(Data $data): array
    {
        if (! $data instanceof CreateCommentRequest) {
            throw new InvalidArgumentException('CommentService::create needs to receive a CreateCommentRequest.');
        }

        $commentId = null;

        DB::transaction(function () use ($data, &$commentId) {
            $nbOfParentComments = 0;

            if ($data->parent_comment_id) {
                $nbOfParentComments = $this->countCommentParents($data->parent_comment_id);
            }

            if ($nbOfParentComments < self::MAX_COMMENT_LEVEL) {
                $commentId = DB::table('comments')->insertGetId([
                    'user_id' => 1,
                    'post_id' => 1,
                    'commenter_name' => $data->commenter_name,
                    'message' => $data->message
                ]);

                if ($data->parent_comment_id) {
                    DB::table('child_comments')->insert([
                        'parent_comment_id' => $data->parent_comment_id,
                        'child_comment_id' => $commentId
                    ]);
                }
            } else {
                throw new BadRequestHttpException("Max comment tree length reached out!");
            }
        });

        return $this->getCommentWithChildren($commentId);
    }

    /**
     * Update a comment record
     *
     * @param Data $data
     * @return array
     */
    public function update(Data $data): array
    {
        if (! $data instanceof UpdateCommentRequest) {
            throw new InvalidArgumentException('CommentService::update needs to receive a UpdateCommentRequest.');
        }

        $comment = $this->getCommentWithChildren($data->comment_id);

        DB::transaction(function () use ($data, $comment) {
            DB::update("UPDATE comments SET commenter_name = ?, message = ? WHERE comments.id = ?", [
                $data->commenter_name ?? $comment['name'],
                $data->message ?? $comment['message'],
                $data->comment_id
            ]);
        });

        return $this->getCommentWithChildren($data->comment_id);
    }

    /**
     * Delete a comment record and its sub-comments
     *
     * @param Data $data
     * @return bool
     */
    public function delete(Data $data): bool
    {
        if (! $data instanceof DeleteCommentRequest) {
            throw new InvalidArgumentException('CommentService::delete needs to receive a DeleteCommentRequest.');
        }

        $comment = $this->getCommentWithChildren($data->comment_id);

        $commentId = $comment['id'];
        $childrenIds = $this->getCommentChildrenIds($comment);

        // add parent comment to children IDs list if its itself a child
        if ($this->commentIsChild($commentId)) {
            $childrenIds[] = $commentId;
        }

        DB::transaction(function () use ($childrenIds, $commentId) {
            if (!empty($childrenIds)) {
                DB::delete("DELETE FROM child_comments WHERE child_comment_id IN(" . implode(', ', $childrenIds) . ")");
            }

            // add parent comment id if not exists
            if (!in_array($commentId, $childrenIds)) {
                $childrenIds[] = $commentId;
            }

            DB::delete("DELETE FROM comments WHERE id IN(" . implode(', ', $childrenIds) . ")");
        });

        return true;
    }

    /**
     * Count the parents tree of a comment
     *
     * @param $commentId
     * @param int $counter
     * @return int
     */
    public function countCommentParents($commentId, int &$counter = 0): int
    {
        if ($this->commentIsChild($commentId)) {
            $counter++;

            $parentCommentId = $this->getParentCommentId($commentId);

            return $this->countCommentParents($parentCommentId, $counter);
        }

        return $counter;
    }

    /**
     * Retrieve a comment's parent ID
     *
     * @param $commentId
     * @return int
     */
    public function getParentCommentId($commentId): int
    {
        $query =
            "SELECT ".
                "child_comments.parent_comment_id ".
            "FROM child_comments ".
            "WHERE child_comments.child_comment_id = ?";

        $parentComment = DB::select($query, [$commentId]);

        return optional($parentComment[0])->parent_comment_id;
    }

    /**
     * Check if a comment is child
     *
     * @param $commentId
     * @return bool
     */
    public function commentIsChild($commentId): bool
    {
        $query =
            "SELECT " .
            "child_comments.child_comment_id " .
            "FROM child_comments " .
            "WHERE child_comments.child_comment_id = ?";

        return !empty(DB::select($query, [$commentId]));
    }

    /**
     * Retrieve the children comments ID list of a parent comment
     *
     * @param $comment
     * @param array $ids
     * @return array
     */
    public function getCommentChildrenIds($comment, array &$ids = []): array
    {
        if (empty($comment['comments'])) {
            return $ids;
        }

        foreach ($comment['comments'] as $child) {
            $ids[] = $child['id'];

            return $this->getCommentChildrenIds($child,$ids);
        }
    }

    /**
     * Retrieve a comment by ID
     *
     * @param $commentId
     * @return array
     */
    public function getComment($commentId): array
    {
        $query =
            "SELECT " .
            "comments.id," .
            "comments.commenter_name AS name," .
            "comments.message " .
            "FROM comments " .
            "WHERE comments.id = ?";

        $comment = DB::select($query, [$commentId]);

        return stdClassToArray($comment[0]);
    }

    /**
     * Retrieve the children comments list of a parent comment by ID
     *
     * @param $commentId
     * @return array
     */
    public function getCommentChildren($commentId): array
    {
        $childCommentsQuery =
            "SELECT ".
            "comments.id,".
            "comments.commenter_name AS name,".
            "comments.message ".
            "FROM comments ".
            "JOIN child_comments ".
            "ON comments.id = child_comments.child_comment_id ".
            "WHERE child_comments.parent_comment_id = ?";

        $comments = DB::select($childCommentsQuery, [$commentId]);

        $result = [];

        foreach ($comments as $comment) {
            $comment = stdClassToArray($comment);
            $comment['comments'] = $this->getCommentChildren($comment['id']);

            $result[] = $comment;
        }

        return $result;
    }

    /**
     * Retrieve a comment by ID with its children comments tree
     *
     * @param $commentId
     * @return array
     */
    public function getCommentWithChildren($commentId): array
    {
        $comment = $this->getComment($commentId);
        $comment['comments'] = $this->getCommentChildren($commentId);

        return $comment;
    }

    /**
     * Retrieve all comments with their own children comments tree
     *
     * @return array
     */
    public function getAllCommentsWithChildren(): array
    {
        $commentsQuery =
            "SELECT " .
            "comments.id," .
            "comments.commenter_name AS name," .
            "comments.message " .
            "FROM comments ".
            "WHERE comments.id NOT IN(
                SELECT child_comments.child_comment_id FROM child_comments
            )";

        $comments = DB::select($commentsQuery);

        $result = [];

        foreach ($comments as $comment) {
            $comment = stdClassToArray($comment);
            $comment['comments'] = $this->getCommentChildren($comment['id']);

            $result[] = $comment;
        }

        return $result;
    }
}
