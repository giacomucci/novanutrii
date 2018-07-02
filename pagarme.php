<?php

require_once __DIR__ . '/app/Mage.php';

Mage::app();

$session = Mage::getSingleton('core/session', array('name' => 'frontend'));
$customer = Mage::getSingleton('customer/session');
$cart = Mage::getSingleton('checkout/cart');
$quote = Mage::getSingleton('checkout/session')->getQuote();

echo Mage::getUrl('pagarme/transaction_boleto/postback');
