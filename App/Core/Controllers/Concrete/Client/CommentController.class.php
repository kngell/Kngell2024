<?php

declare(strict_types=1);

class CommentController extends Controller
{
    private PostModel $post1;

    public function __construct(private CommentModel $comments)
    {
    }
}
