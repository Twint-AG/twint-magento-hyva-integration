<?php

namespace Twint\MagentoHyva\Magewire\Payment\Method;

use Magento\Store\Model\StoreManagerInterface;
use Magewirephp\Magewire\Component;
use Twint\Magento\Constant\TwintConstant;
use Twint\Magento\Helper\ConfigHelper;

abstract class ExpressButton extends Component
{
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
        protected readonly ConfigHelper $configHelper,
    ) {
    }

    public function shouldRender(): bool
    {
        $config    = $this->configHelper->getConfigs();
        $validated = $config->getCredentials()
                            ->getValidated();
        $enabled   = $config->getExpressConfig()
                            ->getEnabled();
        $screen    = $config->getExpressConfig()
                            ->onScreen($this->getScreen());
        $currency  =  $this->storeManager->getStore()
                                         ->getCurrentCurrencyCode() === TwintConstant::CURRENCY;

        return $enabled && $validated && $screen && $currency;
    }

    abstract protected function getScreen(): string;
}
