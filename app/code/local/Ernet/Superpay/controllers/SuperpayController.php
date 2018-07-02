<?php

class Ernet_Superpay_SuperpayController extends Mage_Core_Controller_Front_Action {
    const LIVE_BASE_URL = 'https://superpay2.superpay.com.br/';
    const TEST_BASE_URL = 'https://homologacao.superpay.com.br/';
    const LIVE_WSI = 'checkout/servicosPagamentoCompletoWS.Services?wsdl';
    const TEST_WSI = 'superpay/servicosPagamentoCompletoWS.Services?wsdl';


    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder() {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order')->loadByIncrementID($session->getLastRealOrderId());
        }

        return $this->_order;
    }

    protected function _expireAjax() {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }

    public function boletoAction() {

        try {
		
			Mage::log('# pay_url = '.$_SESSION['pay_url'], null, 'ernet.log');
			$service_code = $_SESSION['service_code'];
			
			if ( $service_code == 17 ) { //ITAU Shopline
				
				$content= "<iframe id='boleto_in' name='boleto_in' FrameBorder='0' src=".$_SESSION['pay_url']." width='100%' height='1000'></iframe>";			
				//$content = file_get_contents($_SESSION['pay_url']);
				$base_url = $this->_getGatewayBaseUrl();
				$url = $base_url . 'checkout/Boleto/';
				//Mage::log('## CONTENT: '.$content,null,'ernet.log');

				if (trim($content) == '' || is_null($content)) {
					Mage::log('## Superpay enviou conteudo invalido!', null, 'ernet.log');
					$this->getResponse()->setBody('Nao foi possivel gerar o Boleto para pagamento. Por favor, utilize o link disponivel no pedido em seu perfil.<br>');
				} else {
					$content = str_replace('<img src="', '<img src="' . $url, $content);
					$content = str_replace($url . $url, $url, $content);
					$content = str_replace('/checkout/img/load1.gif', $base_url . 'checkout/img/load1.gif', $content);
				}
			
			} else if (  $service_code != 100 && $service_code != 19 && $service_code != 17 ) {
			
				$content= "<iframe id='boleto_in' name='boleto_in' FrameBorder='0' src=".$_SESSION['pay_url']." width='100%' height='1000'></iframe>";
				//$content = file_get_contents($_SESSION['pay_url']);
				$base_url = $this->_getGatewayBaseUrl();
				$url = $base_url . 'checkout/Boleto/';
				//Mage::log('## CONTENT: '.$content,null,'ernet.log');

				if (trim($content) == '' || is_null($content)) {
					Mage::log('## Superpay enviou conteudo invalido!', null, 'ernet.log');
					$this->getResponse()->setBody('Nao foi possivel gerar o Boleto para pagamento. Por favor, utilize o link disponivel no pedido em seu perfil.<br>');
				} else {
					$content = str_replace('<img src="', '<img src="' . $url, $content);
					$content = str_replace($url . $url, $url, $content);
					$content = str_replace('/checkout/img/load1.gif', $base_url . 'checkout/img/load1.gif', $content);
				}

			} else {
			
				$content= "<iframe id='boleto_in' name='boleto_in' FrameBorder='0' src=".$_SESSION['pay_url']." width='100%' height='1000'></iframe>";		
				//$content = file_get_contents($_SESSION['pay_url']);
				$error = "entrar em contato com a empresa";
				
				$pos = stripos($content, $error);
				
				$try = 0;
				while ($pos !== false && $try < 5) { 
					$content= "<iframe id='boleto_in' name='boleto_in' FrameBorder='0' src=".$_SESSION['pay_url']." width='100%' height='1000'></iframe>";
					//$content = file_get_contents($_SESSION['pay_url']);
					$pos = stripos($error, $content);
					$try++;
					Mage::log ('## Try: '.$try, null, 'ernet.log');
				}
				
			}
			
            $newOrderStatus = 'pending_payment';

            $order = $this->getOrder();

			$comment = 'Seu pedido foi recebido com sucesso!';
            $order->addStatusHistoryComment($comment, $newOrderStatus)
                    ->setIsVisibleOnFront(false)
                    ->setIsCustomerNotified(true);

            $this->getResponse()->setBody($content);
            
        } catch (Exception $fault) {
            $this->getResponse()->setBody('Nao foi possivel gerar o Boleto para pagamento. Por favor, utilize o link disponivel no pedido em seu perfil.<br>' . $fault);
        }
    }

    public function inlineAction() {

        try {

            require_once(Mage::getBaseDir('lib') . '/superpay_superpay/simple_html_dom.php');

            $service_code = $_SESSION['service_code'];

            $superpay_url = $this->_getGatewayBaseUrl();

            Mage::log('## Boleto called.', null, 'ernet.log');
            Mage::log('## Superpay URL:' . $_SESSION['pay_url'], null, 'ernet.log');
            
			$content= "<iframe id='boleto_in' name='boleto_in' FrameBorder='0' src=".$_SESSION['pay_url']." width='100%' height='1000'></iframe>";
			
			//$content = file_get_contents($_SESSION['pay_url']);
            $url = $superpay_url . 'checkout/Boleto/';
            //Mage::log('## CONTENT: '.$content,null,'ernet.log');

            if (trim($content) == '' || is_null($content)) {

                Mage::log('## Superpay enviou conteudo invalido!', null, 'ernet.log');
                $this->getResponse()->setBody('Nao foi possivel gerar o Boleto para pagamento. Por favor, utilize o link disponivel no pedido em seu perfil.<br>');
            } else {

                $content = str_replace('<img src="', '<img src="' . $url, $content);
                $content = str_replace($url . $url, $url, $content);

                //Mage::log($content, null, 'ernet.log');

                $boleto_id = $this->_saveBoletoImage($_SESSION['pay_url'], $service_code, $content);

                //$comment = 'Seu boleto pode ser impresso aqui: <a href="' . Mage::getUrl("boletos", array('_secure' => 1)) . $boleto_id . '.jpg" target="_blank"><img border="0" src="' . Mage::getUrl("media", array('_secure' => 1)) . 'superpay_superpay/methods-boleto.gif"></a>';

                $comment_email = '<font size="3" color="red">Caso não tenha conseguido imprimir seu boleto, <a href="' . Mage::getUrl("boletos", array('_secure' => 1)) . $boleto_id . '.jpg" target="_blank"><b>CLIQUE AQUI</b></a> para gerar a segunda via.</font>';

                $newOrderStatus = 'pending_payment';

                $order = $this->getOrder(); //Mage::getModel('sales/order')->loadByIncrementID($orderId);

                $order->addStatusHistoryComment($comment, $newOrderStatus)
                        ->setIsVisibleOnFront(true)
                        ->setIsCustomerNotified(true);

                $order->sendOrderUpdateEmail(true, $comment_email);

                //$order->setState($order_status, true, $comment_status, true);
                $order->save();
                $this->getResponse()->setBody("");
            }
        } catch (Exception $fault) {
            $this->getResponse()->setBody('Nao foi possivel gerar o Boleto para pagamento. Por favor, utilize o link disponivel no pedido em seu perfil.<br>' . $fault);
        }
    }

    private function _saveBoletoImage($url, $service_code, $content=null) {


        $boleto_id = uniqid();

        if ($service_code == 17) { //ITAU ShopLine

            $html = file_get_html($url);

            foreach ($html->find('form') as $e)
                $itau_url = $e->action;


            foreach ($html->find('input') as $e) {
                if ($e->name == 'DC')
                    $post_data['DC'] = $e->value;
            }

            $html->clear();
            unset($html);

            $html = str_get_html($this->_getBoletoOnlineHtml($itau_url, $post_data));

            foreach ($html->find('frame') as $e)
                $boleto_url = $e->src;

            $boleto_screen_url = str_replace('Itaubloqueto.asp', '', $itau_url) . 'bloqueto/' . $boleto_url;

            $html->clear();
            unset($html);
			
        } else {

            $output_html = Mage::getBaseDir() . '/boletos/' . $boleto_id . '.html';

            $fh = fopen($output_html, "a");
            fwrite($fh, print_r($content, true));
            fclose($fh);

            $boleto_screen_url = $output_html;
        }


        shell_exec('/usr/bin/wkhtmltoimage \'' . $boleto_screen_url . '\' ' . Mage::getBaseDir() . '/boletos/' . $boleto_id . '.jpg');

        Mage::log('### URL BOLETO = ' . $boleto_screen_url, null, 'ernet.log');

        $_SESSION['boleto_id'] = $boleto_id;
        Mage::log('### BOLETO ID = ' . $boleto_id, null, 'ernet.log');

        return $boleto_id;
    }

    private function _getBoletoOnlineHtml($url, $post_data) {

        foreach ($post_data as $key => $value) {
            $post_items[] = $key . '=' . $value;
        }
        $post_string = implode('&', $post_items);

        $curl_connection = curl_init($url);
        curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

        $html = curl_exec($curl_connection);

        curl_close($curl_connection);

        return $html;
    }


    public function redirectAction() {
        
		Mage::log('### Redirect called!', null, 'ernet.log');

		$pay_url = $_SESSION['pay_url'];
		$service_code = $_SESSION['service_code'];
		
		if ($service_code == 19 || $service_code == 17) {
			$popup_url = $pay_url;
		} else {
			$popup_url = Mage::getUrl("superpay/superpay/boleto");
		}
		
        $target = $this->getConfigDataSuperpay('target');
        $_SESSION['target'] = $target;
       
		$this->boletoAction();
		$this->_redirect('superpay/superpay/success');                    
    }

    public function cancelAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getAlbpayStandardQuoteId(true));

        // cancel order
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
        }

        /* we are calling getPaypalStandardQuoteId with true parameter, the session object will reset the session if parameter is true.
          so we don't need to manually unset the session */
        //$session->unsPaypalStandardQuoteId();
        //need to save quote as active again if the user click on cacanl payment from albpay
        //Mage::getSingleton('checkout/session')->getQuote()->setIsActive(true)->save();
        //and then redirect to checkout one page
        $this->_redirect('checkout/cart');
    }

    /**
     * when albpay returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function successAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getAlbpayStandardQuoteId(true));
        /**
         * set the quote as inactive after back from albpay
         */
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

    public function getConfigDataSuperpay($field) {
        $path = 'payment/superpay_banco/' . $field;
        $config = Mage::getStoreConfig($path);

        return $config;
    }

    /**
     * when albpay returns via ipn
     * cannot have any output here
     * validate IPN data
     * if data is valid need to update the database that the user has
     */
    public function ipnAction() {
		ini_set('soap.wsdl_cache_enabled',0);
		ini_set('soap.wsdl_cache_ttl',0);

        Mage::log('## Notificacao recebida!', null, 'ernet.log');
        Mage::log('## POST data: '.print_r($_POST,true), null, 'ernet.log');
        Mage::log('## Notificacao Pedido # ' . $_POST['numeroTransacao'], null, 'ernet.log');

        $username = $this->getConfigDataSuperpay('username');
        $password = $this->getConfigDataSuperpay('password');       
        $mode = $this->getConfigDataSuperpay('mode');

		Mage::log('## Mode # ' . $mode, null, 'ernet.log');

        $orderId = $_POST['numeroTransacao'];		
		$code = $_POST['codigoEstabelecimento'];
		
        $request = array();
        $request["usuario"] = $username;
        $request["senha"] = $password;
        $request["consultaTransacaoWS"]["codigoEstabelecimento"] = $code;
        $request["consultaTransacaoWS"]["numeroTransacao"] = $orderId;

        $wsdl_url = $this->_getGatewayUrl($mode);
        Mage::log('## Gateway URL: ' . $wsdl_url, null, 'ernet.log');

        $client = new SoapClient($wsdl_url, array('soap_version' => SOAP_1_1, 'trace' => 1));

        try {
            Mage::log('## Sending request to SuperPay', null, 'ernet.log');
            $tran = $client->consultaTransacaoEspecifica($request);
			
			Mage::log('### [SuperPay] Request Build.' . $client->__getLastRequest() , null, 'ernet.log');
            Mage::log('## Response returned.', null, 'ernet.log');
        } catch (SoapFault $fault) {
			Mage::log('### [SuperPay] Request Build.' . $client->__getLastRequest() , null, 'ernet.log');
            Mage::log('## Exception: ' . $fault, null, 'ernet.log');
            Mage::throwException(
                    Mage::helper('superpay')->__('Error processing payment: %s', "[" . $fault->faultcode . "] " . $fault->faultstring)
            );
        }

        $status = (int) $tran->return->statusTransacao;
        Mage::log('## Status Pedido # ' . $status, null, 'ernet.log');

        $comment_status = '## Notificacao automatica SuperPay.';
        $comment_status_customer = 'Seu pedido foi atualizado.';

        $order_status = 'pending_payment';

        if ($status == 1 || $status == 2 || $status == 10 || $status == 31) {
            $order_status = 'processing';
            $comment_status_customer = 'O pagamento do seu pedido foi confirmado.';
        } else if ($status == 3 || $status == 5 || $status == 6 || $status == 8 || $status == 30) {
            $order_status = 'pending_payment';
            $comment_status_customer = 'O pagamento do seu pedido continua em pendente. Estamos aguardando a confirmação para continuar o processo de venda.';
        } else if ($status == 13) {
            $order_status = 'pending_payment';
            $comment_status_customer = 'Pagamento do pedido cancelado.';
		} else if ($status == 9) {
            $order_status = 'payment_review';
            $comment_status_customer = 'Houve uma falha na operadora. Entre em contato conosco.';
        } else if ($status == 21) {
            $order_status = 'payment_review';
            $comment_status_customer = 'Recebemos a confirmação de pagamento do seu boleto. Porém o valor pago é divergente do emitido pelo nosso sistema. Por favor entre em contato conosco.';
        } else if ($status == 22) {
            $order_status = 'payment_review';
            $comment_status_customer = 'Recebemos a confirmação de pagamento do seu boleto. Porém o valor pago é divergente do emitido pelo nosso sistema. Por favor entre em contato conosco.';
        } else {
            Mage::log('## Pedido nao alterado.', null, 'ernet.log');
            exit();
        }

        $order = Mage::getModel('sales/order')->loadByIncrementID($orderId);
        $order->setState($order_status, true, $comment_status_customer, true);
        $order->sendOrderUpdateEmail(true, $comment_status_customer);

        //$order->addStatusHistoryComment($comment_status_customer, $order_status)
        //        ->setIsVisibleOnFront(true)
        //        ->setIsCustomerNotified(true);

        $order->save();

        Mage::log('## Pedido ' . $orderId . ' atualizado para ' . $order_status . '.', null, 'ernet.log');
		
		if ($status == 1 || $status == 2 || $status == 10 || $status == 31) {
			Mage::log('### [SuperPay] Faturar Pedido?' . $this->getConfigDataSuperpay('createOrder'), null, 'ernet.log');
		
			if ($this->getConfigDataSuperpay('createOrder') == true) {
				$this->_createInvoice($order);
			}
		}
			
        exit();

    }

    private function _getGatewayBaseUrl() {

        $mode = $this->getConfigDataSuperpay('mode');

        if ($mode == 'live') {
            return self::LIVE_BASE_URL;
        } else if ($mode == 'test') {
            return self::TEST_BASE_URL;
        } else {
            Mage::log('## Invalid mode selected. Aborting...', null, 'ernet.log');
            return false;
        }
    }

    private function _getGatewayUrl($mode) {

        Mage::log('### Gateway mode: ' . $mode, null, 'ernet.log');

        if ($mode == 'live') {
            return self::LIVE_BASE_URL . self::LIVE_WSI;
        } else if ($mode == 'test') {
            return self::TEST_BASE_URL . self::TEST_WSI;
        } else {
            Mage::log('## Invalid mode selected. Aborting...', null, 'ernet.log');
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
