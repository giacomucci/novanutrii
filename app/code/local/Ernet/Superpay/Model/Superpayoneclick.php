<?php
class Ernet_Superpay_Model_Superpayoneclick extends Mage_Payment_Model_Method_Cc
{
	const LIVE_WSI = 'https://superpay2.superpay.com.br/checkout/servicosPagamentoOneClickWS.Services?wsdl';
	const TEST_WSI = 'https://homologacao.superpay.com.br/superpay/servicosPagamentoCompletoWS.Services?wsdl';
    
	const METHOD_CODE = 'superpay_oneclick';

    protected $_code = self::METHOD_CODE;
    protected $_banks = NULL;
    protected $urlPagamento = false;

    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    protected $_isInitializeNeeded      = false;
	
	
    protected $_forceSuccess = true;
    protected $_formBlockType = 'superpay/superpayoneclick_form';
    protected $_cctype = NULL;
    protected $_payment = NULL;
    protected $_allowCurrencyCode = array('BRL');
    protected $_isTransactionFraud = 'is_transaction_fraud';
	
	public function getCCType($id = NULL) {
        if ($this->_cctype == NULL) {
            $this->_cctype = array(
                120 => "Visa (Cielo)",
                121 => "MasterCard (Cielo)",
				122 => "American Express (Cielo)",
                123 => "ELO (Cielo)",
				124 => "Diners (Cielo)",
				125 => "Discover (Cielo)",
				126 => "Aura (Cielo)",
				127 => "JCB (Cielo)",
				129 => "Visa Electron (Cielo)",				
                60 => "JCB (Moset3)",
                61 => "MasterCard (Moset3)",
                90 => "Visa (Komerci)",
                91 => "MasterCard (Komerci)",
                92 => "Diners (Komerci)",
                11 => "American Express (2party)",
                3 => "Visa (TEF)",
                6 => "MasterCard (TEF)",
                8 => "Diners (TEF)",
                12 => "American Express (TEF)",
                13 => "Hypercard (TEF)",
                14 => "Sorocred (TEF)",
                15 => "JCB (TEF)"
            );
        }
        if (!empty($id))
            return $this->_cctype[$id];
        else
            return $this->_cctype;
    }
		   
    public function void(Varien_Object $payment)
      {
			Mage::log('## [SuperPay] Capturing void.... ', null, 'ernet.log');
		
			return $this;
      }
	  
    public function capture(Varien_Object $payment, $amount) {
        		
		Mage::log('## [SuperPay] Capturing starting.... ', null, 'ernet.log');
				
        return $this;
    }
	
    public function authorize(Varien_Object $payment, $amount) {
		
			ini_set('soap.wsdl_cache_enabled',0);
			ini_set('soap.wsdl_cache_ttl',0);
		
			Mage::log('## [SuperPay] Authorizing starting.... ', null, 'ernet.log');
		
			//$payment->setTrxtype(self::TRXTYPE_AUTH_ONLY);
									
			$error = false;
			$payment->setAmount($amount);									
			$request = $this->_buildRequest($payment);			
						
			if ($request) {
				$payment->setStatus(self::STATUS_SUCCESS);
			}else{
				$payment->setStatus(self::STATUS_DECLINED);
			}
						
		return $this;
    }
	
