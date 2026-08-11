<?php

declare(strict_types=1);

enum ContentBlockLinks: string
{
    public function build(array $params = []): string
    {
        $url = $this->value;

        foreach ($params as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (str_contains($url, $placeholder)) {
                $url = str_replace($placeholder, urlencode($value), $url);
            }
        }

        return $url;
    }

    public static function getListRoute(BlockType $type): string
    {
        return match($type) {
            BlockType::HERO => self::HERO_LIST->value,
            BlockType::SMALL_BANNER => self::SMALL_BANNER_LIST->value,
            BlockType::BIG_BANNER => self::BIG_BANNER_LIST->value,
            BlockType::SUMMER_BANNER => self::SUMMER_BANNER_LIST->value,
            BlockType::BANNER_SQUARE => self::BANNER_SQUARE_LIST->value,
            BlockType::BANNER_LEFT_WIDE => self::BANNER_LEFT_WIDE_LIST->value,
            BlockType::DISCOUNT_ROW => self::DISCOUNT_ROW_LIST->value,
        };
    }

    public static function getAddRoute(BlockType $type): string
    {
        return self::ADD_WITH_TYPE->build(['type' => $type->value]);
    }

    public static function getEditRoute(BlockType $type, string $id): string
    {
        return self::EDIT_WITH_TYPE_AND_ID->build([
            'type' => $type->value,
            'id' => $id,
        ]);
    }

    public static function getSaveRoute(BlockType $type): string
    {
        return self::SAVE_WITH_TYPE->build(['type' => $type->value]);
    }

    public static function getDeleteRoute(): string
    {
        return self::DELETE->value;
    }

    public static function getDeConfirmDeletionRoute(): string
    {
        return self::CONFIRM_DELETION->value;
    }

    public static function getDeleteRouteWithParams(BlockType $type, string $id): string
    {
        return self::DELETE_WITH_TYPE_AND_ID->build([
            'type' => $type->value,
            'id' => $id,
        ]);
    }

    public static function getCancelRoute(BlockType $type, string $id): string
    {
        return self::CANCEL_WITH_TYPE_AND_ID->build([
            'type' => $type->value,
            'id' => $id,
        ]);
    }

    public static function getConfirmRedirectRoute(BlockType $type, string $id): string
    {
        return self::EDIT_WITH_TYPE_AND_ID->build([
            'type' => $type->value,
            'id' => $id,
        ]);
    }
    // Base routes
    case ADD = '/admin/content-block/add';
    case EDIT = '/admin/content-block/edit';
    case SAVE = '/admin/content-block-save/index';
    case DELETE = '/admin/content-block-delete/delete';
    case LIST = '/admin/content-block/list';

    // Clean URL versions with placeholders
    case CONFIRM_DELETION = '/admin/content-block-confirm-deletion/confirm';
    case ADD_WITH_TYPE = '/admin/content-block/{type}/add';
    case EDIT_WITH_TYPE_AND_ID = '/admin/content-block-page/{id}/edit/{type}';
    case SAVE_WITH_TYPE = '/admin/content-block-save/index/{type}';
    case DELETE_WITH_TYPE_AND_ID = '/admin/content-block-delete/{id}/delete/{type}';
    case CANCEL_WITH_TYPE_AND_ID = '/admin/content-block-confirm-deletion/{id}/cancel/{type}';

    // List routes for each block type
    case HERO_LIST = '/admin/content-block-list/index/hero_section';
    case SMALL_BANNER_LIST = '/admin/content-block-list/index/small_banner';
    case BIG_BANNER_LIST = '/admin/content-block-list/index/big_banner';
    case SUMMER_BANNER_LIST = '/admin/summer-banner-list/index';
    case BIG_CARD_LIST = '/admin/big-card-list/index';
    case BANNER_SQUARE_LIST = '/admin/banner-square-list/index';
    case BANNER_LEFT_WIDE_LIST = '/admin/banner-left-wide-list/index';
    case DISCOUNT_ROW_LIST = '/admin/discount-row-list/index';
}