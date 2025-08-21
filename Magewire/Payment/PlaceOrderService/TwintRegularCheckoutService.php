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

    public function evaluateCompletion(EvaluationResultFactory $resultFactory, ?int $orderId = null): EvaluationResult
    {
        /** @var ScanQrModal $block */
        $block = $this->layoutFactory->create()->createBlock(ScanQrModal::class);
        $block->setTemplate('Twint_Magento::qr.phtml');

        $order = $this->orderRepository->get($orderId);
        $pairing = $this->pairingRepository->getByOrderId($order->getIncrementId());

        $hashedParingId = $this->cryptoHandler->hash($pairing->getPairingId());

        $params = [
            'success' => true,
            'id' => $hashedParingId,
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
