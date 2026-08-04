# TWINT Payment Integration for Magento with Hyvä

This module adapts the [TWINT Magento extension](https://github.com/Twint-AG/twint-magento-extension)
to the Hyvä Theme, and — when Hyvä Checkout is installed — to Hyvä Checkout.

## What you get

| Installed | TWINT express checkout | TWINT regular checkout |
|---|---|---|
| Hyvä Theme only | yes — buttons on product page, product list, cart and cart drawer | not available |
| Hyvä Theme + Hyvä Checkout | yes | yes — QR modal inside Hyvä Checkout |

## Requirements

- PHP `>= 8.1`
- PHP extension: `soap`
- Magento 2 (`magento/framework >= 103.0.4`)
- [Hyvä Theme](https://docs.hyva.io/hyva-themes/getting-started/index.html) — `hyva-themes/magento2-theme-module` `^1.3`, commercial, installed from the Composer repository covered by your Hyvä licence
- [Hyvä Checkout](https://docs.hyva.io/checkout/hyva-checkout/index.html) — `hyva-themes/magento2-hyva-checkout` `^1.3`, optional, required only for TWINT regular checkout
- A TWINT account

Hyvä packages are **not** declared as Composer requirements of this module, so
installing it never asks you for Hyvä credentials. Install Hyvä yourself, the
way your licence provides it.

## Installation

1. Install the modules via Composer:

```bash
composer require twint-ag/twint-magento-extension
composer require twint-ag/twint-magento-hyva-integration
```

2. Enable the modules:

```bash
bin/magento module:enable Twint_Magento Twint_MagentoHyva
```

3. Run setup upgrade and recompile:

```bash
bin/magento setup:upgrade && bin/magento setup:di:compile
```

4. Deploy static content (production mode only):

```bash
bin/magento setup:static-content:deploy
```

5. Clear the cache:

```bash
bin/magento cache:clean && bin/magento cache:flush
```

## Configuration

Follow the [TWINT extension guideline](https://github.com/Twint-AG/twint-magento-extension/blob/latest/Documents/twint-payment-extension-guideline.md#configure-the-module).
All TWINT settings live in the `Twint_Magento` extension; this module adds no
configuration of its own.

## Languages

Translations ship with `twint-ag/twint-magento-extension` and cover English
(`en_US`, `en_GB`), German (`de_DE`, `de_CH`), French (`fr_FR`, `fr_CH`) and
Italian (`it_IT`, `it_CH`). This module contains no translation files; to add
or customise a language, edit the CSVs in the TWINT Magento extension.

## Support

Open a GitHub issue or contact [plugin@twint.ch](mailto:plugin@twint.ch).

## License

MIT — see [LICENSE](LICENSE).
