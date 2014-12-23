<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	INSERT INTO  `{$this->getTable('sales/order_status')}` (
		`status` ,
		`label`
	) VALUES (
		'koin_aprovado',
		'Aprovado Koin'
	);
	INSERT INTO  `{$this->getTable('sales/order_status_state')}` (
		`status` ,
		`state` ,
		`is_default`
	) VALUES (
		'koin_aprovado',
		'complete',
		'0'
	);
");
$installer->endSetup();
?>