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

class Oitoo_Cielo_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'apelidocielo';
    protected $_formBlockType = 'apelidocielo/form';
    protected $_infoBlockType = 'apelidocielo/info';

    //Is this payment method a gateway (online auth/charge) ?
    //=======================================================
    protected $_isGateway               = true;

    //Can authorize online?
    //=====================
    protected $_canAuthorize            = true;

    // Can capture funds online?
    //==========================
    protected $_canCapture              = true;

    //Can capture partial amounts online?
    //===================================
    protected $_canCapturePartial       = false;
    protected $_canCancelInvoice        = false;

    //Can refund online?
    //==================

    protected $_canRefundInvoicePartial     = true; //isso só funciona no magento EE
    protected $_canRefund                   = true; //o estorno online somente está disponível no magento EE, porém ainda é possivel fazer o estorno para controle no admin

    //Can void transactions online?
    //=============================
    protected $_canVoid                 = true;     //cancelar a transação antes de capturar


    //Can use this payment method in administration panel?
    //====================================================
    protected $_canUseInternal          = true;

    // Can show this payment method as an option on checkout payment page?
    //====================================================================
    protected $_canUseCheckout          = true;

    // Is this payment method suitable for multi-shipping checkout?
    //=============================================================
    protected $_canUseForMultishipping  = true;

    //Can save credit card information for future processing?
    //========================================================
    protected $_canSaveCc               = false;

    protected $_isInitializeNeeded      = false;
    protected $_canReviewPayment        = false; // changed PJS to true





    /**=====================================
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     ======================================*/
    public function assignData($data)
    {
   
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();

        //zera os juros para evitar erros
        $info->getQuote()->setJuros(0.0);
        $info->getQuote()->setBaseJuros(0.0);    
        $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
    
        $info->setCcType($data->getBandeiraCielo())
            ->setAdditionalData($data->getParcelasCielo())
            ->setCcOwner($data->getPortadorCielo())
            ->setCcLast4(substr($data->getNumeroCartaoCielo(), -4))
            //->setCcNumber($data->getCcNumber())
            //->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getExpiracaoMesCielo())
            ->setCcExpYear($data->getExpiracaoAnoCielo())
            ->setCcCid($info->encrypt($data->getCodigoSegurancaCielo())) //criptografa o código de segurança do cartão
            ->setCcNumber($info->encrypt(str_replace(' ', '',$data->getNumeroCartaoCielo()))) //criptografa o numero do cartão
        ;

        $parcela        =   $data->getParcelasCielo();
        $valorTotal     =   $info->getQuote()->getGrandTotal();
       // $valorDesconto  =   Mage::helper('apelidocielo')->getDiscountAmount($parcela,$valorTotal);
       /* if($valorDesconto > 0):
               // Mage::helper('apelidocielo')->setDiscountQuote($info,$valorDesconto);
        endif;*/
      
        //verifica se tem juros e aplica no carrinho. Se o retorno do getJurosAmount for maior que 0, aplica no quote.
        $valorJuros    =    Mage::helper('apelidocielo')->getJurosAmount($parcela,$valorTotal);
      
        
        if($valorJuros > 0):
            $info->getQuote()->setJuros($valorJuros);
            $info->getQuote()->setBaseJuros($valorJuros);    
            $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
            

        else:
            $info->getQuote()->setJuros(0.0);
            $info->getQuote()->setBaseJuros(0.0);    
            $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        
        endif;

        $info->getQuote()->save();

        return $this;
    }



    public function validate() {

        $info     = $this->getInfoInstance();
        $ccNumber = Mage::helper('core')->decrypt($info->getCcNumber());
        $bandeira = $info->getCcType();
        $validado = false;

      

        if (Mage::helper('apelidocielo')->validateCcNum($ccNumber))
        {
            $ccTypeRegExp = '/^4[0-9]{12}([0-9]{3})?$/'; //visa
            if (preg_match($ccTypeRegExp, $ccNumber) && $bandeira == 'visa')
            {
                $validado = true;
            }

            $ccTypeRegExp = '/^5[1-5][0-9]{14}$/'; //mastercard
            if (preg_match($ccTypeRegExp, $ccNumber) && $bandeira == 'mastercard')
            {
                $validado = true;
            }

            $ccTypeRegExp = '/^3[47][0-9]{13}$/'; //amex
            if (preg_match($ccTypeRegExp, $ccNumber) && $bandeira == 'amex')
            {
                $validado = true;
            }

            $ccTypeRegExp = '/^6011[0-9]{12}$/'; //discover
            if (preg_match($ccTypeRegExp, $ccNumber) && $bandeira == 'discover')
            {
                $validado = true;
            }

            $ccTypeRegExp = '/^(3[0-9]{15}|(2131|1800)[0-9]{11})$/'; //jcb
            if (preg_match($ccTypeRegExp, $ccNumber) && $bandeira == 'jcb')
            {
                $validado = true;
            }

            $ccTypeRegExp = '/^3[0,6,8]\d{12}$/'; //diners
            if (preg_match($ccTypeRegExp, $ccNumber) && $bandeira == 'diners')
            {
                $validado = true;
            }

            if ($bandeira == 'elo' && $bandeira == 'aura')
            {
                $validado = true;
            }
        } else {
            Mage::throwException(Mage::helper('payment')->__('O número do cartão digitado não é válido.'));
        }

        if(!$validado):
            Mage::throwException(Mage::helper('payment')->__('O número do cartão de crédito não é válido para bandeira selecionada. '));
        endif;

        return $this;
    }


    public function order(Varien_Object $payment, $amount){
         

        return $this;
    }

    public function authorize(Varien_Object $payment, $amount){

        $debug = Mage::getStoreConfig('payment/apelidocielo/debug');

        if($amount < 0) {
            Mage::throwException(Mage::helper('payment')->__('O valor para autorização deve ser maior que zero'));
        } else {

            $info               =   $this->getInfoInstance();


            //verifica o id da sessão
            if(!Mage::getSingleton('checkout/session')->getQuoteId()):
                $quoteId = Mage::getSingleton("adminhtml/session_quote")->getQuoteId();
            else:
                $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
            endif;
            

            //define as variáveisCcType
            $idpedido           = Mage::getModel("sales/order")->getCollection()->getLastItem()->getIncrementId();;
            $bandeira_cartao    = $info->getCcType();
            $portador           = $info->getCcOwner();
            $numcartao          = $info->decrypt($info->getCcNumber());
            $codseguranca       = $info->decrypt($info->getCcCid());
            $validade           = $info->getCcExpYear().str_pad($info->getCcExpMonth(), 2, "0", STR_PAD_LEFT); //yyyymm
            $valor              = number_format($amount, 2, '', '');
            $parcelas           = $info->getAdditionalData();
            $parcelamento       = Mage::getStoreConfig('payment/apelidocielo/tipoparcelamento');
            $softdescriptor     = Mage::getStoreConfig('payment/apelidocielo/softdescriptor');
            $campollivre        = '';
            $quoteId            = $quoteId;
            $dadosCliente       = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
            $emaildigitado      = $dadosCliente->getEmail();
            $nomedigitado       = $dadosCliente->getFirstname() . ' ' . $dadosCliente->getLastname();
            $enderecodigitadoa  = $dadosCliente->getStreet();
            $cidadedigitado     = $dadosCliente->getCity();
            $estadodigitado     = $dadosCliente->getRegion();
            $telefonedigitado   = $dadosCliente->getTelephone();
            $cepdigitado        = $dadosCliente->getPostcode();



            if($debug):
                //Esse log só funciona se a opção Ativar log em Developer > Log no admin estiver marcada
                mage::log(  "
===========   Dados do pagamento sendo enviados para autorizacao   ==========
Id do pedido:               $idpedido
Bandeira do cartao:         $bandeira_cartao
Portador do cartao:         $portador
Numero do cartao:           $numcartao
Cod de seguranca do cartao: $codseguranca
Validade do cartao:         $validade
Valor do pagamento:         $valor
Quantidade de parcelas:     $parcelas
Tipo de parcelamento:       $parcelamento
Texto do softdescriptor:    $softdescriptor
Id da quote:                $quoteId

-------------------------------------------------------------------------------
DADOS PREENCHIDOS PELO CLIENTE NO CHECKOUT

e-mail:                     $emaildigitado
Nome:                       $nomedigitado
Endereco:                   $enderecodigitadoa[0]
                            $enderecodigitadoa[1]
                            $enderecodigitadoa[2]
                            $enderecodigitadoa[3]
Cidade:                     $cidadedigitado
Estado:                     $estadodigitado
Telefone:                   $telefonedigitado
CEP:                        $cepdigitado

                        "
                ,null, 'oitoo_cielo.log');

            endif; //if($debug)

            $fluxo = Mage::getStoreConfig('payment/apelidocielo/payment_action');
            if($fluxo == 'authorize_capture'){
                //se a captura for automática, ele coloca a tag na autorizacao
                $capturaautomatica = 'true';
            } else {
                $capturaautomatica = 'false';
            }

            $cielo              =   mage::getModel('apelidocielo/cielo');

            $retornoCielo       =   $cielo->setAutorizacao( $idpedido,
                                                            $bandeira_cartao,
                                                            $portador,
                                                            $numcartao,
                                                            $codseguranca,
                                                            $validade,
                                                            $valor,
                                                            $parcelas,
                                                            $parcelamento,
                                                            $softdescriptor,
                                                            $campollivre,
                                                            $capturaautomatica);


            //guarda o TID da transação no campo PONUMBER
            $tid = (string)$retornoCielo->tid;

          


            if(($retornoCielo->autorizacao->codigo == 4 && $retornoCielo->autorizacao->lr == '00') || $retornoCielo->captura->codigo == 6){

                Mage::dispatchEvent(
                    'oitoo_cielo_log',
                    array('quote_id'=>(string)$quoteId,
                        'codigo'=>(string)$retornoCielo->autorizacao->codigo,
                        'texto'=>(string)$retornoCielo->autorizacao->mensagem,
                        'tid'=>$retornoCielo->tid
                    )
                );

                 //verifica se teve captura e salva os dados. Se houve captura é pq ela é automática
                if($retornoCielo->captura->codigo == 6){
                    $codigocaptura = (string)$retornoCielo->captura->codigo;
                    $mensagemcaptura = (string)$retornoCielo->captura->mensagem;
                }

                if(isset($tid) && $tid != '' && $tid != NULL):
                    $payment->setCcTransId($tid);
                    $payment->setAdditionalInformation('autorizacao_codigo',(string)$retornoCielo->autorizacao->codigo);
                    $payment->setAdditionalInformation('autorizacao_mensagem',(string)$retornoCielo->autorizacao->mensagem);
                    $payment->setAdditionalInformation('autorizacao_lr',(string)$retornoCielo->autorizacao->lr);
                    $payment->setAdditionalInformation('autorizacao_valor',(string)$retornoCielo->autorizacao->valor);

                    if(isset($codigocaptura)):
                        $payment->setAdditionalInformation('captura_codigo', $codigocaptura);
                        $payment->setAdditionalInformation('captura_mensagem', $mensagemcaptura);
                    endif;

                    $payment->save();
                endif;

                return $this; //a compra foi autorizada. O cliente vai para a página de sucesso
            } else {
                if(isset($retornoCielo->codigo)) {

                    //guarda o log no módulo de logs
                    Mage::dispatchEvent(
                        'oitoo_cielo_log',
                        array('quote_id'=>(string)$quoteId,
                            'codigo'=>(string)$retornoCielo->codigo,
                            'texto'=>(string)$retornoCielo->mensagem,
                            'tid'=>'Não existente'
                        )
                    );

                    Mage::throwException(Mage::helper('payment')->__('Erro num: ' . $retornoCielo->codigo . ' - ' .  $retornoCielo->mensagem));
                } else {

                    //guarda o log no módulo de logs
                    Mage::dispatchEvent(
                        'oitoo_cielo_log',
                        array('quote_id'=>(string)$quoteId,
                            'codigo'=>(string)$retornoCielo->autorizacao->codigo,
                            'texto'=>(string)$retornoCielo->autorizacao->mensagem,
                            'tid'=>(string)$retornoCielo->tid
                        )
                    );

                    Mage::throwException(Mage::helper('payment')->__('A operadora não autorizou seu pagamento pelo seguinte motivo: ' . $retornoCielo->autorizacao->codigo . ' - ' .  $retornoCielo->autorizacao->mensagem . ' LR: ' . $retornoCielo->autorizacao->lr . '  Você ainda pode tentar efetuar um novo pagamento. Basta alterar os dados na aba "Informações de pagamento". Qualquer dúvida entre em contato conosco.'));
                }
            }


        }

        return $this;
    }


    /**========================================================
     * Prepare info instance for save
     * Prepara a instancia info para receber os dados do cartão
     * @return Mage_Payment_Model_Abstract
     ==========================================================*/
    public function prepareSave()
    {
 
 
    }


    /**
     * Capture payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        
        if($payment->getCcTransId() == null || $payment->getCcTransId() == ''){
            //se não for a área administrativa e está sendo executado o método capture, então a ação é autorizar e capturar automaticamente
            $this->authorize($payment, $amount);
            return $this;
        }

        if (!$this->canCapture()) {
           Mage::throwException(Mage::helper('payment')->__('Esse pedido não pode ser capturado.'));
        }

        $tid     =   $payment->getCcTransId();
        $valor   =   number_format($amount, 2, '', '');


        $debug = Mage::getStoreConfig('payment/apelidocielo/debug');
        if($debug):
            //Esse log só funciona se a opção Ativar log em Developer > Log no admin estiver marcada
            mage::log("
===========   Dados do pagamento sendo enviados para captura   ==========
                      ", null, 'oitoo_cielo.log');
        endif;

        $cielo   =   mage::getModel('apelidocielo/cielo');
        $retornoCaptura = $cielo->setCaptura($tid,$valor);

        if($retornoCaptura->captura->codigo == 6){
            $payment->setAdditionalInformation('captura_codigo',(string)$retornoCaptura->captura->codigo);
            $payment->setAdditionalInformation('captura_mensagem',(string)$retornoCaptura->captura->mensagem);
            $payment->save();

            //faz o log de sucesso
            Mage::dispatchEvent(
                'oitoo_cielo_log',
                array('quote_id'=>(string)$payment->getOrder()->getQuoteId(),
                    'codigo'=>(string)$retornoCaptura->captura->codigo,
                    'texto'=>(string)$retornoCaptura->captura->mensagem,
                    'tid'=>$retornoCaptura->tid
                )
            );


            return $this; //a compra foi capturada.
        } else {
            if(isset($retornoCaptura->codigo)) {
                Mage::throwException(Mage::helper('payment')->__('Erro num: ' . $retornoCaptura->codigo . ' - ' .  $retornoCaptura->mensagem));
            } else {
                Mage::throwException(Mage::helper('payment')->__('Não foi possivel capturar a autorização pelo seguinte motivo: ' . $retornoCaptura->captura->codigo . ' - ' .  $retornoCaptura->captura->mensagem));
            }
        }

        return false;
    }



    public function cancelamento($observer)
    {

        $payment =   $observer->getEvent()->getPayment();
        $tid     =   $payment->getCcTransId();
        $valor   =   number_format($payment->getAmountAuthorized(), 2, '', '');


        if($payment->getMethod() != 'apelidocielo'){
            return true;
        }

        $debug = Mage::getStoreConfig('payment/apelidocielo/debug');
        if($debug):
            //Esse log só funciona se a opção Ativar log em Developer > Log no admin estiver marcada
            mage::log(  "
===========   Dados do pagamento sendo enviados para autorizacao   ==========

        ", null, 'oitoo_cielo.log');
        endif;

        $cielo   =   mage::getModel('apelidocielo/cielo');
        $retornoCancelamento = $cielo->setCancelamento($tid,$valor);

        if($retornoCancelamento->cancelamentos->cancelamento->codigo == 9){
            $payment->setAdditionalInformation('cancelamento_codigo',(string)$retornoCancelamento->cancelamentos->cancelamento->codigo);
            $payment->setAdditionalInformation('cancelamento_mensagem',(string)$retornoCancelamento->cancelamentos->cancelamento->mensagem);
            $payment->save();

            //faz o log de sucesso
            Mage::dispatchEvent(
                'oitoo_cielo_log',
                array('quote_id'=>(string)$payment->getOrder()->getQuoteId(),
                    'codigo'=>'0',
                    'texto'=>'Pedido do Cartão cancelado com sucesso',
                    'tid'=>$retornoCancelamento->tid
                )
            );

            return $this; //a compra foi cancelada.
        } else {
            if(isset($retornoCancelamento->codigo)) {
                Mage::throwException(Mage::helper('payment')->__('Erro num: ' . $retornoCancelamento->codigo . ' - ' .  $retornoCancelamento->mensagem));
            } else {
                Mage::throwException(Mage::helper('payment')->__('Não foi possivel cancelar a autorização pelo seguinte motivo: ' . $retornoCancelamento->codigo . ' - ' .  $retornoCancelamento->mensagem));
            }
        }


        return $this;
    }


    public function estorno($observer)
    {

        if(!is_object($observer->getEvent()->getCreditmemo()->getInvoice())){
            //apresenta um erro caso a pessoa não escolha a fatura no admin
            Mage::throwException(Mage::helper('payment')->__('Não foi possivel identificar a fatura. É preciso escolher uma fatura para o estorno. '));
        }
        $orderId    = $observer->getEvent()->getCreditmemo()->getInvoice()->getOrderId();
        $order      = mage::getModel('sales/order')->load($orderId);
        $tid     =   $order->getPayment()->getCcTransId();
        $payment   = $order->getPayment();

        if($payment->getMethod() != 'apelidocielo'){
            return true;
        }

        $creditmemo = $observer->getEvent()->getCreditmemo();
        $valor   =   number_format($creditmemo->getGrandTotal(), 2, '', '');

        $debug = Mage::getStoreConfig('payment/apelidocielo/debug');
        if($debug):
            //Esse log só funciona se a opção Ativar log em Developer > Log no admin estiver marcada
            mage::log(  "
===========   Dados do credito sendo enviados para estorno   ==========

        ", null, 'oitoo_cielo.log');
        endif;

        $cielo   =   mage::getModel('apelidocielo/cielo');
        $retornoestorno = $cielo->setCancelamento($tid,$valor);

        if($retornoestorno->autorizacao->codigo == 9 || $retornoestorno->autorizacao->codigo == 6){
            //quando o pagamento é capturado não é mais possivel editar as infromações

            //faz o log de sucesso
            Mage::dispatchEvent(
                'oitoo_cielo_log',
                array('quote_id'=>(string)$payment->getOrder()->getQuoteId(),
                    'codigo'=>$retornoestorno->autorizacao->codigo,
                    'texto'=>'Pedido estornado com sucesso! ',
                    'tid'=>$retornoestorno->tid
                )
            );


            return $this; //o crédito foi estornado.
        } else {
            if(isset($retornoestorno->codigo)) {
                Mage::throwException(Mage::helper('payment')->__('Erro num: ' . $retornoestorno->codigo . ' - ' .  $retornoestorno->mensagem));
            } else {
                Mage::throwException(Mage::helper('payment')->__('Não foi possivel efetuar o estorno'));
            }
        }

        return $this;
    }

    public function getOrderPlaceRedirectUrl($orderId = 0)
    {
        $params = array();
        $params['_secure'] = true;
        return Mage::getUrl('frontendcielo/checkout/success', $params);
    }



}
