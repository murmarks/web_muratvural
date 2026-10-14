<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_MenuItemIconValue extends Brizy_Content_Placeholders_Abstract
{
    public function support($placeholderName)
    {
        return 'menu_item_icon_value' === $placeholderName;
    }

    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        return '';
    }

}
