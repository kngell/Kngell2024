<?php

declare(strict_types=1);

abstract class AbstractSteppedFormLayout
{
    protected array $contentContainerClass = ['step-content'];
    protected array $leftColumnClass = ['checkout__left'];
    protected array $workflowClass = ['checkout__workflow'];

    public function __construct(
        protected StepConfig $stepConfig,
        protected SectionGroupManager $sectionGroupManager,
        protected ProgressBarComponent $progressBar,
        protected StepNavigationComponent $navigation,
    ) {
        if (!empty($stepConfig->getContentContainerClass())) {
            $this->contentContainerClass = $stepConfig->getContentContainerClass();
        }
        if (!empty($stepConfig->getLeftColumnClass())) {
            $this->leftColumnClass = $stepConfig->getLeftColumnClass();
        }
        if (!empty($stepConfig->getWorkflowClass())) {
            $this->workflowClass = $stepConfig->getWorkflowClass();
        }
    }

    protected function getActiveStepConfig(?StepConfig $config): StepConfig
    {
        return $config ?? $this->stepConfig;
    }

    protected function buildSteppedLayout(
        StepConfig $activeStepConfig,
        array $sectionGroups,
        HtmlBuilder $builder,
    ): array {
        $steps = $activeStepConfig->getSteps();
        $components = [];

        // 1. Build hidden radio inputs for step navigation
        $radioComponents = $this->buildStepRadios($steps, $builder, $activeStepConfig);
        $components = array_merge($components, $radioComponents);

        // 2. Build the workflow wrapper with configurable class
        $workflowWrapper = $builder->tag('div')
            ->class(...$this->workflowClass);

        // 3. Build progress indicator inside workflow
        $activeStepId = $this->stepConfig->getActiveStep();
        $progressBar = $this->progressBar->build([
            'steps' => $steps,
            'activeStepId' => $activeStepId,
        ]);
        if ($progressBar) {
            $workflowWrapper->add($progressBar);
        }

        // 4. Build the content wrapper inside workflow
        $contentWrapper = $builder->tag('div')
            ->class('layout');

        // 5. Build left column with all steps
        $leftColumn = $this->buildLeftColumn($steps, $sectionGroups, $builder);
        $contentWrapper->add($leftColumn);

        // 6. Build right column with order summary (ONLY ONCE)
        $rightColumn = $this->buildRightColumn($sectionGroups, $builder);
        if ($rightColumn) {
            $contentWrapper->add($rightColumn);
        }

        $workflowWrapper->add($contentWrapper);
        $components[] = $workflowWrapper;

        return $components;
    }

