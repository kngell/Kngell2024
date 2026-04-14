<?php

declare(strict_types=1);

class UserAcl extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'id')]
    private int $id;

    private int $userId;
    private string $roleName;
    private int $priority;
    private ?DateTimeImmutable $expiresAt;

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
     * @return UserAcl
     */
    public function setId(int $id): UserAcl
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
     * @return UserAcl
     */
    public function setUserId(int $userId): UserAcl
    {
        $this->userId = $userId;

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
     * @return UserAcl
     */
    public function setRoleName(string $roleName): UserAcl
    {
        $this->roleName = $roleName;

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
     * @return UserAcl
     */
    public function setPriority(int $priority): UserAcl
    {
        $this->priority = $priority;

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
     * @return UserAcl
     */
    public function setExpiresAt(?DateTimeImmutable $expiresAt): UserAcl
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}