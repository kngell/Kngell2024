<?php

declare(strict_types=1);

class PostController extends Controller
{
    public function __construct(private PostModel $post, private PostFormCreator $frm, private Validator $validator, private FlashInterface $flash, private HtmlBuilder $html)
    {
    }

    public function index() : Response
    {
        $posts = $this->post->all()->getResults('class')->all();
        $postHtmlObj = new BlogPostHTMLElement($posts, $this->token, $this->html);
        return $this->response(
            'index',
            [
                'posts' => $postHtmlObj->display(),
                'total' => $this->post->getTotal(),
                'message' => $this->flash->get(),
            ]
        );
    }

    public function new(string|null $form = null) : Response
    {
        $session = $this->token->getSession();
        if ($session->exists('form')) {
            $form = $session->get('form');
            $session->delete('form');
        }
        $form = $form ?? $this->frm->make('post/create');
        return $this->response('new', ['form' => $form]);
    }

    public function create() : Response
    {
        $data = $this->request->getPost()->getAll();
        $results = $this->validator->validate($data, 'postRules');
        $session = $this->token->getSession();
        if (! empty($results)) {
            $form = $this->frm->make('post/create', [], $results);
            if (! $session->exists('form')) {
                $session->set('form', $form);
            }
            return $this->redirect('/post/new');
        }
        $insert = $this->post->save($data);
        if ($insert->getQueryResult()) {
            $this->flash->add('The post has been create successfully');
            return $this->redirect("/post/{$insert->getLastInsertId()}/show");
        }
    }

    public function show(string $id) : string
    {
        $this->pageTitle('Show');
        $post = $this->post->getPost($id);
        return $this->render('show', ['post' => $post, 'messgae' => $this->flash->get()]);
    }

    public function edit(string $id, string|null $form = null) : string
    {
        $this->pageTitle('Edit Post');
        if ($form === null) {
            $post = $this->post->getPost($id);
            $form = $this->frm->make("post/$id/update", $post);
        }
        return $this->render('edit', ['id' => $id, 'editFrom' => $form]);
    }

    public function update(string $id) : Response|string
    {
        $data = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($data, 'postRules');
        if (! empty($errors)) {
            $form = $this->frm->make("post/update/{$id}", $data, $errors);
            return $this->edit($id, $form);
        }
        $update = $this->post->save($data);
        if ($update->getQueryResult()) {
            $this->flash->add('Post updated sucessfully');
            return $this->redirect("/post/{$id}/show");
        }
    }

    public function delete(string|null $id = null) : string
    {
        if ($id === null && $this->request->getServer()->get('request_method') === 'POST') {
            $data = $this->request->getPost()->getAll();
            $id = $data['post_id'];
        }
        $form = $this->frm->make("post/destroy/{$id}");
        return $this->render('delete', ['id' => $id, 'deleteForm' => $form]);
    }

    public function destroy(string $id) : Response
    {
        $delete = $this->post->delete($id)->getResults();
        if ($delete->getQueryResult()) {
            $this->flash->add('Post has been deleted sucessfully');
        }
        // /** @var SessionInterface */
        // $session = GlobalManager::get(App::getInstance()->getGlobalSessionKey());
        return $this->redirect('/post/index');
    }
}