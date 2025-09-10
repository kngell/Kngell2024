<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;

final readonly class MimeTypeInfoCodeGenerator
{
    private function __construct()
    {
    }

    public static function generateMimeTypeInfoClass(MimeTypeInfoMap $mimeTypesInfoMap) : void
    {
        $namespace = new PhpNamespace('');
        $class = $namespace->addClass('MimeTypeConstants')->setReadOnly()->setFinal();
        $class->addMethod('__construct')->setPrivate();
        $class->addConstant('MIME_TYPE_TO_EXTENSION', self::getMimeTypeExtensionArray($mimeTypesInfoMap))
            ->setPublic()
            ->setType('array');

        $class->addConstant('EXTENSION_TO_MIME_TYPES', self::getExtensionToMimeTypeArray($mimeTypesInfoMap))
            ->setPublic()
            ->setType('array');

        file_put_contents(dirname(__DIR__) . '/../../Framework/Core/Utils/src/Mime/MimeTypeConstants.php', "<?php\n\n" . $namespace);
    }

    private static function getMimeTypeExtensionArray(MimeTypeInfoMap $mimeTypesInfoMap) : array
    {
        $types = [];
        foreach ($mimeTypesInfoMap->getMimeTypeInfos() as $mimeTypeIfo) {
            foreach ($mimeTypeIfo->getContentTypes() as $type) {
                $types[$type] = $mimeTypeIfo->getExtentions();
            }
        }

        return $types;
    }

    private static function getExtensionToMimeTypeArray(MimeTypeInfoMap $mimeTypesInfoMap) : array
    {
        $extentions = [];
        foreach ($mimeTypesInfoMap->getMimeTypeInfos() as $mimeTypeIfo) {
            foreach ($mimeTypeIfo->getExtentions() as $extension) {
                $extentions[$extension] = $mimeTypeIfo->getContentTypes();
            }
        }

        return $extentions;
    }
}
