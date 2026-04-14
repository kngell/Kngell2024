<?php

declare(strict_types=1);

abstract class AbstractSectionProvider implements SectionProviderInterface
{
    public function __construct(
        protected IconBuilder $iconBuilder,
    ) {
    }

    abstract public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void;

    public function register(HtmlSectionManagerInterface $manager, array $sections, array $registeredKeys): void
    {
        $manager->reset();
        foreach ($sections as $expectedKey => $section) {
            $actualKey = $section->getKey();

            if ($actualKey !== $expectedKey) {
                throw new LogicException(
                    'Section key mismatch for ' . get_class($section) .
                    ": expected '{$expectedKey}', got '{$actualKey}'",
                );
            }

            $manager->registerSection($section);
            $registeredKeys[] = $actualKey;
        }
    }
}