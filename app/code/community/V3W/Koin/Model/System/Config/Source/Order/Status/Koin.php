<?php
/**
 * Order Statuses source model
 */
class V3W_Koin_Model_System_Config_Source_Order_Status_Koin extends Mage_Adminhtml_Model_System_Config_Source_Order_Status_Processing
{
	const STATE_KOIN_POST_PAID  = 'koin_pos_pago';
	
    public function __construct()
    {
    	$options[] = $this->_stateStatuses;
    	$options[] = self::STATE_KOIN_POST_PAID;
    	$this->_stateStatuses = $options;
    }
}
