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
class Oitoo_Cielo_Model_Order_Invoice_Juros extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
	{
		$order = $invoice->getOrder();
        
        $invoice->setGrandTotal($invoice->getGrandTotal() + $order->getJuros());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $order->getBaseJuros());
		
		return $this;
	}
}