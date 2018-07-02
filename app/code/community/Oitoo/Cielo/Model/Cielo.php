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
class Oitoo_Cielo_Model_Cielo extends Mage_Core_Model_Abstract {

    const  URL_PRODUCAO    = 'https://ecommerce.cielo.com.br/servicos/ecommwsec.do';
    const  URL_HOMOLOGACAO = 'https://qasecommerce.cielo.com.br/servicos/ecommwsec.do';


    public $ambiente = 'homologacao';
    public $filiacao = '1006993069';
    public $chave    = '25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3';

    public function __construct(){
        //seta o ambiente e os dados do cliente cielo
        $filiacao = Mage::getStoreConfig('payment/apelidocielo/filiacao');
        if($filiacao != ''){
            $this->filiacao = $filiacao;

        }

        $chave = Mage::getStoreConfig('payment/apelidocielo/chave');
        if($chave != ''){
            $this->chave = $chave;
        }

        $this->ambiente = Mage::getStoreConfig('payment/apelidocielo/ambiente');
    }

    public function getTransacao($tid){

        if($this->ambiente == 'homologacao'){
            $ambiente = self::URL_HOMOLOGACAO;
        } else if ($this->ambiente == 'producao') {
            $ambiente = self::URL_PRODUCAO;
        }



        $autenticacao ="<?xml version='1.0' encoding='ISO-8859-1'?><requisicao-consulta id='" . md5(date("YmdHisu")) . "' versao='1.2.0'>";

        //dados do estabelecimento
        $autenticacao .='
		<tid>'.$tid.'</tid>
		<dados-ec>
		  <numero>'.$this->filiacao.'</numero>
		  <chave>'.$this->chave.'</chave>
		</dados-ec>';
        $autenticacao .='</requisicao-consulta>';

        return $this->enviarParaCielo($ambiente, $autenticacao);

    }

    public function setCancelamento($tid, $valor){

        if($this->ambiente == 'homologacao'){
            $ambiente = self::URL_HOMOLOGACAO;
        } else {
            $ambiente = self::URL_PRODUCAO;
        }

        $autenticacao ="<?xml version='1.0' encoding='ISO-8859-1'?><requisicao-cancelamento id='" . md5(date("YmdHisu")) . "' versao='1.2.0'>";

        //dados do estabelecimento
        $autenticacao .='
		<tid>'.$tid.'</tid>
		<dados-ec>
		  <numero>'.$this->filiacao.'</numero>
		  <chave>'.$this->chave.'</chave>
		</dados-ec>
		<valor>'.$valor.'</valor>';
        $autenticacao .='</requisicao-cancelamento>';

        return $this->enviarParaCielo($ambiente, $autenticacao);

    }


    public function setCaptura($tid, $valor){

        if($this->ambiente == 'homologacao'){
            $ambiente = self::URL_HOMOLOGACAO;
        } else {
            $ambiente = self::URL_PRODUCAO;
        }

        $autenticacao ="<?xml version='1.0' encoding='ISO-8859-1'?><requisicao-captura id='" . md5(date("YmdHisu")) . "' versao='1.2.0'>";

        //dados do estabelecimento
        $autenticacao .='
		<tid>'.$tid.'</tid>
		<dados-ec>
		  <numero>'.$this->filiacao.'</numero>
		  <chave>'.$this->chave.'</chave>
		</dados-ec>
		<valor>'.$valor.'</valor>';
        $autenticacao .='</requisicao-captura>';

        return $this->enviarParaCielo($ambiente, $autenticacao);

    }


