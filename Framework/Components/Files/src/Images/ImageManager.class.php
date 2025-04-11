<?php

declare(strict_types=1);

class ImageManager implements FilesManagerInterface
{
    private $imgName;
    private string $sourcePath;
    private string $destinationPath;
    private string $img;
    private array $imgInfos;
    private array $imgAry;
    private array $allowType = ['JPG', 'PNG', 'JPEG', 'GIF', 'BMP', 'WEBP'];
    private $targetDir = IMAGE_ROOT;
    private string $imgExt;
    private int $imgType;
    private mixed $imgSize;

    public function __construct(array $imgAry = [], string $sourcePath = '', string $destination = '')
    {
        $this->imgAry = $imgAry;
        $this->sourcePath = $sourcePath != '' ? $sourcePath : dirname($imgAry['tmp_name']);
        $this->destinationPath = $destination == '' ? $this->sourcePath : $destination;
        $this->imgName = array_key_exists('name', $imgAry) ? $imgAry['name'] : '';
        $this->setImgInfos();
    }

    public function validate() : bool
    {
        //validate file type
        if (! in_array($this->imgExt, $this->allowType)) {
            throw new FileExtensionException('Image Type not supported');
        }
        if ($this->imgSize > MAX_IMG_SIZE) {
            throw new FileSizeException('File Size exceeded! You cannot load more than 5MB files.');
        }
        if ($this->imgInfos[0] > '1840' && $this->imgInfos[1] > '860') {
            throw new FileWidthAndHeightException('Invalid width and height! Please change your file.');
        }
        return true;
    }

    public function getFileName() : string
    {
        return $this->imgName;
    }

    public function getDestinationPath() : string
    {
        return $this->destinationPath;
    }

    public function getSourcePath() : string
    {
        return $this->sourcePath;
    }

    public function getTargetDir() : string
    {
        return $this->targetDir;
    }

    public function get_infos()
    {
        return $this->infos;
    }

    public function resizeImage(string $width = '', string $height = '', bool $crop = false) : bool
    {
        if (! list($w, $h) = $this->infos) {
            return 'Unsupported image type!';
        }
        $type = strtolower(substr(strrchr($this->img_name, '.'), 1));
        if ($type == 'jpeg') {
            $type = 'jpg';
        }
        $sourceImg = $this->open_img();
        $width = $width != '' ? $width : $this->infos[0];
        $height = $height != '' ? $height : $this->infos[1];
        list($newImg, $x, $w, $h) = $this->resize($width, $height, $w, $h, $crop);
        if ($type == 'gif' or $type == 'png') {
            imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
            imagealphablending($newImg, false);
            imagesavealpha($newImg, true);
        }
        imagecopyresampled($newImg, $sourceImg, 0, 0, $x, 0, $width, $height, $w, $h);
        $result = $this->save_image($newImg);
        $this->destroyImage($sourceImg);
        $this->destroyImage($newImg);

        return $result;
    }

    public function cropImage(string $width = '', string $height = '') : bool
    {
        $sourceImg = $this->open_img();
        $newImg = imagecrop($sourceImg, [
            'x' => 0,
            'y' => 0,
            'width' => $width != '' ? $width : $this->infos[0],
            'height' => $height != '' ? $height : $this->infos[1],
        ]);
        $result = $this->save_image($newImg);
        $this->destroyImage($newImg);

        return $result;
    }

    public function RotateImage(int $rotang = 0) : bool
    {
        $sourceImg = $this->open_img();
        imagealphablending($sourceImg, false);
        imagesavealpha($sourceImg, true);
        $newImg = imagerotate($sourceImg, $rotang, imagecolorallocatealpha($sourceImg, 0, 0, 0, 127));
        $result = $this->save_image($newImg);
        $this->destroyImage($newImg);
        $this->destroyImage($sourceImg);

        return $result;
    }

    public function destroyImage(?GdImage &$img = null)
    {
        if (isset($img)) {
            return imagedestroy($img);
        }

        return true;
    }

    public static function asset_img($img = '') : string
    {
        return HOST ? HOST . IMG . $img : IMG . $img;
    }

    private function setImgInfos() : void
    {
        $incommingSrcPath = array_key_exists('tmp_name', $this->imgAry) ? $this->imgAry['tmp_name'] : '';
        $this->img = $this->sourcePath != '' ? $this->sourcePath : $incommingSrcPath;
        if ($this->img != '') {
            $this->imgInfos = getimagesize($this->img);
            $this->imgExt = strtoupper(pathinfo($this->imgName, PATHINFO_EXTENSION));
            $this->imgType = exif_imagetype($this->img);
            $this->imgSize = $this->imgAry['size'];
        }
    }

    private function save_image(GdImage $newImg)
    {
        if (! file_exists(dirname($this->destinationPath . DS . $this->img_name))) {
            mkdir(dirname($this->destinationPath), 0777, true);
        } else {
            chmod($this->destinationPath, 0777);
        }
        if (isset($this->infos) && ! file_exists($this->destinationPath . DS . $this->img_name)) {
            switch ($this->infos['mime']) {
                case 'image/png':
                    return imagepng($newImg, $this->destinationPath . DS . $this->img_name);
                    break;
                case 'image/jpeg':
                    return imagejpeg($newImg, $this->destinationPath . DS . $this->img_name);
                    break;
                case 'image/gif':
                    return imagegif($newImg, $this->destinationPath . DS . $this->img_name);
                    break;
                default:
                    return false;
                    break;
            }
        }
    }

    private function resize(int $width, int $height, int $w, int $h, bool $crop = false) : array
    {
        if ($crop) {
            if ($w < $width or $h < $height) {
                return 'Picture is too small!';
            }
            $ratio = max($width / $w, $height / $h);
            $h = $height / $ratio;
            $x = ($w - $width / $ratio) / 2;
            $w = $width / $ratio;
        } else {
            if ($w < $width and $h < $height) {
                return 'Picture is too small!';
            }
            $ratio = min($width / $w, $height / $h);
            $width = $w * $ratio;
            $height = $h * $ratio;
            $x = 0;
        }

        return [imagecreatetruecolor($width, $height), $x, $w, $h];
    }

    private function open_img()
    {
        if (isset($this->infos)) {
            switch ($this->infos['mime']) {
                case 'image/png':
                    return imagecreatefrompng($this->img);
                    break;
                case 'image/jpeg':
                    return imagecreatefromjpeg($this->img);
                    break;
                case 'image/gif':
                    return imagecreatefromgif($this->img);
                    break;
                default:
                    return false;
                    break;
            }
        }
    }
}