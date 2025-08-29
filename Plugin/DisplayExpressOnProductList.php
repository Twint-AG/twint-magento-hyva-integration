<?php

namespace Twint\MagentoHyva\Plugin;

use Hyva\Theme\ViewModel\ProductListItem;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Twint\MagentoHyva\Magewire\Payment\Method\ExpressButton;

class DisplayExpressOnProductList
{
    public function __construct(
        private readonly LayoutInterface $layout
    ) {
    }

    public function afterGetProductPriceHtml(ProductListItem $subject, string $result, Product $product): string
    {
        $extraHtml = $this->layout->createBlock(Template::class)
                                  ->setTemplate('Twint_MagentoHyva::checkout/express-checkout/product-list-page.phtml')
                                  ->setData('magewire', ObjectManager::getInstance()->create(ExpressButton::class))
                                  ->setChild(
                                      'loading',
                                      $this->layout->createBlock(Template::class)
                                                   ->setTemplate('Hyva_Theme::ui/loading.phtml')
                                  )
                                  ->toHtml();

        return $result . $extraHtml;
    }
}
