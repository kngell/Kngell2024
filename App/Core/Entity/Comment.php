<?php

declare(strict_types=1);

class Comment extends Entity
{
    #[EntityFieldId('cmtID')]
    private int $id;
    private string $author;
    private string $createdAt;
    private string $message;
    private int $postID;
    private int $userID;
    private int $parentID;
}