    /*============================================================================
      *
      * Solicita autorização do pagamento no webservice da redecard
      *
      * @orderId - deve ser enviado no pedido de autorização
      * @arquivoPayment - Model principal do módulo

      *
      ===========================================================================*/
    public function setAutorizacao( $idpedido, $bandeira_cartao, $portador, $numcartao, $codseguranca, $validade, $valor, $parcelas, $parcelamento, $softdescriptor, $campollivre, $capturaautomatica = 'false')
    {
        $parameters = new stdClass();

        if($this->ambiente == 'homologacao'){
            $ambiente = self::URL_HOMOLOGACAO;
        } else {
            $ambiente = self::URL_PRODUCAO;
        }



        $parameters->Valor_total  = $valor; //pega valor total do pedido
        $parameters->Filiacao = $this->filiacao;
        $parameters->Chave = $this->chave;


        $parameters->OrderId = $idpedido;
        $parameters->Numero_cartao = $numcartao;
        $parameters->Codigo_seguranca = $codseguranca;
        $parameters->Validade = $validade; //yyyymm
        $parameters->Portador = $portador; //nome no cartão
        $parameters->SoftDescriptor = $softdescriptor; //Texto que vai acompanhar a fatura do pedido
        $parameters->Campo_livre = $campollivre;
        $parameters->Descricao = '';
        $parameters->CapturaAutomatica = $capturaautomatica;

        /*=========================================================
         * O INDICADOR PODE SER
         * 0 - Não informado
         * 1 - Informado
         * 2 - Ilegível
         * 3 - Inexistente
         * obs: Somente o código 1 é validado
         */
        $parameters->Indicador = '1';


        /*=========================================================
         * 986 é o código do real
         */
        $parameters->Moeda = "986";

        $parameters->Idioma = 'PT';
        $parameters->Data = strftime( '%Y-%m-%dT%H:%M:%S' );

        /*=========================================================
         * AS BANDEIRAS SÃO EM MINÚSCULO
         *  visa, mastercard, diners, discover, elo, amex, jcb, aura
        */
        $parameters->Bandeira = $bandeira_cartao;



        /*==========================================================
         * A TAG PRODUTO É SE O PAGAMENTO É A VISTA, PARCELADO LOJA, PARCELADO ESTABELECIMENTO OU OUTRO
         * 1 - Crédito à vista
         * A - Débito
         */
        if($parcelas > 1) {
            if($parcelamento == 'loja'){
                $parameters->Produto = '2'; //parcelamento pela loja
            } else if($parcelamento == 'administradora'){
                $parameters->Produto = '3'; //parcelamento pela administradora
            }
        } else {
            $parameters->Produto = '1'; //caso contrário é crédito à vista
        }


        $parameters->Url_retorno = ''; //coloca url de retorno no caso de autenticação


        /*==========================================================
         * INDICA O NIVEL DE AUTORIZAÇÃO DO PEDIDO
         * 0 - NÃO AUTORIZAR
         * 1 - AUTORIZAR SOMENTE SE AUTENTICADA //PADRAO PARA DÉBITO VISA E MASTERCARD DO BRADESCO
         * 2 - AUTORIZAR AUTENTICADA E NAO AUTENTICADA
         * 3 - AUTORIZAR SEM PASSA POR AUTENTICAÇÃO (SOMENTE CRÉDITO - AUTORIZAÇÃO DIRETA) PADRÃO PARA DINERS, DISCOVER, ELO E AMEX
         * 4 - TRANSAÇÃO RECORRENTE
         * O código abaixo verifica se a opção é débito ou crédito
         */
        $parameters->Autorizar = '3'; //para as demais bandeiras passa sem autenticar


        $parameters->Bin = substr($numcartao, 0, 6); //Seis primeiros números do cartão
        $parameters->Parcelas = $parcelas;



        $autenticacao ="<?xml version='1.0' encoding='ISO-8859-1'?><requisicao-transacao id='" . md5(date("YmdHisu")) . "' versao='1.2.0'>";

        //dados do estabelecimento
        $autenticacao .='
		<dados-ec>
		  <numero>'.$parameters->Filiacao.'</numero>
		  <chave>'.$parameters->Chave.'</chave>
		</dados-ec>';

        //dados do cliente
        $autenticacao .='
		<dados-portador>
		    <numero>'.$parameters->Numero_cartao.'</numero>
		    <validade>'.$parameters->Validade.'</validade>
		    <indicador>'.$parameters->Indicador.'</indicador>
		    <codigo-seguranca>'.$parameters->Codigo_seguranca.'</codigo-seguranca>
		</dados-portador>';

        //dados do pedido
        $autenticacao .='
		<dados-pedido><numero>'.$parameters->OrderId.'</numero>
		    <valor>'.$parameters->Valor_total.'</valor>
		    <moeda>'.$parameters->Moeda.'</moeda>
		    <data-hora>'.$parameters->Data.'</data-hora>
		    <descricao>'.$parameters->Descricao.'</descricao>
		    <idioma>'.$parameters->Idioma.'</idioma>
		    <soft-descriptor>'.$parameters->SoftDescriptor.'</soft-descriptor>
		</dados-pedido>';

        //dados do cartão
        $autenticacao .='
		<forma-pagamento>
		    <bandeira>'.$parameters->Bandeira.'</bandeira>
		    <produto>'.$parameters->Produto.'</produto>
		    <parcelas>'.$parameters->Parcelas.'</parcelas>
		</forma-pagamento>';

        //Outros dados
        $autenticacao .='
		<url-retorno>'.$parameters->Url_retorno.'</url-retorno>
		<autorizar>'.$parameters->Autorizar.'</autorizar>
		<capturar>'.$parameters->CapturaAutomatica.'</capturar>
		<campo-livre>'.$parameters->Campo_livre.'</campo-livre>
		<bin>'.$parameters->Bin.'</bin>
		</requisicao-transacao>';

        $mensagem = $autenticacao;




        return $this->enviarParaCielo($ambiente, $mensagem);


    }

