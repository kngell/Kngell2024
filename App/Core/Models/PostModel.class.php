<?php

declare(strict_types=1);

class PostModel extends Model
{
    public function getPaginatedPost(int $limit, int $offset): QueryResult
    {
        $this->em->createQueryBuilder()
            ->select()
            ->OrderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->build();
        return $this->em->persist()->getResults();
    }

    public function getPost(string|int $id): Post
    {
        $post = $this->find($id)->getResults('class')->single();
        if ($post === false) {
            throw new PageNotFoundException("Post $id not found");
        }
        return $post;
    }

    public function getTotal(): int
    {
        $this->em->createQueryBuilder()->select('count(title) AS tot')->build();
        $total = $this->em->persist()->getResults();
        $count = ArrayUtils::first($total->getResults()->all());
        return $count['tot'];
    }
}