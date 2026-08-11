<?php

declare(strict_types=1);

/**
 * @extends AbstractCollectionEntityService<UserAddress>
 */
class ShippingAddressService extends AbstractSingleEntityService
{
    public function __construct(
        private UserAddressModel $model,
        UserAddressCacheManagerFactory $factory,
        private readonly HtmlSectionPresentationService $presenter,
        private readonly UserContext $userContext,
    ) {
        parent::__construct($factory->create());
    }

    public function getUserAddress(): ?UserAddressResponse
    {
        $user = $this->userContext->currentUser();
        if ($user !== null) {
            $id = $user->getUserId();
            return $this->getForPage((string) $id);
        }
        return null;
    }

    #[Override]
    public function getDefaultResponse(): UserAddressResponse
    {
        return $this->createResponse(
            image: $this->getDefaultImageData(),
            entity: null,
            isDefault: true,
        );
    }

    #[Override]
    protected function fetchEntityFromDb(string $id): ?Entity
    {
        $conditions = array_merge(
            ['user_id', $id,
            ],
            [
                ConditionListMode::MODE_FRONTEND->value => true,
            ],
        );
        $result = $this->model->one($conditions);
        return $result->isSuccess() ? $result->asClass() : null;
    }

    #[Override]
    protected function fetchEntityByIdFromDb(string $id): ?Entity
    {
        return $this->fetchEntityFromDb($id);
    }

    #[Override]
    protected function buildResponsiveImage(Entity $entity): array
    {
        return [];
    }

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): UserAddressResponse
    {
        // dd($entity);
        return new UserAddressResponse($image, $entity, $this->presenter, $isDefault);
    }
}