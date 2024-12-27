<?php

declare(strict_types=1);

class PostCategory extends Entity
{
    private int $postCatId;
    private int $postId;
    private int $catId;

    /**
     * @return int
     */
    public function getPostCatId(): int
    {
        return $this->postCatId;
    }

    /**
     * @param int $postCatId
     * @return PostCategory
     */
    public function setPostCatId(int $postCatId): self
    {
        $this->postCatId = $postCatId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @param int $postId
     * @return PostCategory
     */
    public function setPostId(int $postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCatId(): int
    {
        return $this->catId;
    }

    /**
     * @param int $catId
     * @return PostCategory
     */
    public function setCatId(int $catId): self
    {
        $this->catId = $catId;
        return $this;
    }
}