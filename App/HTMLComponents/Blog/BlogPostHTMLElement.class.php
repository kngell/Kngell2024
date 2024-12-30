<?php

declare(strict_types=1);
class BlogPostHTMLElement
{
    /** @var Post[] */
    private array $posts;
    private array $wrapperClass = ['card'];
    private array $wrapperStyle = ['margin-top: 20px'];

    /**
     * @param Post[] $posts
     * @param null|TemplatePathsInterface $paths
     * @return void
     */
    public function __construct(array $posts, private TokenInterface $token, private HtmlBuilder $builder)
    {
        $this->posts = $posts;
    }

    public function display(): string
    {
        $html = $this->builder;
        $posts = [];
        foreach ($this->posts as $post) {
            $form = $this->builder->form($this->token);
            $posts[] = $html->tag('div')->class($this->wrapperClass)->style($this->wrapperStyle)->add(
                $html->tag('div')->class(['card-body'])->add(
                    $html->tag('h3')->content($this->htmlDecode($post->getTitle(), ENT_QUOTES)),
                    $html->tag('p')->content($this->getContentOverview($this->htmlDecode($post->getTitle()))),
                    $html->tag('a')->href("/post/show/{$post->getPostId()}")->class(['btn btn-primary'])->content('Show Post'),
                    $html->tag('a')->href("/post/edit/{$post->getPostId()}")->class(['btn btn-info'])->content('Edit Post'),
                    $form->action('post/delete')->method('post')->style(['display: inline-block'])->add(
                        $form->input('hidden')->name('post_id')->value($post->getPostId()),
                        $form->button()->type('submit')->class(['btn btn-danger'])->content('Delete')
                    )
                )
            )->generate();
        }
        return implode(' ', $posts);
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