<?php

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class BrizyPro_Content_Placeholders_SimpleProductAware extends Brizy_Content_Placeholders_Simple
{


    public function __construct($label, $placeholder, $value, $group = null, $display = Brizy_Content_Placeholders_Abstract::DISPLAY_INLINE, $attrs = [])
    {
        parent::__construct($label, $placeholder, $value, $group, $display, array_merge(['type' => 'product'], $attrs));
    }

    /**
     * @param ContextInterface $context
     * @param ContentPlaceholder $contentPlaceholder
     *
     * @return false|mixed|string
     */
    public function getValue(ContextInterface $context, ContentPlaceholder $contentPlaceholder)
    {

        /**
         * @var WC_Product
         */
        global $product;

        $postId = (int)$contentPlaceholder->getAttribute('product_id');

        if ($postId) {

            if ($product instanceof WC_Product) {
                $oldProductId = $product->get_id();
            }

            if (!isset($oldProductId) || $postId != $oldProductId) {

                $resetProduct = wc_setup_product_data($postId);

                if (!($resetProduct instanceof WC_Product)) {

                    if (isset($oldProductId)) {
                        wc_setup_product_data($oldProductId);
                    }

                    return '';
                }

                $newContext = Brizy_Content_ContextFactory::createContext(
                    $context->getProject(),
                    get_post($postId)
                );

                $newContext->setProduct($resetProduct);

                $html = $this->getHtml($newContext, $contentPlaceholder);

                if (isset($oldProductId)) {
                    wc_setup_product_data($oldProductId);
                }

                return $html;
            }
        }

        if (!($product instanceof WC_Product)) {
            return '';
        }

        $context->setProduct($product);

        return $this->getHtml($context, $contentPlaceholder);
    }

    private function getHtml($context, $contentPlaceholder)
    {

        if (!$context->getWpPost()) {
            return '';
        }

        add_action('woocommerce_locate_template', [$this, 'woocommerce_locate_template'], 9999, 3);

        ob_start();
        ob_clean();
        parent::getValue($context, $contentPlaceholder);
        $html = ob_get_clean();

        remove_action('woocommerce_locate_template', [$this, 'woocommerce_locate_template'], 9999);

        return $html;
    }

    public function woocommerce_locate_template($template, $template_name, $template_path)
    {
        $default = WC()->plugin_path() . '/templates/' . $template_name;

        return file_exists($default) ? $default : $template;
    }
}