<?php

declare(strict_types=1);

abstract class BaseSelectOptionsServices
{
    public function __construct(protected Object $model, protected string $label, protected string $entityName)
    {
    }

    public function getActiveOptions(): array
    {
        try {
            $results = $this->model->getActiveOptions();

            $options = ['' => '-- Select a Status --'];

            foreach ($results as $result) {
                $entityName = $this->entityName;
                if ($result instanceof $entityName) {
                    $options[$status->getId()] = $this->formatLabel($status);
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('ProductStatusService: Failed to load Product Status - ' . $e->getMessage());
            return $this->getDefaultOption();
        }
    }
}