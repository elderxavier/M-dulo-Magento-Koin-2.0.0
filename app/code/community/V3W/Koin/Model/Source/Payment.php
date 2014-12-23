<?php


class V3W_Koin_Model_Source_Payment
{
    public function toOptionArray()
    {
        return array(
            array('value' => Mage_Sales_Model_Order::STATE_COMPLETE, 'label'=>Mage::helper('adminhtml')->__('Aprovado Koin')),
        );
    }
}