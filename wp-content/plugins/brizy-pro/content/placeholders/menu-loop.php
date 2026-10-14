<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_MenuLoop extends Brizy_Content_Placeholders_Abstract
{
    private static $loopCallstack = array();
    private static $loopCallstack2 = array();

    public function support($placeholderName)
    {
        return 'menu_loop' === $placeholderName;
    }

    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $isRecursiveCall = $contentPlaceholder->getAttribute('recursive') == "1";
        $menuId = $contentPlaceholder->getAttribute('menuId');
        $contextMenuId = $context->getParentContext()->getMenuId();
        $contextParentMenu = $context->getParentContext()->getParentMenu();
        $isMegaMenu = !!$context->getParentContext()->getMegaMenu();

        // detect first call
        if (!$isRecursiveCall && !$contextMenuId && !$isMegaMenu) {
            self::$loopCallstack2[$menuId] = true;
        }

        // detect second call in a recursive loop
        if (!$isRecursiveCall && !$contextMenuId && $isMegaMenu) {
            if (isset(self::$loopCallstack2[$menuId]) && $contextParentMenu == $menuId) {
                return __('Menu recursion detected. Please try to use other menu here.');
            }
        }

        if ($isRecursiveCall) {
            $placeholder = $context->searchPlaceholderByNameAndAttr('menu_loop', 'menuId', $context->getMenuUid());

            return $this->handleRecursiveCall($context, $placeholder);
        } else {
            $content = $this->handleMenuLoop($context, $contentPlaceholder);

            return $content;
        }
    }

    /**
     * @param ContextInterface $context
     * @param $menuId
     * @param ContentPlaceholder $contentPlaceholder
     *
     * @return string
     */
    protected function loop(
        ContextInterface $context, ContentPlaceholder $contentPlaceholder, $menuUid, $menuId, $itemParent = 0
    )
    {
        $replacer = new \BrizyPlaceholders\Replacer($context->getProvider());
        $content = '';
        $menuItems = wp_get_nav_menu_items($menuId);
        _wp_menu_item_classes_by_context($menuItems);
        foreach ($menuItems as $menuItem) {
            if ($menuItem->menu_item_parent != $itemParent) {
                continue;
            }
            $newContext = Brizy_Content_ContextFactory::createContext($context->getProject(), $context->getEntity(), true, $context, $contentPlaceholder);
            $newContext->setProvider($context->getProvider());
            $newContext->setMenuItem($menuItem);
            $newContext->setMenuId($menuId);
            $newContext->setMenuUid($menuUid);
            $content .= $replacer->replacePlaceholders($contentPlaceholder->getContent(), $newContext);
        }

        return $content;
    }

    private function handleMenuLoop(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $menuUid = $contentPlaceholder->getAttribute('menuId', true);
        $menuIds = get_terms(['meta_key' => 'brizy_uid', 'meta_value' => $menuUid, 'fields' => 'ids']);
        $menuId = array_pop($menuIds);
        if ($menuId) {
            $content = $this->loop($context, $contentPlaceholder, $menuUid, $menuId, 0);

            return $content;
        }

        return '';
    }

    private function handleRecursiveCall(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $menuId = $context->getMenuId();
        $parentMenuItemId = $context->getMenuItem();
        $menuUid = $context->getMenuUid();
        if ($menuId) {
            return $this->loop($context, $contentPlaceholder, $menuUid, $menuId, $parentMenuItemId->ID);
        }
    }

}
