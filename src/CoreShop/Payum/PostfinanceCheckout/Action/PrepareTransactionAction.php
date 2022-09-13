<?php

declare(strict_types=1);

/*
 * CoreShop
 *
 * This source file is available under two different licenses:
 *  - GNU General Public License version 3 (GPLv3)
 *  - CoreShop Commercial License (CCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 *
 */

namespace CoreShop\Payum\PostfinanceCheckoutBundle\Action;

use CoreShop\Component\Core\Model\CarrierInterface;
use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Core\Model\OrderItemInterface;
use CoreShop\Component\Core\Model\PaymentInterface;
use CoreShop\Component\Order\Model\AdjustmentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use PostFinanceCheckout\Sdk\Model\AddressCreate;
use PostFinanceCheckout\Sdk\Model\LineItemCreate;
use PostFinanceCheckout\Sdk\Model\LineItemType;
use Wvision\Payum\PostFinanceCheckout\Request\PrepareTransaction;

class PrepareTransactionAction implements ActionInterface
{
    /**
     * @inheritDoc
     *
     * @param PrepareTransaction $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $payment = $request->getFirstModel();

        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $order = $payment->getOrder();

        if (!$order instanceof OrderInterface) {
            return;
        }

        $orderLines = [];
        $lineItemId = 1;

        foreach ($order->getItems() as $item) {
            if (!$item instanceof OrderItemInterface) {
                continue;
            }

            $lineItem = new LineItemCreate();
            $lineItem->setName($item->getName());
            $lineItem->setUniqueId($lineItemId);
            $lineItem->setQuantity($item->getQuantity());
            $lineItem->setAmountIncludingTax($item->getTotal() / 100);
            $lineItem->setType(LineItemType::PRODUCT);

            $orderLines[] = $lineItem;

            $lineItemId++;
        }

        foreach ($order->getAdjustments(AdjustmentInterface::CART_PRICE_RULE) as $adjustment) {
            if (!$adjustment instanceof AdjustmentInterface) {
                continue;
            }

            $lineItem = new LineItemCreate();
            $lineItem->setName($adjustment->getLabel());
            $lineItem->setUniqueId($lineItemId);
            $lineItem->setQuantity($lineItemId);

            if ($adjustment->getAmount() < 0) {
                $lineItem->setDiscountIncludingTax($adjustment->getAmount() / 100);
                $lineItem->setType(LineItemType::DISCOUNT);
            } else {
                $lineItem->setAmountIncludingTax($adjustment->getAmount() / 100);
                $lineItem->setType(LineItemType::FEE);
            }

            $orderLines[] = $lineItem;

            $lineItemId++;
        }

        if ($order->getCarrier() instanceof CarrierInterface && $order->getShipping() > 0) {
            $shippingItem = new LineItemCreate();
            $shippingItem->setName($order->getCarrier()->getTitle());
            $shippingItem->setUniqueId($lineItemId);
            $shippingItem->setQuantity(1);
            $shippingItem->setAmountIncludingTax($order->getShipping() / 100);
            $shippingItem->setType(LineItemType::SHIPPING);

            $orderLines[] = $shippingItem;
        }

        $request->getTransaction()->setLineItems($orderLines);

        if ($order->getShippingAddress()) {
            $address = new AddressCreate();

            $address->setCity($order->getShippingAddress()->getCity());
            $address->setCountry($order->getShippingAddress()->getCountry()->getIsoCode());
            $address->setEmailAddress($order->getCustomer()->getEmail());
            $address->setFamilyName($order->getShippingAddress()->getLastname());
            $address->setGivenName($order->getShippingAddress()->getFirstname());
            $address->setOrganizationName($order->getShippingAddress()->getCompany());
            $address->setPhoneNumber($order->getShippingAddress()->getPhoneNumber());
            $address->setSalutation($order->getShippingAddress()->getSalutation());
            $address->setStreet($order->getShippingAddress()->getStreet() . ' ' . $order->getShippingAddress()->getNumber());

            $request->getTransaction()->setShippingAddress($address);
        }

        if ($order->getInvoiceAddress()) {
            $address = new AddressCreate();

            $address->setCity($order->getInvoiceAddress()->getCity());
            $address->setCountry($order->getInvoiceAddress()->getCountry()->getIsoCode());
            $address->setEmailAddress($order->getCustomer()->getEmail());
            $address->setFamilyName($order->getInvoiceAddress()->getLastname());
            $address->setGivenName($order->getInvoiceAddress()->getFirstname());
            $address->setOrganizationName($order->getInvoiceAddress()->getCompany());
            $address->setPhoneNumber($order->getInvoiceAddress()->getPhoneNumber());
            $address->setSalutation($order->getInvoiceAddress()->getSalutation());
            $address->setStreet($order->getInvoiceAddress()->getStreet() . ' ' . $order->getInvoiceAddress()->getNumber());

            $request->getTransaction()->setBillingAddress($address);
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof PrepareTransaction
            && $request->getModel() instanceof \ArrayAccess;
    }
}
