<?php

declare(strict_types=1);

namespace Twint\MagentoHyva\Plugin;

use Twint\Magento\Block\Frontend\Express\Button;

class HideDefaultExpressCheckoutButton
{
    public function afterShouldRender(Button $subject, bool $result): bool
    {
        return false;
    }
}
