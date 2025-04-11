<?php

declare(strict_types=1);
class PratiqueController extends Controller
{
    public function __construct(private UserModel $user, private Validator $validator)
    {
        $this->setLayout('training');
    }

    public function index() : string
    {
        $this->pageTitle('Exos Pratiques');
        return $this->render('index');
    }

    public function figma() : string
    {
        $this->pageTitle('Exos Pratiques');

        return $this->render('figma');
    }

    public function dropzone() : string
    {
        $this->pageTitle('Dropzone');
        return $this->render('dropzone');
    }

    public function profile() : string
    {
        $this->pageTitle('Profile');
        return $this->render('profile');
    }

    public function grid() : string
    {
        $this->pageTitle('Grid');
        return $this->render('grid');
    }

    public function cms() : string
    {
        $this->pageTitle('CMS');
        $dir = ROOT_DIR . DS . 'App' . DS . 'Data';
        $files = FileManager::filePaths($dir);
        $data = [];
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $textFile = '';
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $dirname = pathinfo($file, PATHINFO_DIRNAME);
                if (file_exists($dirname . DS . $filename . '.txt')) {
                    $textFile = array_map(
                        function (string $text): string {
                            return htmlspecialchars($text);
                        },
                        explode("\n", file_get_contents($dirname . DS . $filename . '.txt'))
                    );
                }
                $image_mime = FileManager::getFileMimeType($file);
                $data[] = [
                    'img' => 'data:' . $image_mime . ';base64,' . base64_encode(file_get_contents($file)),
                    'title' => is_array($textFile) ? array_shift($textFile) : '',
                    'content' => is_array($textFile) ? $textFile : [],
                ];
            }
        }
        return $this->render('cms', ['datas' => $data]);
    }

    public function dairy() : string
    {
        $this->pageTitle('php Dairy App');
        return $this->render('dairy_app');
    }
}