<?php

declare(strict_types=1);

class BlogPostHTMLElement extends AbstractHtml
{
    /** @var Post[] */
    private array $posts;
    private array $wrapperClass = ['card'];
    private array $wrapperStyle = ['margin-top: 20px'];

    private Paginator $paginator;
    private HtmlBuilder $builder;

    //private TokenInterface $token, private HtmlBuilder $builder
    /**
     * @param array $posts
     * @param HtmlBuilder $builder
     * @return void
     */
    public function __construct(array $posts, HtmlBuilder $builder)
    {
        $this->posts = $posts;
        $this->builder = $builder;
    }

    public function display(): string
    {
        $html = $this->builder;
        $postsHtml = [];
        /** @var Post $post */
        foreach ($this->posts as $post) {
            $form = $html->form();
            $postsHtml[] = $html->tag('div')->class($this->wrapperClass)->style($this->wrapperStyle)->add(
                $html->tag('div')->class(['card-body'])->add(
                    $html->tag('h3')->content($this->htmlDecode($post->getTitle(), ENT_QUOTES)),
                    $html->tag('p')->content($this->getContentOverview($this->htmlDecode($post->getTitle()))),
                    $html->tag('p')->content('Publié le ' . $this->createdAt($post)),
                    $html->tag('img')->src($this->media($post->getMedia())),
                    $html->tag('a')->href("/post/show/{$post->getPostId()}")->class(['btn btn-primary'])->content('Show Post'),
                    $html->tag('a')->href("/post/edit/{$post->getPostId()}")->class(['btn btn-info'])->content('Edit Post'),
                    $form->action('post/delete')->method('post')->style(['display: inline-block'])->add(
                        $form->input('hidden')->name('post_id')->value($post->getPostId()),
                        $form->button()->type('submit')->class(['btn btn-danger'])->content('Delete')
                    )
                )
            )->generate();
        }
        return implode(' ', $postsHtml);
    }

    private function createdAt(Post $post) : string
    {
        $date = new DateTimeImmutable($post->getCreatedAt());
        // $dateExploded = explode('-', $post->getCreatedAt());
        // $timestamp = mktime(12, 0, 0, (int) $dateExploded[1], (int) $dateExploded[2], (int) $dateExploded[0]);
        // $timestamp2 = $date->getTimestamp();
        return $date->format('d-m-Y') . ' à ' . $date->format('h:m');
    }

    private function getContentOverview(string $content):string
    {
        return substr(strip_tags($this->htmlDecode($content)), 0, 1000) . '...';
    }

    private function htmlDecode(string|null $str) : string
    {
        return ! empty($str) ? htmlspecialchars_decode(html_entity_decode($str), ENT_QUOTES) : '';
    }
}