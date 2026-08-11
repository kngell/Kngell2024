<?php

declare(strict_types=1);

final class StepConfig
{
    /** @var StepItem[] */
    private array $steps = [];

    private array $progressContainerClass = ['form-progress'];
    private array $contentContainerClass = ['step-content'];
    private array $navigationClass = ['form-navigation'];
    private array $leftColumnClass = ['checkout__panel'];
    private array $workflowClass = ['checkout__workflow'];
    private ?string $defaultStepId = null;
    private ?string $activeStepId = null;
    private string $radioGroupName = 'form-step';

    public function addStep(StepItem $step): self
    {
        $this->steps[$step->getId()] = $step;

        if ($step->isActive() && $this->defaultStepId === null) {
            $this->defaultStepId = $step->getId();
        }

        return $this;
    }

    public function getActiveStep(): ?string
    {
        if ($this->activeStepId !== null) {
            return $this->activeStepId;
        }

        if ($this->defaultStepId !== null) {
            return $this->defaultStepId;
        }

        $firstStep = $this->getFirstStep();
        return $firstStep ? $firstStep->getId() : null;
    }

    public function setActiveStep(string $stepId): self
    {
        if (isset($this->steps[$stepId])) {
            $this->activeStepId = $stepId;
        }
        return $this;
    }

    public function removeStep(string $stepId): self
    {
        unset($this->steps[$stepId]);
        return $this;
    }

    public function getStep(string $stepId): ?StepItem
    {
        return $this->steps[$stepId] ?? null;
    }

    public function getSteps(): array
    {
        // Sort by priority
        $steps = $this->steps;
        usort($steps, fn (StepItem $a, StepItem $b) => $a->getPriority() <=> $b->getPriority());
        return $steps;
    }

    public function hasSteps(): bool
    {
        return !empty($this->steps);
    }

    public function getDefaultStepId(): ?string
    {
        return $this->defaultStepId;
    }

    public function setDefaultStepId(string $stepId): self
    {
        if (isset($this->steps[$stepId])) {
            $this->defaultStepId = $stepId;
        }
        return $this;
    }

    public function setProgressContainerClass(array $class): self
    {
        $this->progressContainerClass = $class;
        return $this;
    }

    public function getProgressContainerClass(): array
    {
        return $this->progressContainerClass;
    }

    public function setContentContainerClass(array $class): self
    {
        $this->contentContainerClass = $class;
        return $this;
    }

    public function getContentContainerClass(): array
    {
        return $this->contentContainerClass;
    }

    public function setNavigationClass(array $class): self
    {
        $this->navigationClass = $class;
        return $this;
    }

    public function getNavigationClass(): array
    {
        return $this->navigationClass;
    }

    public function setRadioGroupName(string $name): self
    {
        $this->radioGroupName = $name;
        return $this;
    }

    public function getRadioGroupName(): string
    {
        return $this->radioGroupName;
    }

    public function toArray(): array
    {
        $config = [];
        foreach ($this->steps as $id => $step) {
            $config[$id] = $step->toArray();
        }
        return $config;
    }

    /**
     * @return array
     */
    public function getLeftColumnClass(): array
    {
        return $this->leftColumnClass;
    }

    /**
     * @param array $leftColumnClass
     *
     * @return StepConfig
     */
    public function setLeftColumnClass(array $leftColumnClass): StepConfig
    {
        $this->leftColumnClass = $leftColumnClass;

        return $this;
    }

    /**
     * @return array
     */
    public function getWorkflowClass(): array
    {
        return $this->workflowClass;
    }

    /**
     * @param array $workflowClass
     *
     * @return StepConfig
     */
    public function setWorkflowClass(array $workflowClass): StepConfig
    {
        $this->workflowClass = $workflowClass;

        return $this;
    }

    private function getFirstStep(): ?StepItem
    {
        $steps = $this->getSteps();
        return !empty($steps) ? $steps[0] : null;
    }

    public static function create(): self
    {
        return new self();
    }
}