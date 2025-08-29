<?php

declare(strict_types=1);

namespace Twint\MagentoHyva\Plugin;

use Magento\Quote\Api\PaymentMethodManagementInterface;
use Twint\Magento\Model\Method\TwintExpressMethod;

class HideExpressOnCheckoutPage
{
    public function afterGetList(PaymentMethodManagementInterface $subject, array $result): array
    {
        return array_filter($result, static function ($method) {
            return !$method instanceof TwintExpressMethod;
        });
    }
}
