<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_SimplePostAware extends Brizy_Content_Placeholders_Simple
{

    /**
     * @param ContextInterface $context
     * @param ContentPlaceholder $contentPlaceholder
     *
     * @return false|mixed|string
     */
    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {
        $newContext = null;
        if (($entity = $this->getEntity($contentPlaceholder)) || ($entity = $context->getEntity())) {
            $newContext = Brizy_Content_ContextFactory::createContext(
                $context->getProject(),
                $entity
            );

            $newContext->setObjectData( $context->getObjectData() );

            return parent::getValue($newContext, $contentPlaceholder);
        }

//        if ($postId = $contentPlaceholder->getAttribute('id')) {
//            $newContext = Brizy_Content_ContextFactory::createContext(
//                $context->getProject(),
//                get_post($postId)
//            );
//
//            return parent::getValue($newContext, $contentPlaceholder);
//        }
//
        if (!$context->getWpPost()) {
            return;
        }

        return parent::getValue($context, $contentPlaceholder);
    }

    /**
     * @return mixed|string
     */
    protected function getOptionValue()
    {

        return $this->getReplacePlaceholder();
    }
}