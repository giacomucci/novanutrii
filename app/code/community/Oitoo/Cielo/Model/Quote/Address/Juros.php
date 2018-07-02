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
class Oitoo_Cielo_Model_Quote_Address_Juros extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /** 
     * Constructor that should initiaze 
     */
    public function __construct()
    {
        $this->setCode('juros');
    }

    /**
     * Used each time when collectTotals is invoked
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Your_Module_Model_Total_Custom
     */
    
	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
        parent::collect($address);

		if ($address->getData('address_type') == 'billing') return $this;
		
		//$this->_setAddress($address);
		
		$paymentMethodOK  = ($address->getQuote()->getPayment()->getMethod() == 'apelidocielo');
        $parcelasOK  = ($address->getQuote()->getPayment()->getAdditionalData() != 1);
        $ammount = $address->getQuote()->getJuros();
		if($ammount > 0 && $ammount != null && $parcelasOK && $paymentMethodOK)
		{
			$this->_setBaseAmount($ammount);
			$this->_setAmount($address->getQuote()->getStore()->convertPrice($ammount, false));
			$address->setJuros($ammount); 
            $address->setBaseJuros($ammount); 
		}
		else
		{
			$this->_setBaseAmount(0.00);
			$this->_setAmount(0.00);
		}
		
		return $this;
	}

    /**
     * Used each time when totals are displayed
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Your_Module_Model_Total_Custom
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getJuros() != 0) 
        {
            $address->addTotal(array
            (
                'code' => $this->getCode(),
                'title' => Mage::helper('apelidocielo')->__('Juros'),
                'value' => $address->getJuros()
            ));
        }
    }
}