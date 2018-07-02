<?php  
    class Oitoo_Regrasdefrete_Model_Carrier_Regrasdefrete     
		extends Mage_Shipping_Model_Carrier_Abstract
		implements Mage_Shipping_Model_Carrier_Interface
	{  
        protected $_code = 'regrasdefrete';
        protected $_condicoes = array();
        //$condicoes[] = array('cep','<','37470000');


        public function getRegras(){

            $collection = mage::getModel('regrasdefrete/regras')->getResourceCollection();
            foreach($this->_condicoes as $condicao){
               // mage::log($condicao[0] . ' - ' . $condicao[1] . ' - ' .$condicao[2]);
                $fields = $condicao[0];

                if($condicao[0] == 'pais' || $condicao[0] == 'estado' || $condicao[0] == 'cidade' || $condicao[0] == 'website'){
                    $condicoes = array(
                                        array($condicao[1]  => array($condicao[2])),
                                        array('eq'          => '*')
                                      );
                } else {
                    $condicoes = array($condicao[1] => array($condicao[2]));
                }
                $collection->addFieldToFilter($fields,$condicoes);
            }




            return $collection;


        }
      

        public function collectRates(Mage_Shipping_Model_Rate_Request $request){

            //pega os dados de entrega
            /*
                [dest_country_id] => US
                [dest_region_id] => 3
                [dest_region_code] => AS
                [dest_street] => teste
                [dest_city] => São lourenço
                [dest_postcode] => 43243
                [package_value] => 2
                [package_value_with_discount] => 2
                [package_weight] => 180
                [package_qty] => 2
                [package_physical_value] => 2
                [free_method_weight] => 180
                [store_id] => 1
                [website_id] => 1
                [free_shipping] => 0
                [base_subtotal_incl_tax] => 2
                [country_id] => US
                [region_id] => 12
                [postcode] => 90034

            */
            $cep = str_replace('-','',$request->getDestPostcode());
            $this->_condicoes[] = array('pais', 'finset', $request->getDestCountryId());
            $this->_condicoes[] = array('estado', 'finset', $request->getDestRegionCode());
            $this->_condicoes[] = array('cep_de', 'lteq', $cep);
            $this->_condicoes[] = array('cep_ate', 'gteq', $cep);
            $this->_condicoes[] = array('website', 'finset', $request->getWebsiteId());
            $this->_condicoes[] = array('cidade', 'like', $request->getDestCity());

            $produtoEnviavel = false;
            foreach ($request->getAllItems() as $item) {

                /*
                    [item_id] => 10
                    [quote_id] => 9
                    [product_id] => 2
                    [store_id] => 1
                    [is_virtual] => 0
                    [sku] => 324
                    [name] => Teste 2
                    [free_shipping] =>
                    [weight] => 90.0000
                    [qty] => 1
                    [price] => 1
                    [base_price] => 1
                    [base_row_total] => 1
                    [row_weight] => 90
                    [product_type] => simple
                */
                $pesoTotal = 0;
                //Verifica o tipo de produto
                if ($item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL &&
                    $item->getProductType() != 'downloadable') {
                        $produtoEnviavel = true;

                        //Se calculo for por produto, adiciona o peso de cada produto a condição
                        if($this->getConfigData('fretePorProduto')):
                            $this->_condicoes[] = array('peso_de', 'lteq', $item->getWeight());
                            $this->_condicoes[] = array('peso_ate', 'gteq', $item->getWeight());
                        endif;

                        //pega o peso total dos produtos
                        $pesoTotal += ($item->getWeight() * $item->getQty());
                        $quantidadeTotal +=  $item->getQty();

                }


            }




            //caso o frete não seja por produto, adiciona a condição do peso total dos produtos
            if(!$this->getConfigData('fretePorProduto')):
                $this->_condicoes[] = array('peso_de', 'lteq', $pesoTotal);
                $this->_condicoes[] = array('peso_ate', 'gteq', $pesoTotal);
            endif;


             if($produtoEnviavel) {
                 $result = Mage::getModel('shipping/rate_result');



                //verifica quais regras se encaixam nas condições adicionadas acima
                $regras = $this->getRegras();
                foreach($regras as $regra):

                    $method = Mage::getModel('shipping/rate_result_method');
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));
                    $method->setMethod($this->_code);
                    $method->setMethodTitle($regra->getTitulo());
                    if($this->getConfigData('fretePorProduto')){
                        $method->setPrice($regra->getValor() * $quantidadeTotal);
                        $method->setCost($regra->getCusto() * $quantidadeTotal);
                    } else {
                        $method->setPrice($regra->getValor());
                        $method->setCost($regra->getCusto());
                    }

                    $result->append($method);


                endforeach;
                return $result;

             } else {
                //se não houver nenhum produto que possa ser enviado, finaliza o modelo
                return true;
             }
        }

		/**
		 * Get allowed shipping methods
		 *
		 * @return array
		 */
		public function getAllowedMethods()
		{
			return array($this->_code=>$this->getConfigData('name'));
		}
    }  
