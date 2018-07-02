<?php

require_once __DIR__ . '/app/Mage.php';

Mage::app();

$customerSession = Mage::getSingleton('customer/session');
$coreSession = Mage::getSingleton('core/session');
foreach ($customerSession->getMessages()->getItems() as $message) {
    $coreSession->addMessage($message);
}

echo $customerSession->getMessages(true);


?>