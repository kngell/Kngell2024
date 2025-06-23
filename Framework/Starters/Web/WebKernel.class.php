<?php

declare(strict_types=1);

use JMS\Serializer\Serializer;

abstract class WebKernel extends Kernel
{
    protected Rooter $rooter;
    protected InterceptorChain $interceptorChain;

    protected array $cachedRouteInfo;

    public function __construct()
    {
        parent::__construct();
        $this->interceptorChain = $this->serviceContainer->get('kngell-ecom.interceptorChain');
        $this->createRooter();
    }

    public function handleRequest(Request $request) : void
    {
        $this->interceptorChain->preRequest($request);

        $response = $this->rooter->handle($request);

        $this->interceptorChain->postRequest($request, $response);
        $response->prepare($request);
        $response->send();
    }

    protected function createRooter() : void
    {
        $this->generateCachedRouteInfo();
        $serializer = $this->serviceContainer->getFirstBeanByClass(Serializer::class);
        $this->rooter = new Rooter(
            new RouteMatcher($this->cachedRouteInfo),
            new RouteArgumentGenerator($serializer),
            new RouteResponseGenerator($serializer)
        );
    }

    protected function generateCachedRouteInfo() : void
    {
        $routeScanner = new RouteScanner();
        $this->cachedRouteInfo = $routeScanner->scanForRoutes($this->serviceContainer);
    }
}