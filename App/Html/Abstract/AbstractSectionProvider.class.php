<?php

declare(strict_types=1);

abstract class AbstractSectionProvider implements SectionProviderInterface
{
    public function __construct(
        protected IconBuilder $iconBuilder,
    ) {
    }

    abstract public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void;

    /**
     * Registers sections with the manager.
     *
     * Supports two formats:
     *
     * Legacy (associative array with explicit keys):
     *   $this->register($manager, [
     *       'heroSection' => new HeroSection(...),
     *   ], $registeredKeys);
     *
     * Modern (simple list, sections define their own key via enum):
     *   $this->register($manager, [
     *       new HeroSection(...),
     *   ]);
     *
     * @param HtmlSectionManagerInterface $manager
     * @param AbstractBaseHtmlSection[]   $sections
     * @param array|null                  $registeredKeys Deprecated. Only used for legacy support.
     */
    public function register(
        HtmlSectionManagerInterface $manager,
        array $sections,
        ?array &$registeredKeys = null,
    ): void {
        $manager->reset();

        $isLegacy = $this->isAssociative($sections);
        $tracked = [];

        foreach ($sections as $expectedKey => $section) {
            $actualKey = $section->getKey();

            if ($isLegacy) {
                if ($actualKey !== $expectedKey) {
                    throw new LogicException(
                        sprintf(
                            'Section key mismatch for %s: expected "%s", got "%s".',
                            get_class($section),
                            $expectedKey,
                            $actualKey,
                        ),
                    );
                }
            }

            if (in_array($actualKey, $tracked, true)) {
                throw new LogicException(
                    sprintf(
                        'Duplicate section key "%s" from %s. Each section must have a unique key.',
                        $actualKey,
                        get_class($section),
                    ),
                );
            }

            $manager->registerSection($section);
            $tracked[] = $actualKey;
        }

        // Maintain legacy by-reference behavior
        if ($registeredKeys !== null) {
            $registeredKeys = $tracked;
        }
    }

    private function isAssociative(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return !array_is_list($array);
    }
}