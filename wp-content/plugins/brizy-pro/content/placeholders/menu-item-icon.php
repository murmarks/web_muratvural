<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_MenuItemIcon extends Brizy_Content_Placeholders_Abstract
{
    public function support($placeholderName)
    {
        return 'menu_item_icon' === $placeholderName;
    }

    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $menuItem = $context->getMenuItem();
        $uid = get_post_meta($menuItem->ID, 'brizy_post_uid', true);
        if ($placeholder = $context->searchPlaceholderByNameAndAttr('menu_item_icon_value', 'itemId', $uid)) {
            return $placeholder->getContent();
        }
    }

}
