<?php

declare(strict_types=1);
class VariationAttribute extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId()]
    private int $id; //Unique product identifier

    private int $variationId;
    private string $attributeName;
    private string $attributeValue;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return VariationAttribute
     */
    public function setId(int $id): VariationAttribute
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getVariationId(): int
    {
        return $this->variationId;
    }

    /**
     * @param int $variationId
     *
     * @return VariationAttribute
     */
    public function setVariationId(int $variationId): VariationAttribute
    {
        $this->variationId = $variationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    /**
     * @param string $attributeName
     *
     * @return VariationAttribute
     */
    public function setAttributeName(string $attributeName): VariationAttribute
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeValue(): string
    {
        return $this->attributeValue;
    }

    /**
     * @param string $attributeValue
     *
     * @return VariationAttribute
     */
    public function setAttributeValue(string $attributeValue): VariationAttribute
    {
        $this->attributeValue = $attributeValue;

        return $this;
    }
}