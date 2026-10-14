<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_MenuItemProperty extends Brizy_Content_Placeholders_Abstract
{
    public function support($placeholderName)
    {
        return strpos($placeholderName, 'menu_item_') !== false && !in_array($placeholderName, ['menu_item_icon']);
    }

    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $menuItem = $context->getMenuItem();
        if (!$menuItem) {
            return '';
        }

        $matches = [];
        preg_match("/menu_item_(?<name>.*)/", $contentPlaceholder->getName(), $matches);

        switch ($matches['name']) {
            case 'classname':
                $classes = esc_attr(
                    apply_filters('nav_menu_css_class', implode(' ', array_filter($menuItem->classes)), $menuItem, false, false)
                );

                return $classes;
            case 'title':
                return $menuItem->title;
            case 'target':
                return $menuItem->target;
            case 'href':
            case 'url':
                return $menuItem->url;
            case 'id':
                return $menuItem->ID;
            case 'uid':
                return get_post_meta($menuItem->ID, 'brizy_post_uid', true);
			case 'attr_title':
                return apply_filters( 'nav_menu_attr_title', isset($menuItem->attr_title) ? $menuItem->attr_title : '' );
        }

        return '';
    }

}
