<?php

namespace Twint\MagentoHyva\Magewire\Payment\Method;

use Twint\Magento\Constant\TwintConstant;
use Twint\MagentoHyva\Magewire\Payment\Method\ExpressButton;

class ExpressButtonCartFlyout extends ExpressButton
{
    protected function getScreen(): string
    {
        return TwintConstant::SCREEN_CART_FLYOUT;
    }
}
