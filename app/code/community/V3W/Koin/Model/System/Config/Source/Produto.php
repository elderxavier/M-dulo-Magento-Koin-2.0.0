<?php
/*
 * Koin Module - payment method module for Magento, integrating
 * the billing forms with Koin Web Service.
 * Copyright (C) 2013  Koin
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class V3W_Koin_Model_System_Config_Source_Produto
{
	public function toOptionArray ()
	{
		$options = array();
        
        $options[] = array('value' => 'todos', 'label' => Mage::helper('V3W_Koin')->__('Todos os produtos'));
        $options[] = array('value' => 'manual', 'label' => Mage::helper('V3W_Koin')->__('Produto Específico'));
        
		return $options;
	}

}