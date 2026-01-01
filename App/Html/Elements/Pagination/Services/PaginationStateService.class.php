<?php

declare(strict_types=1);

class PaginationStateService
{
    private array $allowedSizes = [3, 5, 10, 25, 30];

    public function getPaginationData(Request $request): PaginationData
    {
        $currentPage = (int) ($request->get('page') ?? 1);
        $currentPage = max(1, $currentPage);

        $recordsPerPage = (int) ($request->get('per_page') ?? 10);
        $defaultPerPage = 10;

        $validatedPerPage = in_array($recordsPerPage, $this->allowedSizes, true)
            ? $recordsPerPage
            : $defaultPerPage;

        $uiAllowedSizes = $this->allowedSizes;
        if (!in_array($validatedPerPage, $uiAllowedSizes, true)) {
            $uiAllowedSizes[] = $validatedPerPage;
            sort($uiAllowedSizes);
        }

        return new PaginationData(
            currentPage: $currentPage,
            recordsPerPage: $validatedPerPage,
            allowedPageSizes: $uiAllowedSizes,
        );
    }
}