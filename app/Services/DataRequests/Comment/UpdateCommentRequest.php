<?php

namespace App\Services\DataRequests\Comment;

use Spatie\LaravelData\Data;

class UpdateCommentRequest extends Data
{
    public function __construct(
        public int $comment_id,
        public ?string $commenter_name,
        public ?string $message
    )
    {
    }

    public static function rules(): array
    {
        return [
            'comment_id' => ['required', 'integer', 'exists:comments,id'],
            'commenter_name' => ['string', 'max:255'],
            'message' => ['string'],
        ];
    }
}
