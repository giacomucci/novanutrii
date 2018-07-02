<?php

class Ernet_Superpay_Model_Sales_Order_Payment extends Mage_Sales_Model_Order_Payment
{
        
    protected function _authorize($isOnline, $amount)
    {
    
    	Mage::log('### Mage_Sales_Model_Order_Payment | _authorize called',null,'ernet.log');
    
        // update totals
        $amount = $this->_formatAmount($amount, true);
        $this->setBaseAmountAuthorized($amount);

        // do authorization
        $order  = $this->getOrder();
        
        //if (isset($_SESSION['is_boleto']) && $_SESSION['is_boleto']==1 ){
        //    $state  = Mage_Sales_Model_Order::STATE_NEW;
        //} else {
            $state  = Mage_Sales_Model_Order::STATE_PROCESSING;
        //}    
        
        $status = true;
        //if ($isOnline) {
            // invoke authorization on gateway
            $this->getMethodInstance()->setStore($order->getStoreId())->authorize($this, $amount);
        //} else {
        //    $message = Mage::helper('sales')->__('Registered notification about authorized amount of %s.', $this->_formatPrice($amount));
        //}

        // similar logic of "payment review" order as in capturing
        if ($this->getIsTransactionPending()) {
            $message = Mage::helper('sales')->__('Authorizing amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
        } else {
            //if ($_SESSION['is_boleto']==1){
            //    $message = Mage::helper('sales')->__('Boleto emitido no valor de %s.', $this->_formatPrice($amount));
            //} else {
            //    $message = Mage::helper('sales')->__('Authorized amount of %s.', $this->_formatPrice($amount));
            //}             
            
            
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
        if ($order->isNominal()) {
            $message = $this->_prependMessage(Mage::helper('sales')->__('Nominal order registered.'));
        } else {
            $message = $this->_prependMessage($message);
            $message = $this->_appendTransactionToMessage($transaction, $message);
        }
        $order->setState($state, $status, $message);

        return $this;
    }    
    
}
?>