	protected function _buildRequest(Varien_Object $payment) {
				
		$username = "" . $this->getConfigData('username');
        $password = "" . $this->getConfigData('password');
        $code = "" . $this->getConfigData('code');
		$token = "";
		
		$order = $payment->getOrder();
        $this->setStore($order->getStoreId());

        $this->_payment = $order;
	
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();

        $order_id = $order->getIncrementId();
        Mage::log('## [SuperPay] Order ID: ' . $order_id, null, 'ernet.log');        
        
		$_SESSION['is_boleto'] = false;		

        $service = $payment->getCcType();

        $year = substr($payment->getCcExpYear(), 0, 4);
        $month = sprintf("%02d", substr($payment->getCcExpMonth(), 0, 2));

        $currency = "BRL";
        $currency_code = $order->getBaseCurrencyCode();
        $amount = $payment->getAmount();

		$customer = $billing;
		
        if ($currency_code != $currency) {
            //if currency code is not allowed currency code, use USD as default
            $rate = Mage::getSingleton('directory/currency')
                    ->load($order->getBaseCurrencyCode())
                    ->getAnyRate($currency);
            if ($rate > 0)
                $amount *= $rate;
            $currency_code = $currency;
        }

        $amount = round($amount * 100);
		
		//Indica que o usuário cadastrando um novo token
		if ($_POST["payment"]["token"] == 2){
			try{
				$_SESSION['is_oneclick_novo'] = true;
				
				$request_token = array();
				
				$request_token["dadosOneClick"]["codigoEstabelecimento"] = $code;
				$request_token["usuario"] = $username;
				$request_token["senha"] = $password;
			
				$request_token["dadosOneClick"]["nomeTitularCartaoCredito"] = $this->cleanString($payment->getCcOwner());
				$request_token["dadosOneClick"]["numeroCartaoCredito"] = $payment->getCcNumber();
				$request_token["dadosOneClick"]["codigoSeguranca"] = trim($payment->getCcCid());
				$request_token["dadosOneClick"]["dataValidadeCartao"] = "$month/$year";
				$request_token["dadosOneClick"]["emailComprador"] = $customer->getEmail();
				$request_token["dadosOneClick"]["formaPagamento"] = $service;			

				$wsdl_url = $this->_getGatewayUrl();
				
				Mage::log('### [SuperPay] Sending transaction to SuperPay - URL:' . $wsdl_url, null, 'ernet.log');		
				$client = new SoapClient($wsdl_url, array('soap_version' => SOAP_1_1, 'trace' => 1));

				try {												
					$tran = $client->cadastraPagamentoOneClick($request_token);
					
					Mage::log('### [SuperPay] Request Build-CriacaoTOKEN.' . $client->__getLastRequest() , null, 'ernet.log');
					Mage::log('### [SuperPay] Transaction returned.', null, 'ernet.log');
				} catch (SoapFault $fault) {
					Mage::log('### [SuperPay] Request Build-CriacaoTOKEN.' . $client->__getLastRequest() , null, 'ernet.log');
					Mage::log('## [SuperPay] Exception: ' . $fault, null, 'ernet.log');
					
					Mage::throwException(
							Mage::helper('superpay')->__('Nao foi possivel realizar o pagamento. Por favor, tente mais tarde. [ %s ]', "[" . $fault->faultcode . "] " . $fault->faultstring)
					);
				}

				Mage::log('### [SuperPay] Retorno Token: ' . $tran->return, null, 'ernet.log');
		
				//Busca Retorno do Token
				$token = $tran->return;		
			} catch (Exception $e) {
				Mage::log('## [SuperPay] Exception - Token: ' . $e->getMessage(), null, 'ernet.log');

				Mage::throwException(
						Mage::helper('superpay')->__('Nao foi possivel registrar o token. Por favor, tente mais tarde. [ %s ]', "[" . $e->getMessage() . "] " . $e->getMessage())
				);
			}			
		} else {
			Mage::log('### [SuperPay] Cadastro de Token ou Uso de Token: ' . $_POST["payment"]["token_numero"], null, 'ernet.log');		
			$token = $_POST["payment"]["token_numero"];
		}
		
		$_SESSION['token'] = $token;
		
        $parcelamento = $_POST["payment"]["parcelamento"];
		$payment->setAdditionalInformation('parcelamento', $parcelamento);
        Mage::log('### [SuperPay] Parcelamento: ' . $parcelamento, null, 'ernet.log');

        $request = array();

        $request["usuario"] = $username;
        $request["senha"] = $password;

        $request["transacao"]["codigoEstabelecimento"] = $code;
        $request["transacao"]["codigoFormaPagamento"] = $service;
		
        $request["transacao"]["numeroTransacao"] = $order_id;
		
		Mage::log('### [SuperPay] NumeroTransacao = ' . $order_id , null, 'ernet.log');
        
        $request["transacao"]["valor"] = $amount;
        $request["transacao"]["valorDesconto"] = $order->getDiscountAmount();        
        $request["transacao"]["IP"] = $_SERVER["REMOTE_ADDR"];
        $request["transacao"]["origemTransacao"] = 1;
		$request["transacao"]["idioma"] = 'pt';
        $request["transacao"]["parcelas"] = $parcelamento;
        $request["transacao"]["taxaEmbarque"] = 0;
       
        $request["transacao"]["dadosUsuarioTransacao"]["codigoCliente"] = (int) Mage::getSingleton('customer/session')->getCustomerId();
		$request["transacao"]["token"] = $token;
       
        $documento_comprador = $order->getCustomerTaxvat(); 
        Mage::log('### [SuperPay] CPF/CNPJ = ' . $documento_comprador , null, 'ernet.log');
        
        if (strlen($documento_comprador)==11) {
            $request["transacao"]["dadosUsuarioTransacao"]["tipoCliente"] = "1";
        } else {
            $request["transacao"]["dadosUsuarioTransacao"]["tipoCliente"] = "2";
        }

        $request["transacao"]["dadosUsuarioTransacao"]["documentoComprador"] = $documento_comprador;
        
        $request["transacao"]["dadosUsuarioTransacao"]["nomeComprador"] = $this->cleanString($customer->getName());

		Mage::log('### [SuperPay] Nome Comprador: ' . $this->cleanString($customer->getName()), null, 'ernet.log');
		
		
        if ($order->getCustomerGender() == 1)
            $sexo_comprador = 'M';
        else
            $sexo_comprador = 'F';        
        
        Mage::log('### [SuperPay] Gender = ' . $sexo_comprador, null, 'ernet.log');
        
        
        $request["transacao"]["dadosUsuarioTransacao"]["sexoComprador"] = $sexo_comprador;
        
		if ($order->getCustomerDob() != '') {
			$customer_dob = split(' ', $order->getCustomerDob());
			$customer_dob = split('-', $customer_dob[0]);
			$customer_dob =  $customer_dob[2] . '/' . $customer_dob[1] . '/' . $customer_dob[0];
		} else {
			$customer_dob = '01/01/1990';
		}
		
        $request["transacao"]["dadosUsuarioTransacao"]["dataNascimentoComprador"] = $customer_dob;
        
        $request["transacao"]["dadosUsuarioTransacao"]["emailComprador"] = $customer->getEmail();

		Mage::log('### [SuperPay] Telefone - Magento: ' . $billing->getTelephone(), null, 'ernet.log');
				
		$ddd_comprador = substr($billing->getTelephone(), 1, 2);
        $request["transacao"]["dadosUsuarioTransacao"]["dddComprador"] = $ddd_comprador;
		Mage::log('### [SuperPay] DDD Comprador: ' . $ddd_comprador, null, 'ernet.log');
		
		$tel_comprador = substr($billing->getTelephone(), 5);
		$tel_comprador = str_replace("-","",$tel_comprador);
		$tel_comprador = str_replace("_","",$tel_comprador);
		
        $request["transacao"]["dadosUsuarioTransacao"]["telefoneComprador"] = $tel_comprador;
		Mage::log('### [SuperPay] Telefone Comprador: ' . $tel_comprador, null, 'ernet.log');
		
		$ddd_entrega = substr($billing->getTelephone(), 1, 2);
        $request["transacao"]["dadosUsuarioTransacao"]["dddAdicionalComprador"] = $ddd_entrega;				
		
		$tel_entrega = substr($billing->getTelephone(), 5);
		$tel_entrega = str_replace("-","",$tel_comprador);
		$tel_entrega = str_replace("_","",$tel_comprador);
		
        $request["transacao"]["dadosUsuarioTransacao"]["telefoneAdicionalComprador"] = $tel_entrega;
		
        $request["transacao"]["dadosUsuarioTransacao"]["enderecoComprador"] = $this->cleanString($billing->getStreet(1));
        $request["transacao"]["dadosUsuarioTransacao"]["numeroEnderecoComprador"] = $billing->getStreet(2);
        $request["transacao"]["dadosUsuarioTransacao"]["bairroEnderecoComprador"] = $this->cleanString($billing->getStreet(4));
        $request["transacao"]["dadosUsuarioTransacao"]["complementoEnderecoComprador"] = $this->cleanString($billing->getStreet(3));
        $request["transacao"]["dadosUsuarioTransacao"]["cidadeEnderecoComprador"] = $this->cleanString($billing->getCity());
        $request["transacao"]["dadosUsuarioTransacao"]["cepEnderecoComprador"] = $billing->getPostcode();
        $request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoComprador"] = $this->cleanString($billing->getRegion());
		
		Mage::log('### [SuperPay] Dados de Envio do pedido.', null, 'ernet.log');
		$request["transacao"]["dadosUsuarioTransacao"]["enderecoEntrega"] = $this->cleanString($billing->getStreet(1));
		$request["transacao"]["dadosUsuarioTransacao"]["numeroEnderecoEntrega"] = $billing->getStreet(2);
		$request["transacao"]["dadosUsuarioTransacao"]["bairroEnderecoEntrega"] = $this->cleanString($billing->getStreet(4));
		$request["transacao"]["dadosUsuarioTransacao"]["complementoEnderecoEntrega"] = $this->cleanString($billing->getStreet(3));
		$request["transacao"]["dadosUsuarioTransacao"]["cidadeEnderecoEntrega"] = $this->cleanString($billing->getCity());
		$request["transacao"]["dadosUsuarioTransacao"]["cepEnderecoEntrega"] = $billing->getPostcode();
		$request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoEntrega"] = $this->cleanString($billing->getRegion());		
	
		
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneAdicionalComprador"] = "1";
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneAdicionalEntrega"] = "1";
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneComprador"] = "1";
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneEntrega"] = "1";

        Mage::log('### [SuperPay] Adding items into superpay request.', null, 'ernet.log');
        $items = $order->getAllItems();
        $i = 0;
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
			Mage::log('### [SuperPay] Adding SKU -> '.$item->getSku(), null, 'ernet.log');
			$product_code = $item->getSku();
            $request["transacao"]["itensDoPedido"][$i]["codigoProduto"] = $product_code;
			Mage::log('## [SuperPay] Product Code: '.$product_code, null, 'ernet.log');
			
            $request["transacao"]["itensDoPedido"][$i]["codigoCategoria"] = '0';
			
			$product_name = $this->cleanString($item->getName());
            $request["transacao"]["itensDoPedido"][$i]["nomeProduto"] = $product_name;
			Mage::log('## [SuperPay] Product Name: '.$product_name, null, 'ernet.log');
			
			$product_qty = $item->getQtyToInvoice();
            $request["transacao"]["itensDoPedido"][$i]["quantidadeProduto"] = $product_qty;
			Mage::log('## [SuperPay] Product Qty: '.$product_qty, null, 'ernet.log');
			
			$product_unit_price = round($item->getPrice() * 100);
            $request["transacao"]["itensDoPedido"][$i]["valorUnitarioProduto"] = $product_unit_price;
			Mage::log('## [SuperPay] Product Unit Price: '.$product_unit_price, null, 'ernet.log');
			
            $request["transacao"]["itensDoPedido"][$i]["nomeCategoria"] = "Loja";
            $i++;
        }

