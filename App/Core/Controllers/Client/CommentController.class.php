<?php

declare(strict_types=1);

class CommentController extends Controller
{
    public function __construct(private CommentModel $comments)
    {
    }
}