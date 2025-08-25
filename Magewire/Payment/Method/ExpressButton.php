<?php

namespace Twint\MagentoHyva\Magewire\Payment\Method;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magewirephp\Magewire\Component;
use Twint\Magento\Block\Frontend\ScanQrModal;
use Twint\Magento\Service\Express\CheckoutService;
use Twint\Magento\Util\CryptoHandler;

class ExpressButton extends Component
{
    public function __construct(
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly PriceCurrencyInterface $priceCurrency,
        protected readonly UrlInterface $urlBuilder,
        protected readonly LayoutFactory $layoutFactory,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly CheckoutService $checkoutService,
        protected readonly CryptoHandler $cryptoHandler,
    ) {
    }

    public function checkout(array $params): ?array
    {
        $product = $this->getProduct($params['product']);

        if (!$product) {
            return null;
        }

        /** @var \Twint\Magento\Model\Pairing $paring */
        $pairing = $this->checkoutService->checkout($product, $this->parseParams($params));

        /** @var ScanQrModal $block */
        $block = $this->layoutFactory->create()->createBlock(ScanQrModal::class);

        $hashedParingId = $this->cryptoHandler->hash($pairing->getPairingId());

        return [
            'success' => true,
            'id' => $hashedParingId,
            'token' => $pairing->getToken(),
            'amount' => $this->priceCurrency->format($pairing->getAmount()),
            'modal' => $block->toHtml(),
            'monitor_url' => $this->urlBuilder->getUrl('twint/regular/status') . '?id=' . $hashedParingId,
            'cancel_url' => $this->urlBuilder->getUrl('twint/payment/cancel') . '?id=' . $hashedParingId,
            'success_url' => $this->urlBuilder->getUrl('checkout/onepage/success'),
        ];
    }

    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();

    }

    /**
     * @param int $productId
     *
     * @return false|\Magento\Catalog\Model\Product
     */
    public function getProduct(int $productId): false|ProductInterface
    {
        if ($productId) {
            try {
                return $this->productRepository->getById($productId, false, $this->getStoreId());
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    protected function parseParams(array $dataArray): DataObject
    {
        $parsedData = [];
        foreach ($dataArray as $key => $value) {
            if (preg_match('/^(.+)\[(\d+)\]$/', $key, $matches)) {
                $arrayKey = $matches[1];
                $index = $matches[2];
                $parsedData[$arrayKey][$index] = $value;
            } else {
                $parsedData[$key] = $value;
            }
        }

        return new DataObject($parsedData);
    }
}
