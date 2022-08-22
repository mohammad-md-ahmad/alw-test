<?php

namespace App\Services\DataRequests\Comment;

use Spatie\LaravelData\Data;

class CreateCommentRequest extends Data
{
    public function __construct(
        public string $commenter_name,
        public string $message,
        public ?int $parent_comment_id
    )
    {
    }

    public static function rules(): array
    {
        return [
            'commenter_name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'parent_comment_id' => ['integer', 'exists:comments,id'],
        ];
    }
}
