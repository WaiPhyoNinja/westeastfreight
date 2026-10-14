<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SiteSetting extends Model
{
    protected $guarded = [];

    public static function defaultCustomerMenu(string $name): array
    {
        $items = $name === 'customer_book_track' ? [
            ['Request a Quote', '/request-quote'],
        ] : [
            ['My Request Quote', '/my-orders'], ['Profile Information', '/profile'],
            ['Change Password', '/my-account?tab=password'],
        ];

        return array_map(fn ($item) => [
            'label_en' => $item[0], 'url' => $item[1], 'type' => 'URL',
            'is_active' => true, 'open_in_new_tab' => false,
        ], $items);
    }

    public static function defaultCustomerNavigationGroups(): array
    {
        return [
            [
                'menu_id' => 'customer_book_track',
                'label_en' => 'Book & Track',
                'label_my' => 'Book & Track',
                'label_th' => 'Book & Track',
                'feature_image' => null,
                'is_active' => true,
                'items' => self::defaultCustomerMenu('customer_book_track'),
            ],
            [
                'menu_id' => 'customer_tools',
                'label_en' => 'Tools',
                'label_my' => 'Tools',
                'label_th' => 'Tools',
                'feature_image' => null,
                'is_active' => true,
                'items' => self::defaultCustomerMenu('customer_tools'),
            ],
        ];
    }

    public static function customerNavigationGroups(?array $groups = null): array
    {
        $groups ??= static::defaultCustomerNavigationGroups();

        return array_values(array_map(function (array $group, int $index) {
            $items = is_array($group['items'] ?? null) ? $group['items'] : [];
            $label = (string) ($group['label_en'] ?? ($index === 0 ? 'Book & Track' : 'Menu '.($index + 1)));
            $derivedMenuId = strtolower($label);
            if (str_contains($derivedMenuId, 'book') && str_contains($derivedMenuId, 'track')) {
                $menuId = 'customer_book_track';
            } elseif (str_contains($derivedMenuId, 'tool')) {
                $menuId = 'customer_tools';
            } else {
                $menuId = (string) ($group['menu_id'] ?? $group['slug'] ?? $group['id'] ?? Str::slug($label) ?: 'customer_navigation_'.($index + 1));
            }

            return [
                'menu_id' => $menuId,
                'label_en' => $label,
                'label_my' => $group['label_my'] ?? ($group['label_en'] ?? null),
                'label_th' => $group['label_th'] ?? ($group['label_en'] ?? null),
                'description_en' => $group['description_en'] ?? '',
                'description_my' => $group['description_my'] ?? '',
                'description_th' => $group['description_th'] ?? '',
                'feature_image' => $group['feature_image'] ?? $group['image'] ?? null,
                'is_active' => $group['is_active'] ?? true,
                'items' => array_values($items),
            ];
        }, $groups, array_keys($groups)));
    }

    protected function casts(): array
    {
        return [
            'header_menu' => 'array',
            'customer_book_track_menu' => 'array',
            'customer_tools_menu' => 'array',
            'customer_navigation_groups' => 'array',
            'footer_menu' => 'array',
            'footer_services_menu' => 'array',
            'footer_bottom_menu' => 'array',
            'social_icons' => 'array',
            'quotation_countries' => 'array',
        ];
    }
}
