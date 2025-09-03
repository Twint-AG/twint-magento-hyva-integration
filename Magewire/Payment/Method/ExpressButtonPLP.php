<?php

namespace Twint\MagentoHyva\Magewire\Payment\Method;

use Twint\Magento\Constant\TwintConstant;
use Twint\MagentoHyva\Magewire\Payment\Method\ExpressButton;

class ExpressButtonPLP extends ExpressButton
{
    protected function getScreen(): string
    {
        return TwintConstant::SCREEN_PLP;
    }
}
