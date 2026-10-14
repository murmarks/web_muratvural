<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_MenuLoopSubmenu extends Brizy_Content_Placeholders_Abstract
{
    public function support($placeholderName)
    {
        return 'menu_loop_submenu' === $placeholderName;
    }

    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $menuId = $context->getMenuId();
        $menuItem = $context->getMenuItem();

        if (!$menuItem || !$menuId) {
            return '';
        }

        $items = wp_get_nav_menu_items($menuId);

        if ($this->hasSubMenu($items, (int)$menuItem->ID) && !$this->hasMegaMenu($context, (int)$menuItem->ID)) {
            $replacer = new \BrizyPlaceholders\Replacer($context->getProvider());

            return $replacer->replacePlaceholders($contentPlaceholder->getContent(), $context);
        }

        return '';
    }

    private function hasSubMenu(array $menu_items, int $id)
    {
        foreach ($menu_items as $menu_item) {
            if ((int)$menu_item->menu_item_parent === $id) {
                return true;
            }
        }

        return false;
    }

    private function hasMegaMenu(ContextInterface $context, int $menuItemId)
    {
        $uid = get_post_meta($menuItemId, 'brizy_post_uid', true);
        if ($placeholder = $context->searchPlaceholderByNameAndAttr('mega_menu_value', 'itemId', $uid)) {
            return $placeholder->getContent() != '';
        }

        return false;
    }

}
