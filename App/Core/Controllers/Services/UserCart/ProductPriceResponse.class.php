<?php

declare(strict_types=1);

class ProductPriceResponse extends AbstractBaseEntityResponse
{
    use EntityDisplayTrait;
    use ProductPriceTrait;

    public function __construct(
        array $image,
        private readonly HtmlSectionPresentationService $presenter,
        ?ProductCollection $product,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $product, $isDefault);
    }

    public function getEntity(): ?ProductCollection
    {
        return $this->entity;
    }

    public function getName(): ?string
    {
        return $this->presenter->showField($this->getEntity(), 'name') ?? null;
    }

    public function getImageUrl(): ?string
    {
        $img = $this->getEntity()?->getMainImage();
        if ($img === null) {
            $imgArr = $this->getImage();
            $img = $imgArr['fallback']['src'] ?? null;
        }
        return $img;
    }

    public function getSlug(): ?string
    {
        return $this->presenter->showField($this->getEntity(), 'slug') ?? null;
    }

    public function getWeight(): ?string
    {
        return $this->presenter->showField($this->getEntity(), 'product_weight');
    }

    public function getSku(): ?string
    {
        return $this->presenter->showField($this->getEntity(), 'sku') ?? null;
    }

    public function toArray(): array
    {
        return array_merge(
            $this->getPriceData(),
            [
                'productId' => $this->getEntity()?->getId(),
                'name' => $this->getName(),
                'slug' => $this->getSlug(),
                'sku' => $this->getSku(),
                'imageUrl' => $this->getImageUrl(),
                'isDefault' => $this->isDefault(),
            ],
        );
    }
}