		$ipn = Mage::getUrl('superpay/superpay/ipn');
        $request["transacao"]["urlCampainha"] = $ipn;
		Mage::log('## [SuperPay] IPN: '.$ipn, null, 'ernet.log');

        $wsdl_url = $this->_getGatewayUrl();
		Mage::log('## [SuperPay] WSDL URL: '.$wsdl_url, null, 'ernet.log');
        if (!$wsdl_url) {
            Mage::throwException(
                    Mage::helper('superpay')->__('Nao existem meios de pagamento disponiveis no momento.', "[SPERR:012]")
            );
        }

        $client = new SoapClient($wsdl_url, array('soap_version' => SOAP_1_1, 'trace' => 1));

        try {							
            Mage::log('### [SuperPay] Sending transaction to SuperPay', null, 'ernet.log');
			
			$tran = $client->pagamentoOneClick($request);
			
			Mage::log('### [SuperPay] Request Build.' . $client->__getLastRequest() , null, 'ernet.log');
			
            Mage::log('### [SuperPay] Transaction returned.', null, 'ernet.log');
        } catch (SoapFault $fault) {
			Mage::log('### [SuperPay] Request Build-CriacaoTOKEN.' . $client->__getLastRequest() , null, 'ernet.log');
				
            Mage::log('## [SuperPay] Exception: ' . $fault, null, 'ernet.log');
         
            Mage::throwException(
                    Mage::helper('superpay')->__('Nao foi possivel realizar o pagamento. Por favor, tente mais tarde. [ %s ]', "[" . $fault->faultcode . "] " . $fault->faultstring)
            );
        }

