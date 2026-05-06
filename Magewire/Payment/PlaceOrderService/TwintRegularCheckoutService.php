<?php

declare(strict_types=1);

namespace Twint\MagentoHyva\Magewire\Payment\PlaceOrderService;

use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Payment\AbstractOrderData;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Twint\Magento\Api\PairingRepositoryInterface;
use Twint\Magento\Block\Frontend\ScanQrModal;
use Twint\Magento\Util\CryptoHandler;

/**
 * TWINT regular checkout place order service for Hyvä Checkout.
 *
 * This class conditionally extends AbstractPlaceOrderService from Hyvä Checkout
 * when it is installed, or a plain fallback base class when it is not.
 * The conditional base class is required because Magento's DI compiler scans all
 * PHP files in the module and would fail on missing parent class references.
 *
 * This class is only instantiated at runtime when Hyvä Checkout is present
 * (via PlaceOrderServiceProvider DI configuration in di.xml).
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
if (class_exists(AbstractPlaceOrderService::class, false)
    || class_exists(AbstractPlaceOrderService::class)
) {
    class TwintRegularCheckoutService extends AbstractPlaceOrderService
    {
        public function __construct(
            private readonly CryptoHandler $cryptoHandler,
            private readonly PairingRepositoryInterface $pairingRepository,
            private readonly OrderRepositoryInterface $orderRepository,
            private readonly LayoutFactory $layoutFactory,
            private readonly PriceCurrencyInterface $priceCurrency,
            private readonly UrlInterface $urlBuilder,
            CartManagementInterface $cartManagement,
            ?AbstractOrderData $orderData = null,
        ) {
            parent::__construct($cartManagement, $orderData);
        }

        public function canRedirect(): bool
        {
            return false;
        }

        public function evaluateCompletion(
            EvaluationResultFactory $resultFactory,
            ?int $orderId = null
        ): EvaluationResult {
            return $this->doEvaluateCompletion($resultFactory, $orderId);
        }

        private function doEvaluateCompletion($resultFactory, ?int $orderId)
        {
            /** @var ScanQrModal $block */
            $block = $this->layoutFactory->create()->createBlock(ScanQrModal::class);
            $block->setTemplate('Twint_Magento::qr.phtml');

            $order = $this->orderRepository->get($orderId);
            $pairing = $this->pairingRepository->getByOrderId($order->getIncrementId());

            $hashedParingId = $this->cryptoHandler->hash($pairing->getPairingId());

            $params = [
                'success' => true,
                'pairingId' => $hashedParingId,
                'token' => $pairing->getToken(),
                'amount' => $this->priceCurrency->format($pairing->getAmount()),
                'modal' => $block->toHtml(),
                'monitor_url' => $this->urlBuilder->getUrl('twint/regular/status') . '?id=' . $hashedParingId,
                'cancel_url' => $this->urlBuilder->getUrl('twint/payment/cancel') . '?id=' . $hashedParingId,
                'success_url' => $this->urlBuilder->getUrl('checkout/onepage/success'),
            ];

            return $resultFactory->createExecutable('twint-show-modal')->withParams($params);
        }
    }
} else {
    /**
     * Fallback class when Hyvä Checkout is not installed.
     * This class is never instantiated — it exists only to prevent compile-time errors.
     */
    class TwintRegularCheckoutService
    {
    }
}