    protected function buildLeftColumn(
        array $steps,
        array $sectionGroups,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent {
        $leftColumn = $builder->tag('section')
            ->class(...$this->leftColumnClass);

        foreach ($steps as $step) {
            if ($step->isDisabled()) {
                continue;
            }

            $stepDiv = $this->buildStepContent($step, $steps, $sectionGroups, $builder);
            if ($stepDiv) {
                $leftColumn->add($stepDiv);
            }
        }

        return $leftColumn;
    }

    protected function buildStepContent(
        StepItem $step,
        array $steps,
        array $sectionGroups,
        HtmlBuilder $builder,
    ): ?AbstractHtmlComponent {
        $stepDiv = $builder->tag('div')
            ->class('checkout__step', ...$step->getClass())
            ->attr('data-step', $this->getStepNumber($steps, $step))
            ->role('tabpanel')
            ->attr('id', 'checkout-step-' . $step->getId())
            ->attr('aria-label', $step->getTitle() . ' Step');

        if ($step->isActive()) {
            $stepDiv->class('checkout__step--active');
        }

        if (!empty($step->getAttributes())) {
            $stepDiv->custom($step->getAttributes());
        }

        // Add only left-positioned sections directly to the step (no wrapper)
        foreach ($step->getSectionGroups() as $groupKey) {
            $group = $this->sectionGroupManager->getGroup($groupKey);
            if (!$group) {
                continue;
            }

            // Skip right-positioned groups - they'll be rendered globally
            if ($group->getPosition() === 'right') {
                continue;
            }

            $sections = $this->getSectionsForGroup($group, $sectionGroups, $groupKey);
            if (empty($sections)) {
                continue;
            }

            $flattenedSections = $this->flattenSections($sections);
            if (!empty($flattenedSections)) {
                foreach ($flattenedSections as $section) {
                    $stepDiv->add($section);
                }
            }
        }

        // Add navigation directly to the step (no wrapper)
        $navigation = $this->buildStepNavigation($step, $steps);
        if ($navigation) {
            $stepDiv->add($navigation);
        }

        return $stepDiv;
    }

    protected function buildRightColumn(
        array $sectionGroups,
        HtmlBuilder $builder,
    ): ?AbstractHtmlComponent {
        // Get the order-summary group directly from the section group manager
        $orderSummaryGroup = $this->sectionGroupManager->getGroup('order-summary');

        if (!$orderSummaryGroup) {
            return null;
        }

        // Get sections for this group
        $sections = $this->getSectionsForGroup(
            $orderSummaryGroup,
            $sectionGroups,
            'order-summary',
        );

        if (empty($sections)) {
            return null;
        }

        $flattenedSections = $this->flattenSections($sections);
        if (empty($flattenedSections)) {
            return null;
        }

        // Build the right column with the group's wrapper configuration
        $wrapperTag = $orderSummaryGroup->getWrapperTag();
        $wrapperClass = $orderSummaryGroup->getWrapperClass();

        $rightColumn = $builder->tag($wrapperTag)
            ->class(...$wrapperClass);

        if (!empty($orderSummaryGroup->getAttributes())) {
            $rightColumn->custom($orderSummaryGroup->getAttributes());
        }

        // Add all sections
        foreach ($flattenedSections as $section) {
            $rightColumn->add($section);
        }

        return $rightColumn;
    }

    protected function getStepNumber(array $steps, StepItem $currentStep): int
    {
        foreach ($steps as $index => $step) {
            if ($step->getId() === $currentStep->getId()) {
                return $index + 1;
            }
        }
        return 1;
    }

    protected function getSectionsForGroup(
        SectionGroup $group,
        array $sectionGroups,
        string $groupKey,
    ): array {
        $sections = [];
        foreach ($group->getSectionKeys() as $sectionKey) {
            if (isset($sectionGroups[$sectionKey])) {
                $sections[] = $sectionGroups[$sectionKey];
            }
        }
        return $sections;
    }

    protected function flattenSections(array $sections): array
    {
        $result = [];
        foreach ($sections as $section) {
            if (is_array($section)) {
                $result = array_merge($result, $this->flattenSections($section));
            } else {
                $result[] = $section;
            }
        }
        return $result;
    }

    protected function buildStepRadios(
        array $steps,
        HtmlBuilder $builder,
        StepConfig $config,
    ): array {
        $radios = [];
        $radioGroupName = $config->getRadioGroupName();
        $activeStepId = $config->getActiveStep();

        $enabledSteps = array_values(array_filter($steps, fn ($s) => !$s->isDisabled()));
        $lastStepIndex = count($enabledSteps) - 1;

        $enabledIndex = 0;
        foreach ($steps as $step) {
            if ($step->isDisabled()) {
                continue;
            }

            $radio = $builder->input('radio')
                ->name($radioGroupName)
                ->id($step->getId())
                ->value($step->getId())
                ->hidden();

            if ($step->getId() === $activeStepId) {
                $radio->checked();
            }

            // Flag the final step radio!
            if ($enabledIndex === $lastStepIndex) {
                $radio->attr('data-final-step', 'true');
            }

            $radios[] = $radio;
            $enabledIndex++;
        }

        return $radios;
    }
    // protected function buildStepRadios(
    //     array $steps,
    //     HtmlBuilder $builder,
    //     StepConfig $config,
    // ): array {
    //     $radios = [];
    //     $radioGroupName = $config->getRadioGroupName();
    //     $activeStepId = $config->getActiveStep();

    //     foreach ($steps as $step) {
    //         if ($step->isDisabled()) {
    //             continue;
    //         }

    //         $radio = $builder->input('radio')
    //             ->name($radioGroupName)
    //             ->id($step->getId())
    //             ->value($step->getId())
    //             ->hidden();

    //         if ($step->getId() === $activeStepId) {
    //             $radio->checked();
    //         }

    //         if ($step->isDisabled()) {
    //             $radio->disabled();
    //         }

    //         $radios[] = $radio;
    //     }

    //     return $radios;
    // }

    protected function buildStepNavigation(
        StepItem $currentStep,
        array $steps,
    ): ?AbstractHtmlComponent {
        $currentIndex = $this->findStepIndex($steps, $currentStep->getId());

        if ($currentIndex === false) {
            return null;
        }

        // Reset navigation
        $this->navigation->reset();
        $this->navigation->setClass($this->stepConfig->getNavigationClass());

        $hasButtons = false;

        // Back button
        if ($currentIndex > 0) {
            $previousStep = $steps[$currentIndex - 1];
            if (!$previousStep->isDisabled()) {
                $this->navigation->addBackButton(
                    $previousStep->getId(),
                    'Back',
                );
                $hasButtons = true;
            }
        }

        // Next button
        if ($currentIndex < count($steps) - 1) {
            $nextStep = $steps[$currentIndex + 1];
            if (!$nextStep->isDisabled()) {
                $this->navigation->addNextButton(
                    $nextStep->getId(),
                    'Continue to ' . $nextStep->getTitle(),
                );
                $hasButtons = true;
            }
        }

        // Submit button
        if ($currentIndex === count($steps) - 1) {
            $this->navigation->addSubmitButton('Place Order');
            $hasButtons = true;
        }

        return $hasButtons ? $this->navigation->build() : null;
    }

    protected function findStepIndex(array $steps, string $stepId): int|false
    {
        foreach ($steps as $index => $step) {
            if ($step->getId() === $stepId) {
                return $index;
            }
        }
        return false;
    }
}