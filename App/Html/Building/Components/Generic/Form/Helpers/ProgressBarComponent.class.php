<?php

declare(strict_types=1);

class ProgressBarComponent implements StandAloneComponentInterface
{
    public function __construct(
        private HtmlBuilder $htmlBuilder,
        private IconBuilder $iconBuilder,
    ) {
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        if ($params === null || !is_array($params) || empty($params)) {
            return null;
        }

        $steps = $params['steps'] ?? [];
        $activeStepId = $params['activeStepId'] ?? null;

        if (empty($steps)) {
            return null;
        }

        $html = $this->htmlBuilder;
        $stepCount = count($steps);
        $activeIndex = $this->getActiveIndex($steps, $activeStepId);

        $container = $html->tag('div')
            ->class('progress')
            ->role('progressbar')
            ->attr('aria-valuenow', $activeIndex + 1)
            ->attr('aria-valuemin', 1)
            ->attr('aria-valuemax', $stepCount);

        foreach ($steps as $index => $step) {
            $stepNumber = $index + 1;
            $isActive = $step->getId() === $activeStepId;
            $isCompleted = $index < $activeIndex;

            $stepWrapper = $html->tag('div')
                ->class('progress-step')
                ->attr('data-step', $stepNumber);

            if ($isActive) {
                $stepWrapper->class('progress-step--active');
            }
            if ($isCompleted) {
                $stepWrapper->class('progress-step--completed');
            }

            // Step content
            $stepContent = $html->tag('div')->class('progress-step__content');

            // Icon wrapper
            $iconWrapper = $html->tag('div')->class('progress-step__content--icon-wrapper');

            if ($step->getIcon()) {
                $iconWrapper->add(
                    $this->iconBuilder->createIcon(
                        icon: 'icon-' . $step->getIcon(),
                        ariaLabel: $step->getTitle(),
                        iconClass: ['icon--' . $step->getIcon()],
                    ),
                );
            }

            // Step number
            $iconWrapper->add(
                $html->tag('span')
                    ->class('number')
                    ->content((string) $stepNumber),
            );

            $stepContent->add($iconWrapper);

            // Text wrapper
            $textWrapper = $html->tag('div')->class('progress-step__content--text-wrapper');
            $textWrapper->add(
                $html->tag('span')
                    ->class('label')
                    ->content('Step ' . $stepNumber),
            );
            $textWrapper->add(
                $html->tag('span')
                    ->class('title')
                    ->content($step->getTitle()),
            );

            if ($step->getDescription()) {
                $textWrapper->add(
                    $html->tag('span')
                        ->class('description')
                        ->content($step->getDescription()),
                );
            }

            $stepContent->add($textWrapper);
            $stepWrapper->add($stepContent);

            $container->add($stepWrapper);
        }

        return $container;
    }

    private function getActiveIndex(array $steps, ?string $activeStepId): int
    {
        if ($activeStepId === null) {
            return 0;
        }

        foreach ($steps as $index => $step) {
            if ($step->getId() === $activeStepId) {
                return $index;
            }
        }
        return 0;
    }
}