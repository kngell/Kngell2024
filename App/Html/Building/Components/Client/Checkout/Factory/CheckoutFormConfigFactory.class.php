<?php

declare(strict_types=1);

final class CheckoutFormConfigFactory extends AbstractFormConfigFactory
{
    public function __construct(
        private ProgressBarComponent $progressBar,
        private StepNavigationComponent $stepNavigation,
    ) {
    }

    public function buildSections(): array
    {
        return [
            CheckoutOptionsSection::class,
            CheckoutAddressSection::class,
            CheckoutOrderSummarySection::class,
            CheckoutShippingMethodSection::class,
            CheckoutPaymentMethodSection::class,
            // ReviewSection::class,
        ];
    }

    protected function getLayoutBuilder(): ?FormLayoutInterface
    {
        return new SteppedFormLayout(
            $this->stepConfig(),
            $this->sectionGroupManager(),
            $this->progressBar,
            $this->stepNavigation,
        );
    }

    protected function showFormHeader(): bool
    {
        return false;
    }

    protected function isFooterEnabled(): bool
    {
        return false;
    }

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::CHECKOUT->value,
            displayName: 'Checkout',
            plural: EntityKey::CHECKOUT->getPlural(),
            basePath: EntityKey::CHECKOUT->getBasePath(),
        );
    }

    protected function getHiddenFields(): array
    {
        return [
        ];
    }

    protected function getAssets(): array
    {
        return [
            'css' => [
                'css/frontend/ecommerce/pages/checkout',
            ],
            'js' => 'js/frontend/ecommerce/pages/checkout',
            'sectionClass' => 'checkout',
        ];
    }

    protected function getDisplayKey(): ?string
    {
        return 'checkoutForm';
    }

    protected function getFormContainerClass(): array
    {
        return ['checkout__body'];
    }

    protected function getFooterClass(): array
    {
        return ['checkout__footer'];
    }

    protected function formId(): string
    {
        return 'checkoutForm';
    }

    protected function formName(): string
    {
        return 'checkout-frm';
    }

    protected function formClass(): array
    {
        return ['checkout__form'];
    }

    protected function defaultInputLayoutName(): ?string
    {
        return 'input';
    }

    protected function getFieldLayouts(): array
    {
        return [
            'input' => new FieldLayout(),
            'checkbox' => new FieldCheckboxLayout(),
        ];
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new NativeSelectFieldHandler(),
        ];
    }

    #[Override]
    protected function customAttributes(): array
    {
        return [
            'data-validate' => 'true',
            'data-validation-rules' => 'checkoutRules',
            'data-form-type' => EntityKey::CHECKOUT->value,
        ];
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        return SectionGroupManager::create()
            ->addGroup(
                SectionGroup::create('checkout-options')
                    ->setSectionKeys([
                        CheckoutSection::OPTIONS->value,
                    ])
                    ->setPosition('left'),
            )
            ->addGroup(
                SectionGroup::create('checkout-address')
                    ->setSectionKeys([
                        CheckoutSection::ADDRESS->value,
                    ])
                    ->setPosition('left'),
            )
             ->addGroup(
                 SectionGroup::create('order-summary')
                    ->setSectionKeys([
                        CheckoutSection::SUMMARY->value,
                    ])
                    ->setPosition('right')
                    ->setWrapperClass(['layout__summary', 'order-summary'])
                    ->setWrapperTag('aside'),
             )->addGroup(
                 SectionGroup::create('checkout-shipping')
                  ->setSectionKeys([
                      CheckoutSection::SHIPPING->value,
                  ])
                ->setPosition('left'),
             )->addGroup(
                 SectionGroup::create('checkout-payment')
                ->setSectionKeys([
                    CheckoutSection::PAYMENT->value,
                ])
                ->setPosition('left'),
             );
        // ->addGroup(
        //     SectionGroup::create('checkout-review')
        //         ->setSectionKeys([
        //             'review_order',
        //         ])
        //         ->setPosition('full')
        //         ->setWrapperClass(['checkout__step-content--full']),
        // )
        // ->addGroup(
        //     SectionGroup::create('order-summary')
        //         ->setSectionKeys([
        //             'order_summary',
        //         ])
        //         ->setPosition('right')
        //         ->setWrapperClass(['checkout__right', 'order-summary']),
        // );
    }

    protected function stepConfig(): ?StepConfig
    {
        return StepConfig::create()
             ->setRadioGroupName('step')
             ->setProgressContainerClass(['checkout__progress'])
             ->setContentContainerClass(['checkout-step__content'])
             ->setNavigationClass(['checkout__step-nav'])
             ->setLeftColumnClass(['layout__main'])
             ->setWorkflowClass(['checkout__workflow'])
             ->addStep(
                 (new StepItem('step1', 'Options'))
                     ->setDescription('Choose checkout type')
                     ->setIcon('options')
                     ->setState('active')
                     ->setPriority(1)
                     ->addSectionGroup('checkout-options'),
             )
             ->addStep(
                 (new StepItem('step2', 'Address'))
                     ->setDescription('Enter shipping address')
                     ->setIcon('address')
                     ->setPriority(2)
                     ->addSectionGroup('checkout-address'),
             )->addStep(
                 (new StepItem('step3', 'Shipping'))
                ->setDescription('Choose shipping method')
                ->setIcon('shipping')
                ->setPriority(3)
                ->addSectionGroup('checkout-shipping'),
             )->addStep(
                 (new StepItem('step4', 'Payment'))
                ->setDescription('Select payment method')
                ->setIcon('payment')
                ->setPriority(4)
                ->addSectionGroup('checkout-payment')
                ->addSectionGroup('order-summary'),
             );
        // ->addStep(
        //     (new StepItem('step4', 'Payment'))
        //         ->setDescription('Select payment method')
        //         ->setIcon('payment')
        //         ->setPriority(4)
        //         ->addSectionGroup('checkout-payment')
        //         ->addSectionGroup('order-summary'),
        // )
        // ->addStep(
        //     (new StepItem('step5', 'Review'))
        //         ->setDescription('Review and place order')
        //         ->setIcon('review')
        //         ->setPriority(5)
        //         ->addSectionGroup('checkout-review'),
        // );
    }
}