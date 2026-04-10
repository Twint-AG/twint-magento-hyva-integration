<?php

declare(strict_types=1);

namespace Twint\MagentoHyva\Plugin;

use Closure;
use Magento\Framework\Event\Observer;
use Twint\Magento\Model\CloneQuoteContext;

class DisableQuoteIdChangedObserver
{
    public function __construct(
        private readonly CloneQuoteContext $cloneQuoteContext
    ) {
    }

    /**
     * @param mixed $subject Hyva\Checkout\Observer\Frontend\HyvaCheckoutSessionReset
     */
    public function aroundExecute($subject, Closure $closure, Observer $observer)
    {
        if ($this->cloneQuoteContext->hasQuote()) {
            return null;
        }

        return $closure($observer);
    }
}
