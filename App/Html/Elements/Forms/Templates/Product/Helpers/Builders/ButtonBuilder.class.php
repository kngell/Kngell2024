<?php

declare(strict_types=1);

class ButtonBuilder
{
    public function build(array $buttonConfig, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $button = $form->button()
            ->type($buttonConfig['type'] ?? 'button')
            ->class(...$this->getButtonClasses($buttonConfig));

        $iconPosition = $buttonConfig['iconPosition'] ?? 'left';
        $this->addButtonContent($button, $buttonConfig, $form, $formInstance, $iconPosition);

        if (isset($buttonConfig['attributes'])) {
            $button->custom($buttonConfig['attributes']);
        }

        return $button;
    }

    private function getButtonClasses(array $buttonConfig): array
    {
        $classes = ['btn'];

        // Add size class
        if (isset($buttonConfig['size'])) {
            $classes[] = 'btn--' . $buttonConfig['size'];
        }

        // Add style class
        if (isset($buttonConfig['style'])) {
            $classes[] = 'btn--' . $buttonConfig['style'];
        }

        // Add additional classes
        if (isset($buttonConfig['class'])) {
            $additionalClasses = is_array($buttonConfig['class'])
                ? $buttonConfig['class']
                : explode(' ', $buttonConfig['class']);
            $classes = array_merge($classes, $additionalClasses);
        }

        return $classes;
    }

    private function addButtonContent(AbstractHtmlComponent $button, array $config, FormBuilder $form, AbstractForm $formInstance, string $iconPosition): void
    {
        $hasIcon = isset($config['icon']);
        $hasLabel = isset($config['label']);

        if ($hasIcon && $hasLabel) {
            if ($iconPosition === 'right') {
                // Label first, then icon
                $button->add(
                    $form->tag('span')->class('btn__label')->content($config['label']),
                );
                $button->add(
                    $form->tag('span')->class('btn__icon')->add(
                        $formInstance->createIcon($form, $config['icon'], $config['ariaLabel'] ?? 'Button'),
                    ),
                );
            } else {
                // Icon first, then label (default)
                $button->add(
                    $form->tag('span')->class('btn__icon')->add(
                        $formInstance->createIcon($form, $config['icon'], $config['ariaLabel'] ?? 'Button'),
                    ),
                );
                $button->add(
                    $form->tag('span')->class('btn__label')->content($config['label']),
                );
            }
        } elseif ($hasIcon) {
            // Icon only
            $button->add(
                $form->tag('span')->class('btn__icon')->add(
                    $formInstance->createIcon($form, $config['icon'], $config['ariaLabel'] ?? 'Button'),
                ),
            );
        } elseif ($hasLabel) {
            // Label only
            $button->add(
                $form->tag('span')->class('btn__label')->content($config['label']),
            );
        }
    }
}
