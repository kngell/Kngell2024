<?php

declare(strict_types=1);


class ValidationJsExporterMiddleware
{
    public function __construct(
        private ValidationMessageService $messageService,
        private ValidationRulesExporter $exporter,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $globalSettings = [
            'messages' => $this->messageService->getErrorClasses() ?? [],
            'classes' => [
                'hint' =>  $this->messageService->getHintClasses(),
                'error' =>  $this->messageService->getErrorClasses(),
            ],
        ];

        $rulesFilePath = SRC . 'assets' . DS . 'js' . DS . 'components' . DS . 'validation' . DS . 'validation-rules.json';
        $this->exporter->exportForClient($rulesFilePath, $globalSettings);

        return $next->handle($request);
    }
}