    public function enviarParaCielo($ambiente, $corpo){

        $debug = Mage::getStoreConfig('payment/apelidocielo/debug');
        if($debug):
            $this->setLog("==================== ENVIANDO XML PARA CIELO ====================\n$corpo");
        endif;

        $curl = curl_init();
        if ( is_resource( $curl ) ){

            curl_setopt( $curl , CURLOPT_HEADER , 0 );
            curl_setopt( $curl , CURLOPT_RETURNTRANSFER , 1 );
            curl_setopt( $curl , CURLOPT_FOLLOWLOCATION , 1 );
            curl_setopt( $curl , CURLOPT_URL , $ambiente );
            curl_setopt( $curl , CURLOPT_POST , 1 );
            curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false );
            curl_setopt( $curl , CURLOPT_SSLVERSION, 4); //O ambiente de teste estava apresentando erro quando usado o valor 3. O problema está relacionado a um bug do curl
            curl_setopt( $curl , CURLOPT_POSTFIELDS , http_build_query( array( 'mensagem' => $corpo ) ) );

            $xml = curl_exec($curl);
            $ern = curl_errno( $curl ); //numero do erro
            $err = curl_error( $curl ); //mensagem do erro
            curl_close( $curl );

            if ( (bool) $ern ){

                if($debug):
                    $this->setLog("Ocorreu um erro de comunicação com os servidores da cielo - $err");
                endif;


                $resposta = new stdClass();
                $resposta->codigo = '999'; //CODIGO 999 SIGNIFICA ERRO DE COMUNICAÇÃO
                $resposta->mensagem = 'Ocorreu um erro de comunicação entre nossa loja e a operadora do cartão. Por favor entre em contato. ';
                return $resposta;

            } else {

                if($debug):
                    $this->setLog("==================== XML RETORNADO PELA CIELO  ====================\n$xml");
                endif;

                return simplexml_load_string($xml);

            }

        } else {

            if($debug):
                $this->setLog("Ocorreu um erro ao tentar instanciar o módulo curl do php");
            endif;

            $resposta = new stdClass();
            $resposta->codigo = '0000'; //CODIGO 0000 SIGNIFICA ERRO DE CURL
            $resposta->mensagem = 'Ocorreu um erro inesperado no servidor. Por favor tente novamente.';
            return $resposta;

        }
    }

    public function setLog($msg){
        Mage::log($msg,null, 'oitoo_cielo.log');
    }
}