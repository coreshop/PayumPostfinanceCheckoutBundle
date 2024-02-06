# CoreShop Postfinance Checkout Payum Connector
This Bundle activates the Postfinance Checkout PaymentGateway in CoreShop.
It requires the [instride/payum-postfinance-chekcout](https://github.com/instride-ch/payum-postfinance-checkout) repository,
which will be installed automatically.

## Installation

#### 1. Composer
```bash
"coreshop/payum-postfinance-checkout-bundle": "^1.0"
```

#### 2. Activate
Enable the Bundle in Pimcore Extension Manager

#### 3. Setup
Go to CoreShop -> PaymentProvider and add a new Provider. Choose `postfinance_checkout` from `type` and fill out the required fields.
