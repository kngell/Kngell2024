<?php

declare(strict_types=1);

class CheckoutOptionsSection extends AbstractBaseHtmlSection
{
    private const string INPUT_NAME = 'checkoutType';
    private const string DEFAULT_OPTION = 'guest';

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly FieldIdGenerator $idGenerator,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        return $this->buildOptionsComponent();
    }

    #[Override]
    public function getKey(): string
    {
        return CheckoutSection::OPTIONS->value;
    }

    private function buildOptionsComponent(): AbstractHtmlComponent
    {
        $config = $this->createOptionsConfig();

        return (new SelectableOptionComponent(
            $this->htmlBuilder,
            $this->iconBuilder,
            $this->idGenerator,
            $config,
            $this->buttonBuilder,
        ))->build();
    }

    private function createOptionsConfig(): SelectableOptionConfig
    {
        return new SelectableOptionConfig(
            selectableOptionClass: ['checkout-options'],
            selectableOptionsTitle: 'How would you like to checkout?',
            subtitle: 'Choose the option that works best for you.',
            headerClass: ['checkout-options__header'],
            selectableOptionTitleClass: ['checkout-options__title'],
            subtitleClass: ['checkout-options__subtitle'],
            fieldsetClass: ['checkout-options__fieldset'],
            legendTitle: 'Choose checkout option',
            optionWrapperClass: ['option-card'],
            optionHeaderClass: ['option-card__header'],
            optionInfoClass: ['option-card__info'],
            infoTitleClass: ['option-card__title'],
            infoDescriptionClass: ['option-card__description'],
            infoIconsClass: ['option-card__icon'],
            options: [
                $this->createGuestOption(),
                $this->createLoginOption(),
            ],
        );
    }

    private function createGuestOption(): SelectableOptionDto
    {
        return new SelectableOptionDto(
            title: 'Continue as guest',
            radioName: self::INPUT_NAME,
            radioValue: 'guest',
            description: 'No account needed. Fastest way to place your order.',
            isDefault: true,
            icons: ['🛒'],
            optionClass: ['option-card--guest'],
            optionTitleClass: ['option-card__title--guest'],
            optionContentClass: ['option-card__description--guest'], // Optional
            optionId: 'guest-option',
            isWrappedRadio: true,
        );
    }

    private function createLoginOption(): SelectableOptionDto
    {
        return new SelectableOptionDto(
            title: 'Sign in or create an account',
            radioName: self::INPUT_NAME,
            radioValue: 'login',
            description: 'Track orders, save addresses and get faster checkout later.',
            icons: ['👤'],
            optionClass: ['option-card--login'],
            optionTitleClass: ['option-card__title--login'],
            optionContentClass: ['option-card__description--login'], // Optional
            optionId: 'login-option',
            isWrappedRadio: true,
        );
    }
}