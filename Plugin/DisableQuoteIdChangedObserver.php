<?php

declare(strict_types=1);

namespace Twint\MagentoHyva\Plugin;

use Closure;
use Hyva\Checkout\Observer\Frontend\HyvaCheckoutSessionReset;
use Magento\Framework\Event\Observer;
use Twint\Magento\Model\CloneQuoteContext;

class DisableQuoteIdChangedObserver
{
    public function __construct(
        private readonly CloneQuoteContext $cloneQuoteContext
    ) {
    }

    public function aroundExecute(HyvaCheckoutSessionReset $subject, Closure $closure, Observer $observer)
    {
        if ($this->cloneQuoteContext->hasQuote()) {
            return null;
        }

        return $closure($observer);
    }
}
