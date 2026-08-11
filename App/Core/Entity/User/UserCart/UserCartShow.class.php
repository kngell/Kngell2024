<?php

declare(strict_types=1);

class UserCartShow extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    protected const array RELATIONSHIPS = [
        'user_cart_item' => [
            'class' => UserCartItem::class,
            'type' => 'one-to-many',
            'collection' => true,
            'foreign_key' => 'cart_id',
        ],
    ];

    #[EntityFieldId(name: 'uc_id', type: FieldType::INT)]
    private int $id;

    private ?int $userId;
    private string $sessionId;
    private ?string $expiresAt;

    /** @var UserCartItem[] */
    private array $userCartItem = [];

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?string $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    /**
     * @return array
     */
    public function getUserCartItem(): array
    {
        return $this->userCartItem;
    }

    /**
     * @param array $userCartItem
     *
     * @return UserCartShow
     */
    public function setUserCartItem(array $userCartItem): UserCartShow
    {
        $this->userCartItem = $userCartItem;

        return $this;
    }

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
     * @return UserCartShow
     */
    public function setId(int $id): UserCartShow
    {
        $this->id = $id;

        return $this;
    }
}