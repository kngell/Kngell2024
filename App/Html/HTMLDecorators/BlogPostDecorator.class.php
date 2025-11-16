<?php

declare(strict_types=1);

class BlogPostDecorator extends AbstractHtmlDecorator
{
    private int $recordsPerPage;
    private int|null $currentPage;
    private Paginator $paginator;
    private PostModel $postModel;

    public function __construct(Controller $controller, int $recordsPerpPage, int|null $currentPage)
    {
        parent::__construct($controller);
        $this->recordsPerPage = $recordsPerpPage;
        $this->currentPage = $currentPage ?? 1;
        $this->postModel = $this->postModel();
        $this->paginator = $this->paginator();
    }

    public function page(): array
    {
        $postData = $this->postModel->getPaginatedPost($this->recordsPerPage, $this->paginator->getOffset())->getResults('class', 'Post')->all();
        /** @var BlogPostHTMLElement */
        $posts = new BlogPostHTMLElement($postData, $this->controller->getBuilder());
        return array_merge(
            [
                'posts' => $posts->display(),
                'links' => $this->paginator->getLinks(),
            ],
            $this->controller->page()
        );
    }

    private function paginator() : Paginator
    {
        try {
            return new Paginator(
                $this->postModel->getTotal(),
                $this->recordsPerPage,
                $this->currentPage,
                $this->controller->getBuilder()
            );
        } catch (Throwable $th) {
            throw $th;
        }
    }

    private function postModel() : PostModel
    {
        $postModel = $this->controller->getCurrentModel();
        if (! $postModel instanceof PostModel) {
            throw new InvalidArgumentException(sprintf(
                'Model %s is not a valid post model',
                get_class($postModel)
            ));
        }
        return $postModel;
    }
}