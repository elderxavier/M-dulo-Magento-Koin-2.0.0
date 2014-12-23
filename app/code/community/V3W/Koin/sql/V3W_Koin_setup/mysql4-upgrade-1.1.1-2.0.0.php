<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	INSERT INTO  `{$this->getTable('sales/order_status')}` (
		`status` ,
		`label`
	) VALUES (
		'aprovado_koin',
		'Aprovado Koin'
	);
	INSERT INTO  `{$this->getTable('sales/order_status_state')}` (
		`status` ,
		`state` ,
		`is_default`
	) VALUES (
		'aprovado_koin',
		'complete',
		'0'
	);
");
$installer->endSetup();
?>