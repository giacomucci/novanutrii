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
class Oitoo_Cielo_AdminController extends Mage_Adminhtml_Controller_Action{
    public function xmlcieloAction() {

       $tid = Mage::app()->getRequest()->getParam('tid');
       if($tid == '' || $tid == null){
       echo "O tid da transação não foi passado como parâmetro.";
       }
   
       $retorno = mage::getModel('apelidocielo/cielo')->getTransacao($tid);
       echo '<pre>';
       
       echo htmlentities($retorno->asXML(), ENT_COMPAT, 'Windows-1252', false);
       echo '</pre>';
    }


}