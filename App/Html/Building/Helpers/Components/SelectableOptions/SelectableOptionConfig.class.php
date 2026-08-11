<?php

declare(strict_types=1);

class SelectableOptionConfig
{
    // ─── Base Classes ──────────────────────────────────────────────
    private const array BASE_SELECTABLE_CLASSES = ['selectable-options'];
    private const array BASE_TITLE_CLASSES = ['selectable-options__title'];
    private const array BASE_SUBTITLE_CLASSES = ['selectable-options__subtitle'];
    private const array BASE_WRAPPER_CLASSES = ['selectable-options__option'];
    private const array BASE_FIELDSET_CLASSES = ['selectable-options__fieldset'];
    private const array BASE_LEGEND_CLASSES = ['selectable-options__legend', 'sr-only'];
    private const array BASE_HEADER_CLASSES = ['selectable-options__header'];
    private const array BASE_OPTION_HEADER_CLASSES = ['selectable-options__option-header'];
    private const array BASE_OPTION_CONTENT_CLASSES = ['selectable-options__option-content'];
    private const array BASE_OPTION_INFO_CLASSES = ['selectable-options__option-info'];
    private const array BASE_INFO_TITLE_CLASSES = ['selectable-options__info-title'];
    private const array BASE_INFO_DESCRIPTION_CLASSES = ['selectable-options__info-description'];
    private const array BASE_INFO_ICONS_CLASSES = ['selectable-options__info-icons'];
    private const array BASE_SECURITY_CLASSES = ['selectable-options__security'];
    private const array BASE_SECURITY_ICON_CLASSES = ['selectable-options__security-icon'];
    private const array BASE_GROUP_CLASSES = ['selectable-options__group'];
    private const array BASE_BENEFITS_CLASSES = ['selectable-options__benefits'];
    private const array BASE_CARD_CLASSES = ['selectable-options__card'];
    private const array BASE_SECURITY_TEXT_CLASSES = ['security-text'];
    private const array BASE_SECURITY_ICON_CONTAINER_CLASSES = ['security-icon-container'];

    public function __construct(
        // ─── Container ──────────────────────────────────────────────
        public readonly ?string $selectableOptionTag = 'section',
        public readonly array $selectableOptionClass = [],
        public readonly ?string $selectableOptionId = null,
        public readonly array $attributes = [],

        // ─── Header ──────────────────────────────────────────────────
        public readonly ?string $selectableOptionsTitle = null,
        public readonly ?string $subtitle = null,
        public readonly array $headerClass = [],
        public readonly array $selectableOptionTitleClass = [],
        public readonly array $subtitleClass = [],

        // ─── Fieldset ────────────────────────────────────────────────
        public readonly array $fieldsetClass = [],
        public readonly array $legendClass = [],
        public readonly string $legendTitle = 'Choose an option',

        // ─── OPTIONS - ALL EMPTY ─────────────────────────────────────
        // Classes come from DTO (Single Source of Truth)
        public readonly array $optionWrapperClass = [],
        public readonly array $optionHeaderClass = [],
        public readonly array $optionContentClass = [],
        public readonly array $optionInfoClass = [],
        public readonly array $infoTitleClass = [],
        public readonly array $infoDescriptionClass = [],
        public readonly array $infoIconsClass = [],
        public readonly array $options = [],

        // ─── Security ──────────────────────────────────────────────────
        public readonly bool $includeSecurity = false,
        public readonly ?string $securityText = null,
        public readonly array $securityClass = [],
        public readonly array $securityIconClass = [],
        public readonly array $securityTextClass = [],
        public readonly array $securityIconContainerClass = [],

        // ─── Card Layout ──────────────────────────────────────────────
        public readonly bool $asCards = false,
        public readonly array $cardClass = [],
        public readonly array $selectedClass = ['selected'],

        // ─── Icons ────────────────────────────────────────────────────
        public readonly bool $useSvgIcons = true,
        public readonly ?array $iconClass = null,
        public readonly ?IconConfig $defaultIconConfig = null,

        // ─── Benefits ──────────────────────────────────────────────────
        public readonly array $benefitsClass = [],
        public readonly array $groupClass = [],

        // ─── Expandable ───────────────────────────────────────────────
        public readonly bool $expandableContent = false,
    ) {
    }

    // ─── Getter Methods ──────────────────────────────────────────────

    public function getSelectableOptionClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_SELECTABLE_CLASSES, $this->selectableOptionClass, $extraClasses);
    }

    public function getSelectableOptionTitleClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_TITLE_CLASSES, $this->selectableOptionTitleClass, $extraClasses);
    }

    public function getSubtitleClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_SUBTITLE_CLASSES, $this->subtitleClass, $extraClasses);
    }

    public function getOptionWrapperClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_WRAPPER_CLASSES, $this->optionWrapperClass, $extraClasses);
    }

    public function getFieldsetClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_FIELDSET_CLASSES, $this->fieldsetClass, $extraClasses);
    }

    public function getLegendClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_LEGEND_CLASSES, $this->legendClass, $extraClasses);
    }

    public function getHeaderClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_HEADER_CLASSES, $this->headerClass, $extraClasses);
    }

    public function getOptionHeaderClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_OPTION_HEADER_CLASSES, $this->optionHeaderClass, $extraClasses);
    }

    public function getOptionContentClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_OPTION_CONTENT_CLASSES, $this->optionContentClass, $extraClasses);
    }

    public function getOptionInfoClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_OPTION_INFO_CLASSES, $this->optionInfoClass, $extraClasses);
    }

    public function getInfoTitleClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_INFO_TITLE_CLASSES, $this->infoTitleClass, $extraClasses);
    }

    public function getInfoDescriptionClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_INFO_DESCRIPTION_CLASSES, $this->infoDescriptionClass, $extraClasses);
    }

    public function getInfoIconsClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_INFO_ICONS_CLASSES, $this->infoIconsClass, $extraClasses);
    }

    public function getSecurityClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_SECURITY_CLASSES, $this->securityClass, $extraClasses);
    }

    public function getSecurityIconClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_SECURITY_ICON_CLASSES, $this->securityIconClass, $extraClasses);
    }

    public function getBenefitsClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_BENEFITS_CLASSES, $this->benefitsClass, $extraClasses);
    }

    public function getGroupClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_GROUP_CLASSES, $this->groupClass, $extraClasses);
    }

    public function getCardClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_CARD_CLASSES, $this->cardClass, $extraClasses);
    }

    public function getSecurityTextClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_SECURITY_TEXT_CLASSES, $this->securityTextClass, $extraClasses);
    }

    public function getSecurityIconContainerClasses(array $extraClasses = []): array
    {
        return array_merge(self::BASE_SECURITY_ICON_CONTAINER_CLASSES, $this->securityIconContainerClass, $extraClasses);
    }
}