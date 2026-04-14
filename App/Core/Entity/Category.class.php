<?php

declare(strict_types=1);

use Brick\Money\Money;
use Ramsey\Uuid\UuidInterface;

class Category extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    #[NotPersisted()]
    private CollectionInterface $children;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        prefix: '#',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'cat_id', type: FieldType::INT)]
    private int $id;

    #[DisplayFormat(
        style: 'uuid',
        prefix: 'ID: ',
        suffix: ' (UUID)',
    )]
    #[EntityFieldId(name: 'public_id', type: FieldType::STRING)]
    private UuidInterface $publicId;

    private string $name;
    private string $slug;
    private ?string $icon = null;
    private int $parentId;
    private int $level = 0;
    private null|string $path = null;
    private null|int $orderIndex = 0;
    private bool $isActive = true;
    private null|string $metaTitle;
    private null|string $metaDescription;
    private null|string $description;
    private null|string $imageUrl;

    // SEO & Social Media.
    private ?string $metaKeywords = null;
    private ?string $ogTitle = null;
    private ?string $ogDescription = null;
    private ?string $ogImage = null;
    private ?string $twitterCard = null;
    private ?string $canonicalUrl = null;

    //Content & Display
    private ?string $shortDescription = null;
    private ?string $content = null;
    private ?string $template = null;
    private ?string $cssClass = null;
    private ?string $backgroundColor = null;
    private ?string $textColor = null;

    //Category Management
    private bool $showInMenu = true;
    private bool $showInFooter = false;
    private bool $allowSubcategories = true;
    private int $maxDepth = 3;
    private ?int $productsCount = null;

    //URL & Redirects
    private ?string $customUrl = null;
    private ?string $redirectUrl = null;
    private int $redirectType = 301;

    // Filters & Attributes
    private string $defaultSort = 'name ASC';

    // Performance & Caching
    private ?int $cacheTtl = 3600;

    // E-commerce Specific (if applicable)
    private ?Money $minPrice = null;
    private ?Money $maxPrice = null;
    private ?PriceRange $priceRanges = null;
    private bool $isFeatured = false;

    public function initChildren(): void
    {
        $this->children = new Collection();
    }

    /**
     * @param Category $category
     *
     * @return Category
     */
    public function addChildren(Category $category): Category
    {
        $this->children->add($category);
        return $this;
    }

    /**
     * @return CollectionInterface
     */
    public function getChildren(): CollectionInterface
    {
        return $this->children;
    }

    /**
     * @param CollectionInterface $children
     *
     * @return Category
     */
    public function setChildren(CollectionInterface $children): Category
    {
        $this->children = $children;
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
     * @return Category
     */
    public function setId(int $id): Category
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Category
     */
    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Category
     */
    public function setSlug(string $slug): Category
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param null|string $icon
     *
     * @return Category
     */
    public function setIcon(?string $icon): Category
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     *
     * @return Category
     */
    public function setParentId(int $parentId): Category
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return Category
     */
    public function setLevel(int $level): Category
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param null|string $path
     *
     * @return Category
     */
    public function setPath(?string $path): Category
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    /**
     * @param null|int $orderIndex
     *
     * @return Category
     */
    public function setOrderIndex(?int $orderIndex): Category
    {
        $this->orderIndex = $orderIndex;

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
     * @return Category
     */
    public function setIsActive(bool $isActive): Category
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * @param null|string $metaTitle
     *
     * @return Category
     */
    public function setMetaTitle(?string $metaTitle): Category
    {
        $this->metaTitle = $metaTitle;

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
     * @return Category
     */
    public function setDescription(?string $description): Category
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @param null|string $imageUrl
     *
     * @return Category
     */
    public function setImageUrl(?string $imageUrl): Category
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    /**
     * @param null|string $metaKeywords
     *
     * @return Category
     */
    public function setMetaKeywords(?string $metaKeywords): Category
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    /**
     * @param null|string $ogTitle
     *
     * @return Category
     */
    public function setOgTitle(?string $ogTitle): Category
    {
        $this->ogTitle = $ogTitle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    /**
     * @param null|string $ogDescription
     *
     * @return Category
     */
    public function setOgDescription(?string $ogDescription): Category
    {
        $this->ogDescription = $ogDescription;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    /**
     * @param null|string $ogImage
     *
     * @return Category
     */
    public function setOgImage(?string $ogImage): Category
    {
        $this->ogImage = $ogImage;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTwitterCard(): ?string
    {
        return $this->twitterCard;
    }

    /**
     * @param null|string $twitterCard
     *
     * @return Category
     */
    public function setTwitterCard(?string $twitterCard): Category
    {
        $this->twitterCard = $twitterCard;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    /**
     * @param null|string $canonicalUrl
     *
     * @return Category
     */
    public function setCanonicalUrl(?string $canonicalUrl): Category
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param null|string $shortDescription
     *
     * @return Category
     */
    public function setShortDescription(?string $shortDescription): Category
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param null|string $content
     *
     * @return Category
     */
    public function setContent(?string $content): Category
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param null|string $template
     *
     * @return Category
     */
    public function setTemplate(?string $template): Category
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    /**
     * @param null|string $cssClass
     *
     * @return Category
     */
    public function setCssClass(?string $cssClass): Category
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    /**
     * @param null|string $backgroundColor
     *
     * @return Category
     */
    public function setBackgroundColor(?string $backgroundColor): Category
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTextColor(): ?string
    {
        return $this->textColor;
    }

    /**
     * @param null|string $textColor
     *
     * @return Category
     */
    public function setTextColor(?string $textColor): Category
    {
        $this->textColor = $textColor;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowInMenu(): bool
    {
        return $this->showInMenu;
    }

    /**
     * @param bool $showInMenu
     *
     * @return Category
     */
    public function setShowInMenu(bool $showInMenu): Category
    {
        $this->showInMenu = $showInMenu;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowInFooter(): bool
    {
        return $this->showInFooter;
    }

    /**
     * @param bool $showInFooter
     *
     * @return Category
     */
    public function setShowInFooter(bool $showInFooter): Category
    {
        $this->showInFooter = $showInFooter;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowSubcategories(): bool
    {
        return $this->allowSubcategories;
    }

    /**
     * @param bool $allowSubcategories
     *
     * @return Category
     */
    public function setAllowSubcategories(bool $allowSubcategories): Category
    {
        $this->allowSubcategories = $allowSubcategories;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    /**
     * @param int $maxDepth
     *
     * @return Category
     */
    public function setMaxDepth(int $maxDepth): Category
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getProductsCount(): ?int
    {
        return $this->productsCount;
    }

    /**
     * @param null|int $productsCount
     *
     * @return Category
     */
    public function setProductsCount(?int $productsCount): Category
    {
        $this->productsCount = $productsCount;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomUrl(): ?string
    {
        return $this->customUrl;
    }

    /**
     * @param null|string $customUrl
     *
     * @return Category
     */
    public function setCustomUrl(?string $customUrl): Category
    {
        $this->customUrl = $customUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @param null|string $redirectUrl
     *
     * @return Category
     */
    public function setRedirectUrl(?string $redirectUrl): Category
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * @return int
     */
    public function getRedirectType(): int
    {
        return $this->redirectType;
    }

    /**
     * @param int $redirectType
     *
     * @return Category
     */
    public function setRedirectType(int $redirectType): Category
    {
        $this->redirectType = $redirectType;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultSort(): string
    {
        return $this->defaultSort;
    }

    /**
     * @param string $defaultSort
     *
     * @return Category
     */
    public function setDefaultSort(string $defaultSort): Category
    {
        $this->defaultSort = $defaultSort;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getCacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    /**
     * @param null|int $cacheTtl
     *
     * @return Category
     */
    public function setCacheTtl(?int $cacheTtl): Category
    {
        $this->cacheTtl = $cacheTtl;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFeatured(): bool
    {
        return $this->isFeatured;
    }

    /**
     * @param bool $isFeatured
     *
     * @return Category
     */
    public function setIsFeatured(bool $isFeatured): Category
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getPublicId(): UuidInterface
    {
        return $this->publicId;
    }

    /**
     * @param UuidInterface $publicId
     *
     * @return Category
     */
    public function setPublicId(UuidInterface $publicId): Category
    {
        $this->publicId = $publicId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @param null|string $metaDescription
     *
     * @return Category
     */
    public function setMetaDescription(?string $metaDescription): Category
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @return null|Money
     */
    public function getMinPrice(): ?Money
    {
        return $this->minPrice;
    }

    public function setMinPrice($minPrice): self
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    /**
     * @return null|Money
     */
    public function getMaxPrice(): ?Money
    {
        return $this->maxPrice;
    }

    public function setMaxPrice($maxPrice): self
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    /**
     * @return null|PriceRange
     */
    public function getPriceRanges(): ?PriceRange
    {
        return $this->priceRanges;
    }

    public function setPriceRanges(?PriceRange $priceRanges): self
    {
        $this->priceRanges = $priceRanges;
        return $this;
    }
}