<?php

declare(strict_types=1);

class PostController extends Controller
{
    private const int RECORDS_PER_PAGE = 2;

    public function __construct(private PostModel $post, private PostFormCreator $frm, private Validator $validator, private FileUploadInterface $imgUpload)
    {
        $this->currentModel($this->post);
    }

    public function index(int|null $currentPage = null) : Response
    {
        $this->pageTitle('Blog');
        $blogPost = new BlogPostDecorator($this, self::RECORDS_PER_PAGE, $currentPage);
        return $this->response('index', $blogPost->page());
    }

    public function new(string|null $form = null) : Response
    {
        if ($this->session->exists('form')) {
            $form = $this->session->get('form');
            $this->session->delete('form');
        }
        $form = $form ?? $this->frm->make('post/create');
        return $this->response('new', ['form' => $form]);
    }

    public function create() : Response
    {
        $data = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($data, 'postRules');
        $this->imgUpload->proceed(false);
        $errors = array_merge($errors, $this->imgUpload->getErrors());
        if (! empty($errors)) {
            $form = $this->frm->make('post/create', [], $errors);
            if (! $this->session->exists('form')) {
                $this->session->set('form', $form);
            }
            return $this->redirect('/post/new');
        }
        null !== $this->imgUpload->getMediaPaths() ? $data['media'] = $this->imgUpload->getMediaPaths() : '';
        $insert = $this->post->save($data);
        if ($insert->getQueryResult()) {
            $this->flash->add('The post has been create successfully');
            return $this->redirect("/post/{$insert->getLastInsertId()}/show");
        }
        return $this->response('new', ['form' => $this->frm->make('post/create')]);
    }

    public function show(string $id) : string
    {
        $this->pageTitle('Show');
        $post = $this->post->getPost($id);
        return $this->render('show', ['post' => $post, 'message' => $this->flash->get()]);
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
        $this->imgUpload->proceed(false);
        $errors = array_merge($errors, $this->imgUpload->getErrors());
        if (! empty($errors)) {
            $form = $this->frm->make("post/update/{$id}", $data, $errors);
            return $this->edit($id, $form);
        }
        null !== $this->imgUpload->getMediaPaths() ? $data['media'] = $this->imgUpload->getMediaPaths() : '';
        $update = $this->post->save($data);
        if ($update->getQueryResult()) {
            $this->flash->add('Post updated sucessfully');
            return $this->redirect("/post/{$id}/show");
        }
        return $this->index();
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