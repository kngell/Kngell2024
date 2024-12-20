<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;

final readonly class StatusCodeGenerator
{
    private const string NAMESPACE = '';

    private function __construct()
    {
    }

    /**
     * @param StatusCode[] $codes
     * @return void
     */
    public static function generateHttpCodeClasses(array $codes) : void
    {
        $namespace = new PhpNamespace(self::NAMESPACE);
        $httpStatusCodeEnum = $namespace->addEnum('HttpStatusCode');
        $httpStatusCodeEnum->addComment('Generate by StatusCodeGenerator');
        $httpStatusCodeEnum->setType('int');

        $message = [];
        foreach ($codes as $statusCode) {
            $message[$statusCode->getCode()] = $statusCode->getMessage();
            $httpStatusCodeEnum->addCase(self::generateHttpCodeName($statusCode), $statusCode->getCode())->addComment($statusCode->getDescription());
        }

        file_put_contents(
            dirname(__DIR__) . '/Common/HttpStatusCode.php',
            "<?php\n\n" . $namespace
        );

        $namespace = new PhpNamespace(self::NAMESPACE);
        $httpStatusMessageClass = $namespace->addClass('HttpStatusCodeMessages');
        $httpStatusMessageClass->setReadOnly()
            ->setFinal()
            ->addComment('Generate by StatusCodeGenerator');
        $httpStatusMessageClass->addMethod('__construct')->setPrivate();
        $httpStatusMessageClass->addConstant('STATUS_CODE_TO_MESSAGE', $message)
            ->setPublic()
            ->setType('array');
        file_put_contents(
            dirname(__DIR__) . '/Common/HttpStatusCodeMessages.php',
            "<?php\n\n" . $namespace
        );
    }

    private static function generateHttpCodeName(StatusCode $statuscode) : string
    {
        $name = strtoupper($statuscode->getMessage());
        $name = str_replace([' ', '-'], '_', $name);
        $name = str_replace(['(', ')', '\''], '', $name);
        return 'HTTP_' . $name;
    }
}
