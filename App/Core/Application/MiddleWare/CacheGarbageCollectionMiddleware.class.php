<?php

declare(strict_types=1);

class CacheGarbageCollectionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $results = $next->handle($request);

        // Run garbage collection with 1% probability
        if (random_int(1, 100) === 1 && method_exists($this->cache, 'collectGarbage')) {
            $this->cache->collectGarbage();
        }
        if ($results instanceof Response) {
            return $results;
        }
        return new Response($results);
    }
}