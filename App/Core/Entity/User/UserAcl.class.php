<?php

declare(strict_types=1);

class UserAcl extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    protected const array RELATIONSHIPS = [
        'acl_user_role' => [
            'class' => AclUserRole::class,
            'type' => 'one-to-many',
            'collection' => true,
            'foreign_key' => 'role_id',
        ],
    ];

    #[EntityFieldId(name: 'id')]
    private int $id;

    private string $roleName;
    private ?string $description = null;
    private bool $isSystem = false;
    private int $priority = 0;

    /** @var AclUserRole[] */
    private array $aclUserRole = [];

    private ?int $level = null;
    private ?DateTimeImmutable $expiresAt = null;
    private ?string $userId = null;

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
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return UserAcl
     */
    public function setDescription(?string $description): UserAcl
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
     * @return UserAcl
     */
    public function setIsSystem(bool $isSystem): UserAcl
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
     * @return UserAcl
     */
    public function setPriority(int $priority): UserAcl
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return array
     */
    public function getAclUserRole(): array
    {
        return $this->aclUserRole;
    }

    /**
     * @param array $aclUserRole
     *
     * @return UserAcl
     */
    public function setAclUserRole(array $aclUserRole): UserAcl
    {
        $this->aclUserRole = $aclUserRole;
        return $this;
    }

    public function addAclUserRole(AclUserRole $aclUserRole): UserAcl
    {
        $this->aclUserRole[] = $aclUserRole;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param null|int $level
     *
     * @return UserAcl
     */
    public function setLevel(?int $level): UserAcl
    {
        $this->level = $level;

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

    /**
     * @return null|string
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * @param null|string $userId
     *
     * @return UserAcl
     */
    public function setUserId(?string $userId): UserAcl
    {
        $this->userId = $userId;

        return $this;
    }
}