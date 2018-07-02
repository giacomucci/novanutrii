<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('regrasdefrete/regras');
$sql=<<<SQLTEXT
create table $table (id_regra int not null auto_increment, titulo varchar(250),  peso_de int(12), peso_ate int(12), pais varchar(250), estado varchar(500), cidade varchar(250),  cep_de int(12),  cep_ate int(12), valor varchar(250) , custo varchar(250), website varchar(250),  store varchar(250),  view varchar(250), primary key(id_regra));

		
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 