<?php
class Ernet_Superpay_Model_Superpaybanco extends Mage_Payment_Model_Method_Abstract {
    
	const LIVE_WSI = 'https://superpay2.superpay.com.br/checkout/servicosPagamentoCompletoWS.Services?wsdl';
    const TEST_WSI = 'https://homologacao.superpay.com.br/superpay/servicosPagamentoCompletoWS.Services?wsdl';

    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT = 'CREDIT';
    const REQUEST_TYPE_VOID = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    const METHOD_CODE = 'superpay_banco';

    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'superpay/superpaybanco_form';
    protected $_banks = NULL;
    protected $urlPagamento = false;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = false;
    protected $_canOrder = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = true;
    protected $_forceSuccess = false;
    protected $_transactionId = null;

    public function assignData($data) {     
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount) {

	
		Mage::log('### [SuperPay Banco] authorize called.', null, 'ernet.log');
	
        $_REQUEST["urlRedirect"] = "";

        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();

        $order_id = $order->getIncrementId();

        Mage::log('### [SuperPay Banco] Order ID = ' . $order_id, null, 'ernet.log');

        $username = "" . $this->getConfigData('username');
        $password = "" . $this->getConfigData('password');
        $language = "" . $this->getConfigData('lang');
        $code = "" . $this->getConfigData('code');
        $mode = "" . $this->getConfigData('mode');
        $expiry = "" . $this->getConfigData('expiry');

        $service = (int) $_POST["payment"]["service"];
        $_SESSION['service_code'] = $service;
		Mage::log('### [SuperPay Banco] Service Code = ' . $service, null, 'ernet.log');
		
        $_SESSION['is_boleto'] = true;
		Mage::log('### [SuperPay Banco] Boleto? = true', null, 'ernet.log'); 
		
        if (!$service) {
            Mage::throwException(
                    Mage::helper('superpay')->__('Por favor selecione um servico valido.')
            );
        }

        $currency = "BRL";
        $currency_code = $order->getBaseCurrencyCode();
        //$amount = $payment->getAmount();

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
        Mage::log('### [SuperPay Banco] Valor da Venda = ' . $amount, null, 'ernet.log');
        
		$shipping_ammount = $order->getShippingAmount() * 100;
        Mage::log('### [SuperPay Banco] Valor do Frete = ' . $shipping_ammount, null, 'ernet.log');
		
		$discount = round($order->getDiscountAmount()*100*-1);
		Mage::log('### [SuperPay Banco] Valor do Desconto = ' . $discount, null, 'ernet.log');

        $request = array();

        $request["usuario"] = $username;
        $request["senha"] = $password;

        $request["transacao"]["codigoEstabelecimento"] = $code;
        $request["transacao"]["codigoFormaPagamento"] = $service;

        $request["transacao"]["numeroTransacao"] = $order_id;
        $request["transacao"]["valor"] = $amount;
        $request["transacao"]["valorDesconto"] = $discount;
        
        $request["transacao"]["IP"] = $_SERVER["REMOTE_ADDR"];
        $request["transacao"]["origemTransacao"] = 1;
        $request["transacao"]["idioma"] = 'pt';
        $request["transacao"]["parcelas"] = 0;
        $request["transacao"]["taxaEmbarque"] = 0;

		Mage::log('### [SuperPay Banco] Dias para vencimento = ' . $expiry, null, 'ernet.log');
		$vencimento_boleto = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + (int)$expiry, date("Y")));
		
		$request["transacao"]["vencimentoBoleto"] = $vencimento_boleto;
		Mage::log('### [SuperPay Banco] Vencimento Boleto = ' . $vencimento_boleto, null, 'ernet.log');
		
        //$customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer = $billing;  //this->getCustomer();

        $request["transacao"]["dadosUsuarioTransacao"]["codigoCliente"] = (int) Mage::getSingleton('customer/session')->getCustomerId();

        $documento_comprador = $order->getCustomerTaxvat();
		$request["transacao"]["dadosUsuarioTransacao"]["documentoComprador"] = $documento_comprador;
        Mage::log('### [SuperPay Banco] CPF/CNPJ = ' . $documento_comprador, null, 'ernet.log');

        if (strlen($documento_comprador) == 11) {
            $request["transacao"]["dadosUsuarioTransacao"]["tipoCliente"] = "1";
        } else {
            $request["transacao"]["dadosUsuarioTransacao"]["tipoCliente"] = "2";
        }
		
        if ($order->getCustomerGender() == 1)
            $sexo_comprador = 'M';
        else
            $sexo_comprador = 'F';

        Mage::log('### [SuperPay Banco] Gender = ' . $sexo_comprador, null, 'ernet.log');

        $request["transacao"]["dadosUsuarioTransacao"]["nomeComprador"] = $this->cleanString($customer->getName());
        $request["transacao"]["dadosUsuarioTransacao"]["sexoComprador"] = $sexo_comprador;

		if ($order->getCustomerDob() != '') {
			$customer_dob = split(' ', $order->getCustomerDob());
			$customer_dob = split('-', $customer_dob[0]);
			$customer_dob =  $customer_dob[2] . '/' . $customer_dob[1] . '/' . $customer_dob[0];
		} else {
			$customer_dob = '01/01/1990';
		}
		Mage::log('### [SuperPay Banco] DoB = ' . $customer_dob, null, 'ernet.log');
		
        $request["transacao"]["dadosUsuarioTransacao"]["dataNascimentoComprador"] = $customer_dob;
		
		$customer_email = $customer->getEmail();
        $request["transacao"]["dadosUsuarioTransacao"]["emailComprador"] = $customer_email;
		Mage::log('### [SuperPay Banco] e-mail = ' . $customer_dob, null, 'ernet.log');
		
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
        $request["transacao"]["dadosUsuarioTransacao"]["numeroEnderecoComprador"] = $this->cleanString($billing->getStreet(2));
        $request["transacao"]["dadosUsuarioTransacao"]["bairroEnderecoComprador"] = $this->cleanString($billing->getStreet(4));
        $request["transacao"]["dadosUsuarioTransacao"]["complementoEnderecoComprador"] = $this->cleanString($billing->getStreet(3));
        $request["transacao"]["dadosUsuarioTransacao"]["cidadeEnderecoComprador"] = $this->cleanString($billing->getCity());
        $request["transacao"]["dadosUsuarioTransacao"]["cepEnderecoComprador"] = $this->cleanString($billing->getPostcode());
        $request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoComprador"] = $this->cleanString($billing->getRegion());

		Mage::log('### [SuperPay] Dados de Envio do pedido.', null, 'ernet.log');
		$request["transacao"]["dadosUsuarioTransacao"]["enderecoEntrega"] = $this->cleanString($billing->getStreet(1));
		$request["transacao"]["dadosUsuarioTransacao"]["numeroEnderecoEntrega"] = $billing->getStreet(2);
		$request["transacao"]["dadosUsuarioTransacao"]["bairroEnderecoEntrega"] = $this->cleanString($billing->getStreet(4));
		$request["transacao"]["dadosUsuarioTransacao"]["complementoEnderecoEntrega"] = $this->cleanString($billing->getStreet(3));
		$request["transacao"]["dadosUsuarioTransacao"]["cidadeEnderecoEntrega"] = $this->cleanString($billing->getCity());
		$request["transacao"]["dadosUsuarioTransacao"]["cepEnderecoEntrega"] = $billing->getPostcode();
		$request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoEntrega"] = $this->cleanString($billing->getRegion());		
		
		
		//URLs de redirecionamento
		if ($this->getConfigData('urlRedirecionamentoPago') != ''){
			$request["transacao"]["urlRedirecionamentoPago"] = $this->cleanString($this->getConfigData('urlRedirecionamentoPago'));
		}
		
		if ($this->getConfigData('urlRedirecionamentoNaoPago') != ''){
			$request["transacao"]["urlRedirecionamentoNaoPago"] = $this->cleanString($this->getConfigData('urlRedirecionamentoNaoPago'));
		}
		
		$estados = array(
			"Acre" => "AC",
			"Alagoas" => "AL",
			"Amazonas" => "AM",
			"Amapá" => "AP",
			"Amapa" => "AP",
			"Bahia" => "BA",
			"Ceará" => "CE",
			"Ceara" => "CE",
			"Distrito Federal" => "DF",
			"Espírito Santo" => "ES",
			"Espirito Santo" => "ES",
			"Goiás" => "GO",
			"Goias" => "GO",
			"Maranhão" => "MA",
			"Maranhao" => "MA",
			"Mato Grosso" => "MT",
			"Mato Grosso do Sul" => "MS",
			"Minas Gerais" => "MG",
			"Pará" => "PA",
			"Para" => "PA",
			"Paraíba" => "PB",
			"Paraiba" => "PB",
			"Paraná" => "PR",
			"Parana" => "PR",
			"Pernambuco" => "PE",
			"Piauí" => "PI",
			"Piaui" => "PI",
			"Rio de Janeiro" => "RJ",
			"Rio Grande do Norte" => "RN",
			"Rondônia" => "RO",
			"Rondonia" => "RO",
			"Rio Grande do Sul" => "RS",
			"Roraima" => "RR",
			"Santa Catarina" => "SC",
			"Sergipe" => "SE",
			"São Paulo" => "SP",
			"Sao Paulo" => "SP",
			"Tocantins" => "TO"
		);
		
		if (strlen($request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoComprador"]) > 2){
			foreach ($estados as $estado => $uf) {
				if ($request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoComprador"] == $estado)
					$request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoComprador"] = $uf;				
			}
		}
		
		if (strlen($request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoEntrega"]) > 2){
			foreach ($estados as $estado => $uf) {
				if ($request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoEntrega"] == $estado)
					$request["transacao"]["dadosUsuarioTransacao"]["estadoEnderecoEntrega"] = $uf;				
			}
		}			
					
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneAdicionalComprador"] = "1";
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneAdicionalEntrega"] = "1";
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneComprador"] = "1";
        $request["transacao"]["dadosUsuarioTransacao"]["codigoTipoTelefoneEntrega"] = "1";

		$ipn = Mage::getUrl('superpay/superpay/ipn');
        $request["transacao"]["urlCampainha"] = $ipn;
		Mage::log('### [SuperPay Banco] IPN = ' . $ipn, null, 'ernet.log');
		
        Mage::log('### [SuperPay Banco] Order Clazz instance of ' . get_class($order), null, 'ernet.log');
        Mage::log('### [SuperPay Banco] Payment Clazz instance of ' . get_class($payment), null, 'ernet.log');

        $items = $order->getAllItems();

        Mage::log('### [SuperPay Banco] Adding items inside superpay request....', null, 'ernet.log');

        $i = 0;
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            Mage::log('### [SuperPay Banco] Adding item: #' . $item->getSku(), null, 'ernet.log');

			Mage::log('### [SuperPay Banco] Adding SKU -> '.$item->getSku(), null, 'ernet.log');
			$product_code = $item->getSku();
            $request["transacao"]["itensDoPedido"][$i]["codigoProduto"] = $product_code;
			Mage::log('## [SuperPay Banco] Product Code: '.$product_code, null, 'ernet.log');
			
            $request["transacao"]["itensDoPedido"][$i]["codigoCategoria"] = '0';
			
			$product_name = $this->cleanString($item->getName());
            $request["transacao"]["itensDoPedido"][$i]["nomeProduto"] = $this->cleanString($product_name);
			Mage::log('## [SuperPay Banco] Product Name: '.$product_name, null, 'ernet.log');
			
			$product_qty = $item->getQtyToInvoice();
            $request["transacao"]["itensDoPedido"][$i]["quantidadeProduto"] = $product_qty;
			Mage::log('## [SuperPay Banco] Product Qty: '.$product_qty, null, 'ernet.log');
			
			$product_unit_price = round($item->getPrice() * 100);
            $request["transacao"]["itensDoPedido"][$i]["valorUnitarioProduto"] = $product_unit_price;
			Mage::log('## [SuperPay Banco] Product Unit Price: '.$product_unit_price, null, 'ernet.log');
			
            $request["transacao"]["itensDoPedido"][$i]["nomeCategoria"] = "Loja";
            $i++;
        }

        $this->_forceSuccess = false;

        $wsdl_url = $this->_getGatewayUrl();

        if (!$wsdl_url) {
            Mage::throwException(
                    Mage::helper('superpay')->__('Nao existem meios de pagamento disponiveis no momento.', "[SPERR:012]")
            );
        }

        $client = new SoapClient($wsdl_url, array('soap_version' => SOAP_1_1, 'trace' => 1));

        try {
            Mage::log('## Sending transaction to SuperPay', null, 'ernet.log');
            $tran = $client->pagamentoTransacaoCompleta($request);
			
			Mage::log('## Request XML: ' . $client->__getLastRequest() , null, 'ernet.log');
			
            Mage::log('## Transaction returned.', null, 'ernet.log');
        } catch (SoapFault $fault) {
            Mage::log('## Exception: ' . $fault, null, 'ernet.log');
			// #################### DEBUG #####################################
			$fh = fopen("errors/superpay_debug_".$order_id.".txt","a");
			fwrite($fh,print_r($_POST,true));
			fwrite($fh,print_r($request,true));		
			fwrite($fh,$client->__getLastRequest());
			fclose($fh);		
			// #################### DEBUG #####################################				
            Mage::throwException(
                    Mage::helper('superpay')->__('Nao foi possivel realizar o pagamento. Por favor, tente mais tarde. [ %s ]', "[" . $fault->faultcode . "] " . $fault->faultstring)
            );
        }
	
        $status = (int) $tran->return->statusTransacao;
        $this->_transactionId = $tran->return->numeroTransacao;
        $_REQUEST["urlRedirect"] = $this->urlPagamento = $tran->return->urlPagamento;
        $payment->setTransactionId($this->_transactionId);

        Mage::log('### [SuperPay Banco] URL Redirect = ' . $this->urlPagamento, null, 'ernet.log');
        Mage::log('### [SuperPay Banco] Payment Status = ' . $status, null, 'ernet.log');
        Mage::log('### [SuperPay Banco] Return: ' . print_r($tran->return, true), null, 'ernet.log');

        if (($status != 1 && $status != 5 && $status != 6 && $status != 8 && $status != 31 && $status != 30) || empty($this->urlPagamento)) {
            Mage::log('### [SuperPay Banco] Validation failed! ', null, 'ernet.log');
            Mage::throwException(
                    Mage::helper('superpay')->__('Erro no processamento da sua compra: %s', $tran->return->mensagemVenda)
            );
            return false;
        }



        Mage::log('### [SuperPay Banco] Returning authorize method.', null, 'ernet.log');

        return $this;
    }

    /**
     * Send capture request to gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @param decimal $amount
     * @return Mage_Paygate_Model_Authorizenet
     */
    public function capture(Varien_Object $payment, $amount) {
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for capture.'));
        }

        //$this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_CAPTURE);

        Mage::log('### [SuperPay Banco] CAPTURANDO PAGAMENTO......', null, 'ernet.log');

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Void the payment through gateway
     *
     * @param  Mage_Payment_Model_Info $payment
     * @return Mage_Paygate_Model_Authorizenet
     */
    public function void(Varien_Object $payment) {

        Mage::log('### [SuperPay Banco] VOID.', null, 'ernet.log');
        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    protected function _postRequest(Varien_Object $request, $tran) {

        Mage::log('### [SuperPay Banco] _postRequest called.', null, 'ernet.log');
        if ($tran->Process()) {
            return true;
        } else {
            Mage::throwException(
                    Mage::helper('superpay')->__('Error processing credit card: %s', $tran->error)
            );
            return false;
        }
    }

    private function _getAmount() {
        $info = $this->getInfoInstance();
        if ($this->_isPlaceOrder()) {
            return (double) $info->getOrder()->getQuoteBaseGrandTotal();
        } else {
            return (double) $info->getQuote()->getBaseGrandTotal();
        }
    }

    public function getConfigDataSuperpay($field) {
        $path = 'payment/superpay_banco/' . $field;
        $config = Mage::getStoreConfig($path);
        return $config;
    }

    private function cleanString($str) {
			
		$str = $this->checkNullString($str);		
		$str = strtr(utf8_decode($str), "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC");	
				
		return $str;		
    }

    private function checkNullString($str) {
        //($str=='') ? return '-' : return $str;

        if (trim($str) == '' || is_null($str)) {
            return 'X';
        } else {
            return $str;
        }
    }

    public function getBanks($id = NULL) {
        if ($this->_banks == NULL) {
            $this->_banks = array(                
                16 => "Itau Shopline Transferencia      (Itau)",
                17 => "Itau Shopline Boleto (online)",
                18 => "Bradesco Shopfacil Transferencia (Bradesco)",        
                19 => "Bradesco Shopfacil Boleto (online)",
                20 => "RealPague Transferencia          (Real)",            
                21 => "BBOnline Transferencia	    (Banco do Brasil)", 
                22 => "HSBC Transferencia		    (HSBC)",	          
                100 => "HSBC Boleto (online)",
                23 => "Banricompras.com Tranferencia    (Banrisul)",        
                24 => "Banricompras.com Parcelamento    (Banrisul)",        
                25 => "Banricompras.com PrDatado        (Banrisul)",        
                26 => "Banricompras.com Boleto          (Banrisul)",        
                27 => "OiPaggo			        (OiPaggo)",		  
                28 => "BB Boleto (offline)",
                29 => "Itau Boleto (offline)",
                30 => "Bradesco Boleto (offline)",
                31 => "Unibanco Boleto (offline)",
                32 => "HSBC Boleto (offline)",
                33 => "Real Boleto (offline)",
                34 => "CEF Boleto (offline)",
                35 => "MOIP(MOIP)",            
                36 => "Mercado Pago(CEF)",             
                37 => "Pagamento Digital(PD)",              
                38 => "DinheiroMail(DinheiroMail)",    
                39 => "PagSeguro(PagSeguro)",
				110 => "PayPal WS(PayPal WS)",
				111 => "PayPal POST(PayPal)",
				150 => "Boleto Parcelado iVarejo(iVarejo)",
				80 => "Redecard - Komerci Integrado(Visa)",
				81 => "Redecard - Komerci Integrado(MasterCard)",
				82 => "Redecard - Komerci Integrado(Diners)",
				130 => "Visa(Cielo)",
				131 => "MasterCard(Cielo)",
				132 => "American Express(Cielo)",
				133 => "ELO(Cielo)",
				134 => "Diners(Cielo)",
				135 => "Discover(Cielo)",
				136 => "Aura(Cielo)",
				137 => "JCB(Cielo)",				
				139 => "Visa Electron(Cielo)"				
            );
        }
        if (!empty($id))
            return $this->_banks[$id];
        else
            return $this->_banks;
    }

    public function getOrderPlaceRedirectUrl() {
        Mage::log('### [SuperPay Banco] getOrderPlaceRedirectUrl() called.', null, 'ernet.log');
        $_SESSION['pay_url'] = $_REQUEST["urlRedirect"];
        Mage::log($this->getConfigPaymentAction(), null, 'ernet.log');

        // #################### DEBUG #####################################
        //Mage::log('@@@@'.$this,null,'ernet.log');
        //$fh = fopen("superpay_this_obj.txt","a");
        //fwrite($fh,print_r($this,true));
        //fclose($fh);		
        //exit();
        // #################### DEBUG #####################################	

        return Mage::getUrl('superpay/superpay/redirect', array('_secure' => true));
    }

    public function validate() {

        Mage::log($this->getConfigPaymentAction(), null, 'ernet.log');
        parent::validate();
//        Mage::throwException(Mage::helper('superpay')->__('error occuried.'));
        return $this;
    }

    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Retrieve session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession() {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote');
        } else {
            return Mage::getSingleton('checkout/session');
        }
    }

    private function _getGatewayUrl() {

        $mode = $this->getConfigData('mode');

        Mage::log('### [SuperPay Banco] Gateway mode: ' . $mode, null, 'ernet.log');

        if ($mode == 'live') {
            return self::LIVE_WSI;
        } else if ($mode == 'test') {
            return self::TEST_WSI;
        } else {
            Mage::log('### [SuperPay Banco] Invalid mode selected. Aborting...', null, 'ernet.log');
            return false;
        }
    }

}
?>