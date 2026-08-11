<?php

declare(strict_types=1);

class AclUserRole extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'id')]
    private int $id;

    private int $userId;
    private int $roleId;
    private ?int $assignedBy = null;
    private ?DateTimeImmutable $assignedAt;
    private ?DateTimeImmutable $expiresAt;
    private bool $isActive = true;

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
     * @return AclUserRole
     */
    public function setId(int $id): AclUserRole
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return AclUserRole
     */
    public function setUserId(int $userId): AclUserRole
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoleId(): int
    {
        return $this->roleId;
    }

    /**
     * @param int $roleId
     *
     * @return AclUserRole
     */
    public function setRoleId(int $roleId): AclUserRole
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getAssignedBy(): ?int
    {
        return $this->assignedBy;
    }

    /**
     * @param null|int $assignedBy
     *
     * @return AclUserRole
     */
    public function setAssignedBy(?int $assignedBy): AclUserRole
    {
        $this->assignedBy = $assignedBy;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getAssignedAt(): ?DateTimeImmutable
    {
        return $this->assignedAt;
    }

    /**
     * @param null|DateTimeImmutable $assignedAt
     *
     * @return AclUserRole
     */
    public function setAssignedAt(?DateTimeImmutable $assignedAt): AclUserRole
    {
        $this->assignedAt = $assignedAt;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @param null|DateTimeImmutable $expiresAt
     *
     * @return AclUserRole
     */
    public function setExpiresAt(?DateTimeImmutable $expiresAt): AclUserRole
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return AclUserRole
     */
    public function setIsActive(bool $isActive): AclUserRole
    {
        $this->isActive = $isActive;

        return $this;
    }
}