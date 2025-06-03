<?php

declare(strict_types=1);

class Transactions extends Entity
{
    #[EntityFieldId(name: 'tr_id')]
    private int $trId;
    private ?string $itemNumber;
    private ?string $itemName;
    private ?string $itemPrice;
    private ?string $itemPriceCurrency;
    private ?string $orderId;
    private ?string $transactionId;
    private ?string $paidAmount;
    private ?string $paidAmountCurrency;
    private ?string $paymentSource;
    private ?string $paymentSourceCardName;
    private ?string $paymentSourceCardLastDigits;
    private ?string $paymentSourceCardExpiry;
    private ?string $paymentSourceCardBrand;
    private ?string $paymentSourceCardType;
    private ?string $paymentStatus;
    private ?string $createdAt;
    private ?string $updatedAt;

    /**
     * Get the value of trId.
     */
    public function getTrId(): int
    {
        return $this->trId;
    }

    /**
     * Set the value of trId.
     */
    public function setTrId(int $trId): self
    {
        $this->trId = $trId;

        return $this;
    }

    /**
     * Get the value of itemNumber.
     */
    public function getItemNumber(): ?string
    {
        return $this->itemNumber;
    }

    /**
     * Set the value of itemNumber.
     */
    public function setItemNumber(?string $itemNumber): self
    {
        $this->itemNumber = $itemNumber;

        return $this;
    }

    /**
     * Get the value of itemName.
     */
    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    /**
     * Set the value of itemName.
     */
    public function setItemName(?string $itemName): self
    {
        $this->itemName = $itemName;

        return $this;
    }

    /**
     * Get the value of itemPrice.
     */
    public function getItemPrice(): ?string
    {
        return $this->itemPrice;
    }

    /**
     * Set the value of itemPrice.
     */
    public function setItemPrice(?string $itemPrice): self
    {
        $this->itemPrice = $itemPrice;

        return $this;
    }

    /**
     * Get the value of itemPriceCurrency.
     */
    public function getItemPriceCurrency(): ?string
    {
        return $this->itemPriceCurrency;
    }

    /**
     * Set the value of itemPriceCurrency.
     */
    public function setItemPriceCurrency(?string $itemPriceCurrency): self
    {
        $this->itemPriceCurrency = $itemPriceCurrency;

        return $this;
    }

    /**
     * Get the value of orderId.
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * Set the value of orderId.
     */
    public function setOrderId(?string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get the value of transactionId.
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * Set the value of transactionId.
     */
    public function setTransactionId(?string $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Get the value of paidAmount.
     */
    public function getPaidAmount(): ?string
    {
        return $this->paidAmount;
    }

    /**
     * Set the value of paidAmount.
     */
    public function setPaidAmount(?string $paidAmount): self
    {
        $this->paidAmount = $paidAmount;

        return $this;
    }

    /**
     * Get the value of paidAmountCurrency.
     */
    public function getPaidAmountCurrency(): ?string
    {
        return $this->paidAmountCurrency;
    }

    /**
     * Set the value of paidAmountCurrency.
     */
    public function setPaidAmountCurrency(?string $paidAmountCurrency): self
    {
        $this->paidAmountCurrency = $paidAmountCurrency;

        return $this;
    }

    /**
     * Get the value of paymentSource.
     */
    public function getPaymentSource(): ?string
    {
        return $this->paymentSource;
    }

    /**
     * Set the value of paymentSource.
     */
    public function setPaymentSource(?string $paymentSource): self
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    /**
     * Get the value of paymentSourceCardName.
     */
    public function getPaymentSourceCardName(): ?string
    {
        return $this->paymentSourceCardName;
    }

    /**
     * Set the value of paymentSourceCardName.
     */
    public function setPaymentSourceCardName(?string $paymentSourceCardName): self
    {
        $this->paymentSourceCardName = $paymentSourceCardName;

        return $this;
    }

    /**
     * Get the value of paymentSourceCardLastDigits.
     */
    public function getPaymentSourceCardLastDigits(): ?string
    {
        return $this->paymentSourceCardLastDigits;
    }

    /**
     * Set the value of paymentSourceCardLastDigits.
     */
    public function setPaymentSourceCardLastDigits(?string $paymentSourceCardLastDigits): self
    {
        $this->paymentSourceCardLastDigits = $paymentSourceCardLastDigits;

        return $this;
    }

    /**
     * Get the value of paymentSourceCardExpiry.
     */
    public function getPaymentSourceCardExpiry(): ?string
    {
        return $this->paymentSourceCardExpiry;
    }

    /**
     * Set the value of paymentSourceCardExpiry.
     */
    public function setPaymentSourceCardExpiry(?string $paymentSourceCardExpiry): self
    {
        $this->paymentSourceCardExpiry = $paymentSourceCardExpiry;

        return $this;
    }

    /**
     * Get the value of paymentSourceCardBrand.
     */
    public function getPaymentSourceCardBrand(): ?string
    {
        return $this->paymentSourceCardBrand;
    }

    /**
     * Set the value of paymentSourceCardBrand.
     */
    public function setPaymentSourceCardBrand(?string $paymentSourceCardBrand): self
    {
        $this->paymentSourceCardBrand = $paymentSourceCardBrand;

        return $this;
    }

    /**
     * Get the value of paymentSourceCardType.
     */
    public function getPaymentSourceCardType(): ?string
    {
        return $this->paymentSourceCardType;
    }

    /**
     * Set the value of paymentSourceCardType.
     */
    public function setPaymentSourceCardType(?string $paymentSourceCardType): self
    {
        $this->paymentSourceCardType = $paymentSourceCardType;

        return $this;
    }

    /**
     * Get the value of paymentStatus.
     */
    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    /**
     * Set the value of paymentStatus.
     */
    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * Get the value of createdAt.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt.
     */
    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt.
     */
    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}