<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_MegaMenuItem extends Brizy_Content_Placeholders_Abstract
{
    public function support($placeholderName)
    {
        return 'mega_menu' === $placeholderName;
    }

    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $menuItem = $context->getMenuItem();
        $uid = get_post_meta($menuItem->ID, 'brizy_post_uid', true);
        if ($placeholder = $context->searchPlaceholderByNameAndAttr('mega_menu_value', 'itemId', $uid)) {
            $replacer = new \BrizyPlaceholders\Replacer($context->getProvider());
            $newContext = Brizy_Content_ContextFactory::createContext(
                $context->getProject(),
                $context->getEntity(),
                false,
                $context,
                $contentPlaceholder
            );
            $newContext->setMegaMenu(true);
            $newContext->setParentMenu($context->getMenuUid());
            $newContext->setProvider($context->getProvider());

            return $replacer->replacePlaceholders($placeholder->getContent(), $newContext);
        }

        return "";
    }
}