        $status = $tran->return->statusTransacao;
        $this->TransactionId = $tran->return->numeroTransacao;
		
        Mage::log('### [SuperPay] Status Superpay: ' . $status, null, 'ernet.log');
        Mage::log('### [SuperPay] Transaction ID Superpay: ' . $this->TransactionId, null, 'ernet.log');
		
		// ### STATUS PENDENTE ##
        if ( $status == 2 || $status == 5 || $status == 8 || $status == 15 || $status == 18 || $status == 30 ) {

			Mage::log('### [SuperPay] Status returned, transaction should be set to PENDING...', null, 'ernet.log');
			//$payment->setIsTransactionPending(true);
			$payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
			$order_state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;

		// ### STATUS FINAL - AUTORIZADO E CAPTURADO ##
        } elseif ($status == 1) {
		
			Mage::log('### [SuperPay] Status returned, transaction AUTHORIZED and CAPTURED...', null, 'ernet.log');
			$order_state = Mage_Sales_Model_Order::STATE_PROCESSING;
			$payment->setTransactionId($this->TransactionId);
            $payment->setIsTransactionClosed(1);
			
			Mage::log('### [SuperPay] Faturar Pedido?' . $this->getConfigData('createOrder'), null, 'ernet.log');
		
			if ($this->getConfigData('createOrder') == true) {
				$this->_createInvoice($order);
			}
		
		// ### STATUS FINAL - FALHA ##
		} elseif ($status == 3 || $status == 9 || $status == 17 || $status == 31) {

			Mage::log('### [SuperPay] Status returned, transaction should be ABORTED...', null, 'ernet.log');
			Mage::log($tran->return->mensagemVenda, null, 'ernet.log');
			          	
			$order_state = Mage_Sales_Model_Order::STATE_CANCELED;			
 
            Mage::throwException(
					Mage::helper('superpay')->__('Erro: %s', 'A solicitacao de pagamento nao foi autorizada pela administradora do seu cartao de credito. Verifique o valor e parcelamento selecionado e tente novamente.')
                    //Mage::helper('superpay')->__('Erro: %s', $tran->return->mensagemVenda)
            );
            return false;
        } else {
		
			Mage::log('### [SuperPay] Status returned, transaction should be ABORTED...', null, 'ernet.log');
			Mage::log($tran->return->mensagemVenda, null, 'ernet.log');
			          	
			$order_state = Mage_Sales_Model_Order::STATE_CANCELED;			
 
            Mage::throwException(
					Mage::helper('superpay')->__('Erro: %s', 'A solicitacao de pagamento nao foi autorizada pela administradora do seu cartao de credito. Verifique o valor e parcelamento selecionado e tente novamente.')
                    //Mage::helper('superpay')->__('Erro: %s', $tran->return->mensagemVenda)
            );
            return false;
			
		}
		
		
        // ## Store authorization code inside order
        
