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


class Oitoo_Cielo_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Oitoo_Cielo/form.phtml');

       //zera a taxa de juros, caso o cliente tenha voltado ao carrinho
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $quote->setJuros(0.0);
        $quote->setBaseJuros(0.0);
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $quote->save();
       
    }

    //pega configurações do magento
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    public function getValorMinimoParcela(){
        return Mage::getStoreConfig('payment/apelidocielo/valor_minimo');
    }

    public function getParcelassemJuros(){
        return Mage::getStoreConfig('payment/apelidocielo/parcelas_sem_juros');
    }

    public function getMaximoParcelas(){
        return Mage::getStoreConfig('payment/apelidocielo/num_max_parc');
    }

    public function getJurosParcela(){
        return Mage::getStoreConfig('payment/apelidocielo/juros_parcela');
    }

    public function getDescontoaVista(){
        return Mage::getStoreConfig('payment/apelidocielo/valor_desconto_avista');
    }

    public function getValorTotal(){
                 
        $total = Mage::getSingleton('checkout/cart')->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->getGrandTotal();
        if($total == '' || $total <= 0 ) {
            $total = Mage::getSingleton("adminhtml/session_quote")->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->getGrandTotal();
        }


        return $total;
    }


    protected function getBandeiras()
    {
        $bandeiras = Mage::getStoreConfig('payment/apelidocielo/bandeiras');
        return explode(',',$bandeiras);
    }

}