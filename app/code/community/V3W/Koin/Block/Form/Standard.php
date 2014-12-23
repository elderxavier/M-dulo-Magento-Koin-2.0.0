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
class V3W_Koin_Block_Form_Standard extends Mage_Payment_Block_Form {
    protected function _construct(){
        parent::_construct();
        $this->setTemplate('koin/form/standard.phtml');
    }
	
	/**
     * Pega os valores da configuracao do modulo
     * @param string $config
     * @return void
     */
    public function getConfigData($config){
    	return Mage::getStoreConfig('payment/V3W_Koin_Standard/' . $config);
	}
	
	/**
	 * Pega os valores da configuracao do modulo
	 * @param string $config
	 * @return void
	 */
	public function getConfigAdvancedData($config){
		return Mage::getStoreConfig('payment/koin_configuracoes_avancadas/' . $config);
	}
	
	/**
	 * Recupera os inputs que devem ser exibidos no checkout
	 * @return array
	 */
	public function getFormInputs(){
		$retorno = array();
				
		return $retorno;
	}
	
	/**
	 * Recupera os IDs dos campos de CPF e CNPJ configurados
	 * @retur array
	 */
	public function getIdCpfCnpjInput(){
		$storeId = Mage::app()->getStore()->getId();
		
		$retorno['id_input_cpf']  = $this->getConfigAdvancedData('koin_id_input_cpf', $storeId);
		$retorno['id_input_cnpj'] = $this->getConfigAdvancedData('koin_id_input_cnpj', $storeId);
		return $retorno;
	}
	
	public function exibirTermo(){

		return true;
	}
}
