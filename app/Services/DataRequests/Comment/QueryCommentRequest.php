<?php

namespace App\Services\DataRequests\Comment;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class QueryCommentRequest extends Data
{
    public function __construct(
        public ?int $comment_id,
    )
    {
    }

    public static function rules(): array
    {
        return [
            'comment_id' => ['integer', 'exists:comments,id'],
        ];
    }
}
