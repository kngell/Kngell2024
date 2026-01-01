<?php

declare(strict_types=1);

class BooleanPresenter implements TypePresenterInterface
{
    public function __construct(
        private TranslatorServiceInterface $translator,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_bool($value);
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        // Check for custom display options from property attributes
        $style = 'yesno';
        $trueText = null;
        $falseText = null;
        $useIcons = false;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $format = $attributes[0]->newInstance();
                $style = $format->style ?? $style;
                $trueText = $format->trueText ?? null;
                $falseText = $format->falseText ?? null;
                $useIcons = $format->useIcons ?? $useIcons;
            }
        }

        // Use custom texts if provided
        if ($trueText !== null && $falseText !== null) {
            return $value ? $trueText : $falseText;
        }

        // Handle different display styles
        switch ($style) {
            case 'yesno':
                return $value
                    ? $this->translator->translate('common.yes')
                    : $this->translator->translate('common.no');

            case 'truefalse':
                return $value
                    ? $this->translator->translate('common.true')
                    : $this->translator->translate('common.false');

            case 'activeinactive':
                return $value
                    ? $this->translator->translate('common.active')
                    : $this->translator->translate('common.inactive');

            case 'onoff':
                return $value
                    ? $this->translator->translate('common.on')
                    : $this->translator->translate('common.off');

            case 'icon':
                if ($useIcons) {
                    return $value
                        ? '<span class="icon-check text-success" title="' . $this->translator->translate('common.yes') . '"></span>'
                        : '<span class="icon-x text-danger" title="' . $this->translator->translate('common.no') . '"></span>';
                }
                // Fall through to yesno

                // no break
            default:
                return $value
                    ? $this->translator->translate('common.yes')
                    : $this->translator->translate('common.no');
        }
    }
}