<?php   
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @title      Cielo pagamento com cartão de crédito (Brazil)
 * @category   payment
 * @package    Oitoo_Cielo
 * @copyright  Copyright (c) 2014 Oitoo (www.oitoo.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Oitoo <www.oitoo.com.br>
 */
class Oitoo_Cielo_Block_Success extends Mage_Checkout_Block_Onepage_Success {

    public function sendEmailOrder(){
        $order = Mage::getModel('sales/order');
        $order_id = $this->getOrderId();
        $order->loadByIncrementId($order_id);
        $order->sendNewOrderEmail();
        $order->setEmailSent(true);
    }
    
    public function getMensagem(){

        return Mage::getStoreConfig('payment/apelidocielo/msg_add');
    }


}