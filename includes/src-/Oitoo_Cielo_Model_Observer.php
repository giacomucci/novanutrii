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
class Oitoo_Cielo_Model_Observer
{
	public function setDiscount($observer)
    {
       $quote=$observer->getEvent()->getQuote();
       $quoteid=$quote->getId();


       if(!is_object($quote->getPayment())) return true;
       try{
            $info = $quote->getPayment()->getMethodInstance();
       } catch(exception $e){
        return true;
       }
       if($info['info_instance']->getMethod() != 'apelidocielo') return true;

       $valorTotal      =   $quote->getGrandTotal();
       $parcela         =   $info['info_instance']->getAdditionalData();
       $valorDesconto   =   Mage::helper('apelidocielo')->getDiscountAmount($parcela,$valorTotal);
       if($valorDesconto > 0):
                 mage::helper('apelidocielo')->setDiscountQuote($quote,$valorDesconto,'Desconto para pagamento à vista no Cartão de crédito');
       endif;

      
 }

   


} 