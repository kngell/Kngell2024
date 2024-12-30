<?php

declare(strict_types=1);
abstract class AbstractMiddleware
{
    protected function redirect(string $url, bool|HttpStatusCode $permanent = true) : Response
    {
        // $statusCode = $permanent ? HttpStatusCode::HTTP_SEE_OTHER : HttpStatusCode::HTTP_MOVED_PERMANENTLY;
        // $this->response->setStatusCode($statusCode);
        // $this->response->redirect($url);
        return Rooter::redirect($url, $permanent);
    }
}