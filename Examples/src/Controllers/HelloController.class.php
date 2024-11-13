<?php

declare(strict_types=1);

#[ControllerAttr]
class HelloController
{
    #[GetPathAttr(path: '/hello/world/{name}')]
    #[ResponseBody(type: ResponseBodyType::RAW, produces: 'text/xml')]
    #[ResponseStatus(statusCode: HttpStatusCode::HTTP_CREATED)]
    public function hello(
        #[PathVariableAttr]
        string $name,
        #[HeaderParam(name: 'Authorization', required: false)]
        string|null $authHeader,
        #[QueryParam(name: 'capitalize', defaultValue: false, required: false)]
        bool $capitalize
    ) : string {
        dump($authHeader);
        dump($capitalize);
        return 'hello world ' . $name;
    }

    #[PostPathAttr(path: '/hello/world')]
    #[ResponseBody(type: ResponseBodyType::JSON)]
    #[ResponseStatus(statusCode: HttpStatusCode::HTTP_CREATED)]
    public function helloPost(
        #[RequestBody(required: true)]
        HelloRequest $request,
        Request $httpRequest
    ) : HelloResponse {
        $r = $httpRequest->getHeaders();
        return new HelloResponse($request->getName() . ' with response');
    }
}