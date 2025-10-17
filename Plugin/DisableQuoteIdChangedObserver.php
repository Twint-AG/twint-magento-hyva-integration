<?php

declare(strict_types=1);

namespace Twint\MagentoHyva\Plugin;

use Closure;
use Hyva\Checkout\Observer\Frontend\HyvaCheckoutSessionReset;
use Magento\Framework\Event\Observer;
use Twint\Magento\Plugin\SubmitClonedQuotePlugin;

class DisableQuoteIdChangedObserver
{
    public function aroundExecute(HyvaCheckoutSessionReset $subject, Closure $closure, Observer $observer)
    {
        foreach (debug_backtrace() as $item) {
            if (isset($item['class']) && $item['class'] === SubmitClonedQuotePlugin::class) {
                return null;
            }
        }

        return $closure($observer);
    }
}
