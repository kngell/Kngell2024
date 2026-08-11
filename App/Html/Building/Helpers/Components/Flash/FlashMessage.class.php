<?php

declare(strict_types=1);

class FlashMessage implements StandAloneComponentInterface
{
    /** @var list<FlashMessageDTO> */
    private array $messages = [];

    public function __construct(
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $iconBuilder,
        private readonly HtmlEscaper $escaper,
        private readonly FlashConfigDTO $config,
    ) {
    }

    /**
     * Add a single flash message to be rendered.
     */
    public function add(FlashMessageDTO $message): self
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Bulk-set messages (replaces any previously added).
     *
     * @param list<FlashMessageDTO> $messages
     */
    public function withMessages(array $messages): self
    {
        $this->messages = array_values($messages);
        return $this;
    }

    /**
     * Whether the component has any messages to render.
     */
    public function hasMessages(): bool
    {
        return !empty($this->messages);
    }

    /**
     * Reset internal state (useful when reusing a single instance).
     */
    public function reset(): self
    {
        $this->messages = [];
        return $this;
    }

    #[Override]
    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        if (empty($this->messages)) {
            return null;
        }

        $containerClass = $this->config->useToast
            ? 'flash-container flash-container--toast'
            : 'flash-container';

        $container = $this->builder->div()
            ->class($containerClass)
            ->attribute('aria-live', 'polite')
            ->attribute('aria-atomic', 'true');

        $nodes = [];
        foreach ($this->messages as $message) {
            $node = $this->buildMessage($message);
            if ($node !== null) {
                $nodes[] = $node;
            }
        }

        return $container->add(...$nodes);
    }

    private function buildMessage(FlashMessageDTO $msg): ?AbstractHtmlComponent
    {
        if (empty($msg->message)) {
            return null;
        }

        $html = $this->builder;

        $isError = $msg->isError();
        $iconId = $this->config->iconFor($msg->type);
        $role = $isError ? 'alert' : 'status';
        $ariaLive = $isError ? 'assertive' : 'polite';

        // Root flash element
        $messageContainer = $html->div()
            ->class('flash', 'flash--' . $msg->type, 'flash-message-js')
            ->role($role)
            ->attribute('aria-live', $ariaLive)
            ->attribute('aria-atomic', 'true');

        if (!empty($msg->duration)) {
            $messageContainer->attribute('data-flash-duration', (string) $msg->duration);
        }

        // 1. Status icon
        $messageContainer->add(
            $html->div()
                ->class('flash__icon-container')
                ->add(
                    $this->iconBuilder
                        ->createIcon($iconId, 'Flash Icon', ['icon'])
                        ->attribute('aria-hidden', 'true'),
                ),
        );

        // 2. Body
        $flashBody = $html->div()->class('flash__body');

        if (!empty($msg->title)) {
            $flashBody->add(
                $html->tag('span')
                    ->class('flash__title')
                    ->content($this->escaper->escape($msg->title)),
            );
        }

        $flashBody->add(
            $html->tag('span')
                ->class('flash__text', 'flash-message-js__text')
                ->content($this->escaper->escape($msg->message)),
        );

        $messageContainer->add($flashBody);

        // 3. Close button
        if ($msg->dismissible) {
            $messageContainer->add(
                $html->button()
                    ->attribute('type', 'button')
                    ->class('flash__close')
                    ->attribute('aria-label', 'Close')
                    ->attribute('data-flash-dismiss', 'true')
                    ->add(
                        $this->iconBuilder
                            ->createIcon('icon-close', 'Close', ['icon'])
                            ->attribute('aria-hidden', 'true'),
                    ),
            );
        }

        // 4. Progress bar
        if ($msg->showProgress) {
            $messageContainer->add(
                $html->tag('span')
                    ->class('flash__progress')
                    ->attribute('aria-hidden', 'true'),
            );
        }

        return $messageContainer;
    }
}