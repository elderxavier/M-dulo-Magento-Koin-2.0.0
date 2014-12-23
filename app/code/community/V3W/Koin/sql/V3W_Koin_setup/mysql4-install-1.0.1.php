<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer = $this;
$installer->startSetup();


$atributo = 'koin_cpf';
$table = $this->getTable('eav/attribute');
$sqlTeste = "SELECT * FROM {$table} WHERE attribute_code = '{$atributo}'";
$connectionTeste = Mage::getSingleton('core/resource')->getConnection('core_read');
$estadoTeste = $connectionTeste->fetchAll($sqlTeste);

if(@$estadoTeste[0]['attribute_code'] != $atributo){
	$config = array(
		'type' => 'varchar',
		'input' => 'text',
		'label' => 'CPF',
		'global' => 1,
		'visible' => 1,
		'required' => 0,
		'user_defined' => 1,
		'default' => '',
		'visible_on_front' => 0
	);
	
	//Adiciona o campo no billing e shipping address
	$setup->addAttribute('customer_address', $atributo, $config);
	$tableQuote = $this->getTable('sales/order_address');
	Mage::log("TABELA CPF ".$tableQuote);
	$installer->run("ALTER TABLE  $tableQuote ADD {$atributo} VARCHAR(25) NULL");
        
	//Adicionar o campo no customer
	$setup->addAttribute('customer', $atributo, $config);
	
	if (version_compare(Mage::getVersion(), '1.4.2', '>=')){
        $tableQuote = $this->getTable('sales/quote');
        Mage::log("TABELA CPF ".$tableQuote);
        $installer->run("ALTER TABLE  $tableQuote ADD customer_{$atributo} VARCHAR(25) NULL");
	}
};


$atributo = 'koin_cnpj';
$table = $this->getTable('eav/attribute');
$sqlTeste = "SELECT * FROM {$table} WHERE attribute_code = '{$atributo}'";
$connectionTeste = Mage::getSingleton('core/resource')->getConnection('core_read');
$estadoTeste = $connectionTeste->fetchAll($sqlTeste);

if(@$estadoTeste[0]['attribute_code'] != $atributo){
	$config = array(
			'type' => 'varchar',
			'input' => 'text',
			'label' => 'CNPJ',
			'global' => 1,
			'visible' => 1,
			'required' => 0,
			'user_defined' => 1,
			'default' => '',
			'visible_on_front' => 0
	);

	//Adiciona o campo no billing e shipping address
	$setup->addAttribute('customer_address', $atributo, $config);
	$tableQuote = $this->getTable('sales/order_address');
	Mage::log("TABELA CNPJ ".$tableQuote);
	$installer->run("ALTER TABLE  $tableQuote ADD {$atributo} VARCHAR(25) NULL");

	//Adicionar o campo no customer
	$setup->addAttribute('customer', $atributo, $config);

	if (version_compare(Mage::getVersion(), '1.4.2', '>=')){
		$tableQuote = $this->getTable('sales/quote');
		Mage::log("TABELA CNPJ ".$tableQuote);
		$installer->run("ALTER TABLE  $tableQuote ADD customer_{$atributo} VARCHAR(25) NULL");
	}
};


$atributo = 'koin_birthday';
$table = $this->getTable('eav/attribute');
$sqlTeste = "SELECT * FROM {$table} WHERE attribute_code = '{$atributo}'";
$connectionTeste = Mage::getSingleton('core/resource')->getConnection('core_read');
$estadoTeste = $connectionTeste->fetchAll($sqlTeste);

if(@$estadoTeste[0]['attribute_code'] != $atributo){
	$config = array(
			'type' => 'datetime',
			'input' => 'date',
			'label' => 'Data de nascimento',
			'global' => 1,
			'visible' => 1,
			'required' => 0,
			'user_defined' => 1,
			'default' => '',
			'visible_on_front' => 0
	);

	//Adiciona o campo no billing e shipping address
	$setup->addAttribute('customer_address', $atributo, $config);
	$tableQuote = $this->getTable('sales/order_address');
	Mage::log("TABELA Birthday ".$tableQuote);
	$installer->run("ALTER TABLE  $tableQuote ADD {$atributo} DATETIME NULL");

	//Adicionar o campo no customer
	$setup->addAttribute('customer', $atributo, $config);

	if (version_compare(Mage::getVersion(), '1.4.2', '>=')){
		$tableQuote = $this->getTable('sales/quote');
		Mage::log("TABELA Birthday ".$tableQuote);
		$installer->run("ALTER TABLE  $tableQuote ADD customer_{$atributo} DATETIME NULL");
	}
};


$atributo = 'koin_founding_date';
$table = $this->getTable('eav/attribute');
$sqlTeste = "SELECT * FROM {$table} WHERE attribute_code = '{$atributo}'";
$connectionTeste = Mage::getSingleton('core/resource')->getConnection('core_read');
$estadoTeste = $connectionTeste->fetchAll($sqlTeste);

if(@$estadoTeste[0]['attribute_code'] != $atributo){
	$config = array(
			'type' => 'datetime',
			'input' => 'date',
			'label' => 'Data de fundação',
			'global' => 1,
			'visible' => 1,
			'required' => 0,
			'user_defined' => 1,
			'default' => '',
			'visible_on_front' => 0
	);

	//Adiciona o campo no billing e shipping address
	$setup->addAttribute('customer_address', $atributo, $config);
	$tableQuote = $this->getTable('sales/order_address');
	Mage::log("TABELA FoundingDate ".$tableQuote);
	$installer->run("ALTER TABLE  $tableQuote ADD {$atributo} DATETIME NULL");

	//Adicionar o campo no customer
	$setup->addAttribute('customer', $atributo, $config);

	if (version_compare(Mage::getVersion(), '1.4.2', '>=')){
		$tableQuote = $this->getTable('sales/quote');
		Mage::log("TABELA FoundingDate ".$tableQuote);
		$installer->run("ALTER TABLE  $tableQuote ADD customer_{$atributo} DATETIME NULL");
	}
};

$installer->endSetup();