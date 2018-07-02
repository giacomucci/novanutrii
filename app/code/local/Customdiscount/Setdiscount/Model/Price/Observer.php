<?php
class Customdiscount_Setdiscount_Model_Price_Observer{
    function set_product_discount($observer)
    {


        $quote = $observer->getEvent()->getQuote();

        $quoteid = $quote->getId();


        if ($quoteid && Mage::app()->getRequest()->getRouteName() != "checkout"){

            $paymentcode = $quote->getPayment()->getMethod();

            //echo $quote;

            $discountAmount  = $quote->getSubtotal() * 0.05;

            if ($paymentcode == "pagarme_cc") {


                $post = Mage::app()->getRequest()->getPost();
                $paymentInstallment = 0;
                if ($paymentcode == 'pagarme_checkout') {
                    $paymentInstallment = $post['payment']['pagarme_checkout_installments'] > 1
                        ? $post['payment']['pagarme_checkout_installments']
                        : $paymentInstallment;
                } elseif ($paymentcode == 'pagarme_cc') {
                    $paymentInstallment = $post['payment']['installments'] >= 1
                        ? $post['payment']['installments']
                        : $paymentInstallment;
                }


                if ($discountAmount > 0 && $paymentInstallment == 1) {

                    $quote->setSubtotal(0);
                    $quote->setBaseSubtotal(0);

                    $quote->setSubtotalWithDiscount(0);
                    $quote->setBaseSubtotalWithDiscount(0);

                    $quote->setGrandTotal(0);
                    $quote->setBaseGrandTotal(0);


                    $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');

                    foreach ($quote->getAllAddresses() as $address) {

                        $address->setSubtotal(0);
                        $address->setBaseSubtotal(0);

                        $address->setGrandTotal(0);
                        $address->setBaseGrandTotal(0);

                        $address->collectTotals();

                        $quote->setSubtotal((float)$quote->getSubtotal() + $address->getSubtotal());
                        $quote->setBaseSubtotal((float)$quote->getBaseSubtotal() + $address->getBaseSubtotal());

                        $quote->setSubtotalWithDiscount(
                            (float)$quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                        );
                        $quote->setBaseSubtotalWithDiscount(
                            (float)$quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                        );

                        $quote->setGrandTotal((float)$quote->getGrandTotal() + $address->getGrandTotal());
                        $quote->setBaseGrandTotal((float)$quote->getBaseGrandTotal() + $address->getBaseGrandTotal());

                        $quote->save();

                        $quote->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                            ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                            ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                            ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                            ->save();


                        if ($address->getAddressType() == $canAddItems) {
                            //echo $address->setDiscountAmount; exit;
                            $address->setSubtotalWithDiscount((float)$address->getSubtotalWithDiscount() - $discountAmount);
                            $address->setGrandTotal((float)$address->getGrandTotal() - $discountAmount);
                            $address->setBaseSubtotalWithDiscount((float)$address->getBaseSubtotalWithDiscount() - $discountAmount);
                            $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() - $discountAmount);
                            if ($address->getDiscountDescription()) {
                                $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                                $address->setDiscountDescription($address->getDiscountDescription() . ', 5% de desconto');
                                $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                            } else {
                                $address->setDiscountAmount(-($discountAmount));
                                $address->setDiscountDescription('5% de desconto');
                                $address->setBaseDiscountAmount(-($discountAmount));
                            }
                            $address->save();
                        }
                    }
                }
            }

        }



    }
}
