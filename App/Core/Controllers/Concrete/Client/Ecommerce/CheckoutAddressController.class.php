<?php

declare(strict_types=1);

final class CheckoutAddressController extends AbstractBaseModalController
{
    private bool $isLoggedIn = false;

    public function __construct(
        FormCreatorService $frm,
        private readonly CheckoutAddressModalBuilder $modalBuilder,
        private readonly AddressFormConfigFactory $formFactory,
        private readonly UserContext $userContext,
    ) {
        parent::__construct($frm);
        $this->isLoggedIn = $userContext->isLoggedIn();
    }

    /**
     * @param null|Address $entity
     *
     * @return null|CheckoutAddressDTO
     */
    protected function createDTO(?Entity $entity = null): ?CheckoutAddressDTO
    {
        $cancelRoute = '/checkout#close';
        $deleteRoute = $this->getDeleteRoute();

        if ($entity !== null) {
            $addressType = $entity->isDefaultShipping() ? 'shipping' : 'billing';
            return CheckoutAddressDTO::fromEntity(
                address: $entity,
                cancelRoute: $cancelRoute,
                deleteRoute: $deleteRoute,
                isVisible: true,
                addressType: $addressType,
                isLoggedIn: $this->isLoggedIn,
            );
        }

        // Check if we have guest data to pre-fill
        $guestData = $this->request->get('guest_data', []);
        $addressType = $this->request->get('addressType', 'shipping');

        if (!empty($guestData) && !$this->isLoggedIn) {
            return CheckoutAddressDTO::fromGuestData(
                data: $guestData,
                cancelRoute: $cancelRoute,
                deleteRoute: $deleteRoute,
                addressType: $addressType,
                isVisible: true,
            );
        }

        // New address (guest or logged-in)
        return CheckoutAddressDTO::forNewAddress(
            cancelRoute: $cancelRoute,
            deleteRoute: $deleteRoute,
            addressType: $addressType,
            isLoggedIn: $this->isLoggedIn,
            isVisible: true,
        );
    }

    #[Override]
    protected function getModalBuilder(): ModalFormBuilderInterface
    {
        return $this->modalBuilder;
    }

    #[Override]
    protected function getSaveRoute(): string
    {
        return '/checkout/address/save';
    }

    #[Override]
    protected function getFormFactory(): AbstractFormConfigFactory
    {
        return $this->formFactory;
    }

    protected function getModlIndentifier(): ModalIdentifier
    {
        return ModalIdentifier::CHECKOUT_ADDRESS;
    }

    protected function getEntityType(): string
    {
        return Address::class;
    }

    private function getDeleteRoute(): string
    {
        return '/checkout/address/delete';
    }
}