		$auth_code = $tran->return->autorizacao;
		$mensagem_venda = $tran->return->mensagemVenda;
		
        $auth_comment = "Autorizacao: ".$auth_code." | Mensagem Venda: ".$mensagem_venda.".";
        
        $order->addStatusHistoryComment($auth_comment, $order_state)
        	->setIsVisibleOnFront(true)
        	->setIsCustomerNotified(false);  
        
        //$payment->setAdditionalInformation('auth_code', $auth_code);        
		$payment->save();
		
        return $this;
    }
	
	public function getConfigDataSuperpay($field) {
        $path = 'payment/superpay/' . $field;
        $config = Mage::getStoreConfig($path);

        return $config;
    }

    
    private function cleanString($str) {
		
		$str = $this->checkNullString($str);		
		return strtr(utf8_decode($str), utf8_decode("áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ"), "aaaaeeiooouucAAAAEEIOOOUUC");				                
    }

    private function checkNullString($str) {

        if (trim($str) == '' || is_null($str)) {
            return '-';
        } else {
            return $str;
        }
    }

    public function validate() {

        Mage::log('### Credit card validation.', null, 'ernet.log');
		return true;
		
    }

    private function riskCheck($order) {

        Mage::log('### Risk Check starting....', null, 'ernet.log');

        $block = Mage::getModel('antifraude/MClearSale');
        $risk_status = $block->getRiskScore($order);

        Mage::log('### Risk Check finished.', null, 'ernet.log');

        return $risk_status;
    }
    
    public function getMethodCode(){
        return $this->_code;
    }
    
    public function getOrderCCType(){
        return $this->__get('info_instance')->getData('cc_type');
    }    
    
    private function _getGatewayUrl() {

        $mode = $this->getConfigData('mode');

        Mage::log('### [SuperPay OneClick] Gateway mode: ' . $mode, null, 'ernet.log');

        if ($mode == 'live') {
            return self::LIVE_WSI;
        } else if ($mode == 'test') {
            return self::TEST_WSI;
        } else {
            Mage::log('### [SuperPay OneClick] Invalid mode selected. Aborting...', null, 'ernet.log');
            return false;
        }
    }
	
	protected function _createInvoice($order){	
	
		try {
		
			Mage::log('## Inicio Criar Invoice!', null, 'ernet.log');
			
			if(!$order->canInvoice())
			{
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
			}
			 
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
			 
			if (!$invoice->getTotalQty()) {
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
			}			 	
			
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
			//$invoice->register();
			$transactionSave = Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder());
			 
			$transactionSave->save();
						
			Mage::log('## Invoice criada com sucesso!', null, 'ernet.log');
		}
		catch (Mage_Core_Exception $e) {
			Mage::log('## Erro!!', null, 'ernet.log');
			Mage::log('## Erro ao criar Invoice com sucesso!' . $e, null, 'ernet.log');
		}	
	}
}
?>
