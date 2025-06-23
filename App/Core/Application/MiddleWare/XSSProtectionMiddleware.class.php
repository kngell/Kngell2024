<?php

declare(strict_types=1);

class XSSProtectionMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        if ($request->getMethod() === HttpMethod::POST) {
            $data = $request->getPost()->getAll();
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            }
            $request->getPost()->addAll($data);
            return $next->handle($request);
        }
        return $next->handle($request);
    }
}
