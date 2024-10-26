<?php

declare(strict_types=1);

class MimeTypeGuessDelegator implements MimeTypesGuesserInterface
{
    private static ?self $instance = null;

    /**
     * @var MimeTypesGuesserInterface
     */
    private array $guessers;

    private function __construct()
    {
        $this->guessers = [];
        $this->registerguesser(new MimeTypeGuesserFromConstants());
        $this->registerguesser(new FileInfoMimeTypesGuesser());
    }

    /**
     * Get the value of instance.
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function registerguesser(MimeTypesGuesserInterface $guesser) : void
    {
        $this->guessers[] = $guesser;
    }

    public function isSupported(): bool
    {
        /**
         * @var MimeTypesGuesserInterface $guesser
         */
        foreach ($this->guessers as $guesser) {
            if ($guesser->isSupported()) {
                return true;
            }
        }
        return false;
    }

    public function guessMimeType(string $path): ?string
    {
        /**
         * @var MimeTypesGuesserInterface $guesser
         */
        foreach ($this->guessers as $guesser) {
            if (! $guesser->isSupported()) {
                continue;
            }
            $mimetype = $guesser->guessMimeType($path);
            if ($mimetype !== null) {
                return $mimetype;
            }
        }
        return null;
    }

    public function guessExtensionByMimeType(string $mimeType) : array
    {
        $mimeType = strtolower($mimeType);
        if (! isset(MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$mimeType])) {
            return [];
        }
        return MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$mimeType];
    }
}
