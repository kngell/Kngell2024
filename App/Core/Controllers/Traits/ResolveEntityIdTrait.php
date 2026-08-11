<?php

declare(strict_types=1);
/** @method string getRedirectUrl()
 */
trait ResolveRedirectTrait
{
    private function resolveRedirectUrl(): string
    {
        return $this->getRedirectUrl()
            ?? DeletionFlowConfig::DEFAULT_REDIRECT->value;
    }
}