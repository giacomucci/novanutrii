<?php
class Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("regrasdefrete_form", array("legend"=>Mage::helper("regrasdefrete")->__("Item information")));

				
						$fieldset->addField("titulo", "text", array(
						"label" => Mage::helper("regrasdefrete")->__("Título"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "titulo",
						));


                        $fieldset->addField("valor", "text", array(
                            "label" => Mage::helper("regrasdefrete")->__("Valor"),
                            "class" => "required-entry",
                            "required" => true,
                            "name" => "valor",
                        ));

                        $fieldset->addField("custo", "text", array(
                            "label" => Mage::helper("regrasdefrete")->__("custo"),
                            "name" => "custo",
                        ));
					
						$fieldset->addField("peso_de", "text", array(
						"label" => Mage::helper("regrasdefrete")->__("Peso a partir de"),
						"name" => "peso_de",
						));
					
						$fieldset->addField("peso_ate", "text", array(
						"label" => Mage::helper("regrasdefrete")->__("Peso até"),
						"name" => "peso_ate",
						));


                        $countryList = Mage::getResourceModel('directory/country_collection')->loadByStore()->toOptionArray(false);
                        $options = array('value'=>'*','label'=>'Todas os Paises');
                        array_unshift($countryList,$options);
                        $fieldset->addField("pais", "multiselect", array(
                            "label" => Mage::helper("regrasdefrete")->__("País"),
                            "name" => "pais",
                            "values" => $countryList,
                        ));


                        $options = array();
                        $options[] = array('value'=>'*','label'=>'Todas os Estados');
                        foreach($countryList as $country){
                            if($country['value'] != ""){
                                $code = $country['value'];
                            } else {
                                continue;
                            }
                            $collection = Mage::getModel('directory/region')->getResourceCollection()
                                ->addCountryFilter($code)
                                ->load();



                            foreach($collection as $estado) {
                            $options[] = array('value'=>$estado['code'],'label'=>$estado['default_name']);
                            }

                        }

						 $fieldset->addField('estado', 'multiselect', array(
						'label'     => Mage::helper('regrasdefrete')->__('Estado'),
						'values'   => $options,
						'name' => 'estado',
						));
						$fieldset->addField("cidade", "text", array(
						"label" => Mage::helper("regrasdefrete")->__("Cidade"),
						"name" => "cidade",
						));


					
						$fieldset->addField("cep_de", "text", array(
						"label" => Mage::helper("regrasdefrete")->__("Cep à partir de"),
						"name" => "cep_de",
						));
					
						$fieldset->addField("cep_ate", "text", array(
						"label" => Mage::helper("regrasdefrete")->__("Cep até"),
						"name" => "cep_ate",
						));

                        $options = array();
                        $options[] = array('value'=>'*','label'=>'Todas os WebSites');
                        foreach (Mage::app()->getWebsites() as $website) {

                            $options[] = array('value'=>$website->getId(),'label'=>$website->getName());
                        }
						 $fieldset->addField('website', 'multiselect', array(
						'label'     => Mage::helper('regrasdefrete')->__('Site'),
						'values'   => $options,
						'name' => 'website',
						));				
						/* $fieldset->addField('store', 'multiselect', array(
						'label'     => Mage::helper('regrasdefrete')->__('Loja'),
						'values'   => Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Grid::getValueArray11(),
						'name' => 'store',
						));				
						 $fieldset->addField('view', 'multiselect', array(
						'label'     => Mage::helper('regrasdefrete')->__('Visão'),
						'values'   => Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Grid::getValueArray12(),
						'name' => 'view',
						)); */

				if (Mage::getSingleton("adminhtml/session")->getRegrasData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getRegrasData());
					Mage::getSingleton("adminhtml/session")->setRegrasData(null);
				} 
				elseif(Mage::registry("regras_data")) {
				    $form->setValues(Mage::registry("regras_data")->getData());
				}
				return parent::_prepareForm();
		}
}
