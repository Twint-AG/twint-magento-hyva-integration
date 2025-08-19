<?php

namespace Hyva\TwintPayment\Plugin;

use Magento\Quote\Api\PaymentMethodManagementInterface;
use Twint\Magento\Model\Method\TwintExpressMethod;

class HideExpressCheckout
{
    public function afterGetList(PaymentMethodManagementInterface $subject, array $result): array
    {
        return array_filter($result, static function ($method) {
            return !$method instanceof TwintExpressMethod;
        });
    }
}
