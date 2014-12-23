<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer = $this;
$installer->startSetup();

$atributo = 'koin_termo_aceite';
$table = $this->getTable('eav/attribute');
$sqlTeste = "SELECT * FROM {$table} WHERE attribute_code = '{$atributo}'";
$connectionTeste = Mage::getSingleton('core/resource')->getConnection('core_read');
$termoTeste = $connectionTeste->fetchAll($sqlTeste);

if(@$termoTeste[0]['attribute_code'] != $atributo){
	$config = array(
		'type' => 'int',
		'input' => 'text',
		'label' => 'Aceite do Termo de Uso',
		'global' => 1,
		'visible' => 0,
		'required' => 0,
		'user_defined' => 1,
		'default' => '',
		'visible_on_front' => 0
	);
	
	//Adiciona o campo no billing e shipping address
	$setup->addAttribute('customer_address', $atributo, $config);
	$tableQuote = $this->getTable('sales/order_address');
	Mage::log("TABELA AceiteTermoVersao ".$tableQuote);
	$installer->run("ALTER TABLE  $tableQuote ADD {$atributo} TINYINT(1) NULL");
	
	//Adicionar o campo no customer
	$setup->addAttribute('customer', $atributo, $config);
	
	if (version_compare(Mage::getVersion(), '1.4.2', '>=')){
		$tableQuote = $this->getTable('sales/quote');
		Mage::log("TABELA AceiteTermoVersao ".$tableQuote);
		$installer->run("ALTER TABLE  $tableQuote ADD customer_{$atributo} TINYINT(1) NULL");
	}
};

$installer->endSetup();