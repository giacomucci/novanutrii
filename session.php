<?php

require_once __DIR__ . '/app/Mage.php';

Mage::app();

$session = Mage::getSingleton('core/session', array('name' => 'frontend'));
$customer = Mage::getSingleton('customer/session');
$cart = Mage::getSingleton('checkout/cart');
$quote = Mage::getSingleton('checkout/session')->getQuote();

$response = [
    'isLoggedIn' => $customer->isLoggedIn(),
    'formKey' => $session->getFormKey(),
    'cart' => [
        'itemsCount' => $cart->getSummaryQty() ?: 0,
        'grandTotal' => $quote->getStore()->formatPrice($quote->getGrandTotal(), false),
        'items' => [],
    ],
];

foreach ($quote->getAllVisibleItems() as $item) {
    $itemRenderer = new Mage_Checkout_Block_Cart_Item_Renderer();
    $itemRenderer->setItem($item);

    $response['cart']['items'][] = [
        'id' => $item->getId(),
        'name' => $itemRenderer->getProductName(),
        'quantity' => $itemRenderer->getQty(),
        'price' => $quote->getStore()->formatPrice($item->getPrice(), false),
        'productUrl' => $itemRenderer->getProductUrl(),
        'productImage' => (string) Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail')->resize(50, 50),
        'deleteUrl' => $itemRenderer->getDeleteUrl(),
    ];
}

header('Content-Type: application/json');
ob_start('ob_gzhandler');
echo json_encode($response);
