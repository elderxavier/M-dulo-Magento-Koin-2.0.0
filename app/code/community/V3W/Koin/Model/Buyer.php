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
class V3W_Koin_Model_Buyer {
    
    /**
     * Retorna o tipo de a chave do documento de pessoa física da Koin
     * @return array
     */
    public function getTipoPessoaFisica(){
		return array (
			'tipo_pessoa'=>1,
			'tipo_documento'=>'CPF'
        );
    }
    
    /**
     * Retorna o tipo de a chave do documento de pessoa jurídica da Koin
     * @return array
     */
    public function getTipoPessoaJuridica(){
    	return array (
    		'tipo_pessoa'=>2,
    		'tipo_documento'=>'CNPJ'
    	);
    }
    
    /**
     * Recupera o código do tipo de pessoa da Koin com base nos dados do comprador
     * @param array $argDadosComprador
     * @return int
     */
    public function getTipoPessoa(&$argDadosComprador=''){
    	//apenas para o modulo OSC
    	if (is_array($argDadosComprador)){
    		$tipo_pessoa = ($argDadosComprador['tipopessoa']) ? $argDadosComprador['tipopessoa'] : '';
	    	if ($tipo_pessoa=='Juridica'){
		   		$tipo_pessoa = $this->getTipoPessoaJuridica();
	    		return $tipo_pessoa['tipo_pessoa'];
	    	}
    	}
    	$tipo_pessoa = $this->getTipoPessoaFisica();
    	return $tipo_pessoa['tipo_pessoa'];
    }
    
    /**
     * Retorna o tipo de documento com base no tipo de pessoa Koin
     * @param int $argTipoPessoa
     * @return string
     */
    public function getTipoDocumento($argTipoPessoa){
    	$tipo_pessoa_fisica = $this->getTipoPessoaFisica();
    	if ($argTipoPessoa===$tipo_pessoa_fisica['tipo_pessoa']){
    		return $tipo_pessoa_fisica['tipo_documento'];
    	}
    	$tipo_pessoa_juridica = $this->getTipoPessoaJuridica();
    	if ($argTipoPessoa===$tipo_pessoa_juridica['tipo_pessoa']){
    		return $tipo_pessoa_juridica['tipo_documento'];
    	}
    	return '';
    }
    
    /**
     * Recupera o número do documento com base nos dados do comprador
     * @param array $argDadosComprador
     * @param int $argTipoPessoa
     * @return string
     */
    public function getDocumento($argDadosComprador, $argTipoPessoa){
    	$tipo_pessoa_fisica = $this->getTipoPessoaFisica();
    	if ($argTipoPessoa===$tipo_pessoa_fisica['tipo_pessoa']){
    		return $argDadosComprador['koin_cpf'];
    	}
    	$tipo_pessoa_juridica = $this->getTipoPessoaJuridica();
    	if ($argTipoPessoa===$tipo_pessoa_juridica['tipo_pessoa']){
    		return $argDadosComprador['koin_cnpj'];
    	}
    	return '';
    }
    
    /**
     * Verifica se o comprador possui compras anteriores na loja
     * @return boolean
     */
    public function isFirstPurchase($argCompradorId=null){
    	$count = 0;
    	if ($argCompradorId){
    		$count = Mage::getModel('sales/order')->getCollection()
    			->addFieldToFilter('customer_id',$argCompradorId)
    			->addAttributeToFilter('status', array(
    					array('finset' => Mage_Sales_Model_Order::STATE_PROCESSING),
    					array('finset' => Mage_Sales_Model_Order::STATE_COMPLETE),
    					array('finset' => Mage_Sales_Model_Order::STATE_CLOSED)
    				)
    			)
    			->getSize();
    	}
    	return ($count == 0) ? true : false;
    }
    
    /**
     * Verifica se o comprador é confiável
     * TODO
     * @return boolean
     */
    public function isReliable(){
    	return true;
    }
}