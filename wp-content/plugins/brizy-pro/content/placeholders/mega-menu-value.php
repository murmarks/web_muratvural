<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_MegaMenuValue extends Brizy_Content_Placeholders_Abstract
{
    public function support($placeholderName)
    {
        return 'mega_menu_value' === $placeholderName;
    }

    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        return '';
    }

}
