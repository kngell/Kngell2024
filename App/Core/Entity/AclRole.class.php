<?php

declare(strict_types=1);

class AclRole extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'id')]
    private int $id;

    private string $roleName;
    private ?string $description = null;
    private bool $isSystem = false;
    private int $priority = 0;

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
     * @return AclRole
     */
    public function setId(int $id): AclRole
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoleName(): string
    {
        return $this->roleName;
    }

    /**
     * @param string $roleName
     *
     * @return AclRole
     */
    public function setRoleName(string $roleName): AclRole
    {
        $this->roleName = $roleName;

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
     * @return AclRole
     */
    public function setDescription(?string $description): AclRole
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSystem(): bool
    {
        return $this->isSystem;
    }

    /**
     * @param bool $isSystem
     *
     * @return AclRole
     */
    public function setIsSystem(bool $isSystem): AclRole
    {
        $this->isSystem = $isSystem;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return AclRole
     */
    public function setPriority(int $priority): AclRole
    {
        $this->priority = $priority;

        return $this;
    }
}