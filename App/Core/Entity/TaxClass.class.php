<?php

declare(strict_types=1);

class TaxClass extends Entity
{
    use EntityTimestampableTrait;

    #[EntityFieldId()]
    private int $id;

    private string $code;
    private string $label;
    private null|string $description = null;
    private AppliesTo $appliesTo = AppliesTo::ALL;
    private int $active = 1;

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
     * @return TaxClass
     */
    public function setId(int $id): TaxClass
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return TaxClass
     */
    public function setCode(string $code): TaxClass
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return TaxClass
     */
    public function setLabel(string $label): TaxClass
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return TaxClass
     */
    public function setDescription(?string $description): TaxClass
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @param int $active
     *
     * @return TaxClass
     */
    public function setActive(int $active): TaxClass
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return AppliesTo
     */
    public function getAppliesTo(): AppliesTo
    {
        return $this->appliesTo;
    }

    /**
     * @param string $appliesTo
     *
     * @return TaxClass
     */
    public function setAppliesTo(string $appliesTo): TaxClass
    {
        $enum = AppliesTo::tryFrom($appliesTo);
        if ($enum === null) {
            throw new InvalidArgumentException("Invalid status value: $appliesTo");
        }

        $this->appliesTo = $enum;
        return $this;
    }
}