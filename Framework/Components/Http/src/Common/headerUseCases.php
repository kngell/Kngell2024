<?php

declare(strict_types=1);

// Need custom headers for an API endpoint?
$response->getHeaders()->add('X-API-Version', '1.0');

// Special CSP for admin area?
if ($request->isAdminArea()) {
    $headerManager->applyAdminCsp();
}

// Usage in controllers:
return new ApiResponse(
    ['data' => $results],
    HttpStatusCode::HTTP_OK
);

// In your admin controller:
$response->prepare($request);
if ($request->isAdminArea()) {
    $headerManager->applyAdminSecurityHeaders();
}

// In your asset controller:
$response = new Response(
    file_get_contents($assetPath),
    HttpStatusCode::HTTP_OK,
    ['Content-Type' => $mimeType]
);
$headerManager->applyStaticAssetHeaders($assetPath);