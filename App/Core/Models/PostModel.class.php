<?php

declare(strict_types=1);

class PostModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getPaginatedPost(int $limit, int $offset) : QueryResult
    {
        $this->entityManager->createQueryBuilder()
            ->select()
            ->OrderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->build();
        return $this->entityManager->persist()->getResults();
    }

    public function getPost(string|int $id) : Post
    {
        $post = $this->find($id)->getResults('class')->single();
        if ($post === false) {
            throw new PageNotFoundException("Post $id not found");
        }
        return $post;
    }

    public function getTotal() : int
    {
        $this->entityManager->createQueryBuilder()->select('count(title) AS tot')->build();
        $total = $this->entityManager->persist()->getResults();
        $count = ArrayUtils::first($total->getResults()->all());
        return $count['tot'];
    }
}