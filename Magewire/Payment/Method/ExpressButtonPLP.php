<?php

declare(strict_types=1);

namespace Twint\MagentoHyva\Magewire\Payment\Method;

use Twint\Magento\Constant\TwintConstant;

class ExpressButtonPLP extends ExpressButton
{
    protected function getScreen(): string
    {
        return TwintConstant::SCREEN_PLP;
    }
}
