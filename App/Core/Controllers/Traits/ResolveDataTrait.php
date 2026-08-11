<?php

declare(strict_types=1);

/** @property Request $request
 * @method string getRedirectUrl()
 * @method string getEntityKeyfield()
 * @method string entityClass()
 */
trait ResolveDataTrait
{
    protected ?BlockType $blockType = null;

    protected function resolveEntityId(): array
    {
        $keyField = $this->getEntityKeyfield();
        $value = $this->request->get($keyField, '');
        if (!empty($value)) {
            return ['key' => $keyField, 'value' => $value];
        }

        $value = $this->request->get('public_id', '');
        if (!empty($value)) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Invalid public_id payload type received.');
            }

            if (!StringUtils::isUuid($value) && !preg_match('/^[a-zA-Z0-9_\-]+$/', $value)) {
                throw new InvalidArgumentException(sprintf(
                    'Security Violation: Malformed public_id string provided for entity class %s.',
                    $this->entityClass(),
                ));
            }

            return ['key' => 'public_id', 'value' => $value];
        }

        return [];
    }

    private function resolveBlockType(?string $blockType = null, ?Request $request = null): void
    {
        $request = $request ?? $this->request;
        $blockType = $blockType ?? $request->get('block_type');

        if (empty($blockType)) {
            $this->blockType = null;
            return;
        }
        $this->blockType = BlockType::tryFrom($blockType);
    }

    private function resolveRedirectUrl(): string
    {
        return $this->getRedirectUrl()
            ?? DeletionFlowConfig::DEFAULT_REDIRECT->value;
    }
}