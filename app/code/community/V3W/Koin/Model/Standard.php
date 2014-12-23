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
class V3W_Koin_Model_Standard extends Mage_Payment_Model_Method_Abstract {
    protected $_code  = 'V3W_Koin_Standard';
    protected $_formBlockType = 'V3W_Koin/form_standard';
    protected $_infoBlockType = 'V3W_Koin/info_standard';
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;
    
    /**
     * Pega os valores da configuracao do modulo
     * @param string $config
     * @return void
     */
    public function getConfigAdvancedData($config){
    	return Mage::getStoreConfig('payment/koin_configuracoes_avancadas/' . $config);
    }
	    
    /**
     * Assign data to info model instance
     * @param mixed $data
     * @return Mage_Payment_Model_Info
     */
    public function assignData($data){
        if (!($data instanceof Varien_Object)){
            $data = new Varien_Object($data);
        }

        //Captura dados comprador
        $dadosSaffira = Mage::getSingleton('checkout/session')->getQuote();

        //Captura de formata data nascimento
        $dataNasc = substr($dadosSaffira->getCustomer()->_data["dob"], 0, 10);
	
    	//recupera os inputs koin
		$additionaldata = array();
		if ($data->offsetExists('koin_fraud_id')){
			$additionaldata['koin_fraud_id'] = $data->getKoinFraudId();
		}
		if ($data->offsetExists('koin_cpf')){
			$additionaldata['koin_cpf'] = $data->getKoinCpf();
		}
		if ($data->offsetExists('koin_birthday')){
			$additionaldata['koin_birthday'] = $data->getKoinBirthday();
		}
		if ($data->offsetExists('koin_cnpj')){
			$additionaldata['koin_cnpj'] = $data->getKoinCnpj();
		}
		if ($data->offsetExists('koin_founding_date')){
			$additionaldata['koin_founding_date'] = $data->getKoinFoundingDate();
		}
		if ($data->offsetExists('koin_termo_aceite')){
			$additionaldata['koin_termo_aceite'] = $data->getKoinTermoAceite();
		}
		
		$info = $this->getInfoInstance();
		$info->setAdditionalData(serialize($additionaldata));
		
		return $this;
    }
	
	
	/**
	 * Valida dados
	 * @return  Mage_Payment_Model_Abstract
	 */
	public function validate(){
		parent::validate();

		//recupera os inputs do meio koin
		$info = unserialize($this->getInfoInstance()->getAdditionalData());
		$errorMsg = false;

		$dadosComprador = array();
		if (Mage::helper('customer')->isLoggedIn()){
			$dadosComprador = Mage::getSingleton('customer/session')->getCustomer()->getData();
		}


        //Captura dados comprador
        $dadosSaffira = Mage::getSingleton('checkout/session')->getQuote();

        //Captura de formata data nascimento
        $dataNasc = substr($dadosSaffira->getCustomer()->_data["dob"], 0, 10);







        //Validações de pessoa física
		if (!$errorMsg){
			if (isset($info['koin_cpf']) && ! $dadosSaffira->getCustomerTaxvat() && !Mage::helper('V3W_Koin')->validarCpf($info['koin_cpf'])){
				$errorMsg = 'Informe um CPF válido para o meio de pagamento Koin';
			}
		}

		if (!$errorMsg){
			if (isset($info['koin_birthday']) && !$dataNasc && !Mage::helper('V3W_Koin')->validarData($info['koin_birthday'])){
				$errorMsg = 'Informe uma data de nascimento válida para o meio de pagamento Koin ';
			}
		}

		//Validações de pessoa jurídica
		if (!$errorMsg){
			if (isset($info['koin_cnpj']) && !$dadosSaffira->getCustomerTaxvat() && !Mage::helper('V3W_Koin')->validarCnpj($info['koin_cnpj'])){
				$errorMsg = 'Informe um CNPJ válido para o meio de pagamento Koin';
			}
		}

		if (!$errorMsg){
			if (isset($info['koin_founding_date']) && !$dataNasc && !Mage::helper('V3W_Koin')->validarData($info['koin_founding_date'])){
				$errorMsg = 'Informe uma data de fundação válida para o meio de pagamento Koin';
			}
		}

		if (!$errorMsg){
			if (isset($info['koin_termo_aceite']) && !$info['koin_termo_aceite']){
				$errorMsg = 'Para finalizar a compra é necessário aceitar os termos de uso Koin';
			}
		}

		if($errorMsg){
			Mage::throwException($errorMsg);
		}

		return $this;
	}
	
	/**
     *  Getter da instancia do pedido
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder(){
		return $this->_order;
    }

    /**
     *  Setter da instancia do pedido
     *  @param Mage_Sales_Model_Order $order
     */
    public function setOrder($order){
		if ($order instanceof Mage_Sales_Model_Order){
		    $this->_order = $order;
		} elseif (is_numeric($order)){
		    $this->_order = Mage::getModel('sales/order')->load($order);
		} else {
		    $this->_order = null;
		}
		return $this;
    }
    
    /**
     * Atualiza os dados do comprador caso necessário e retorna um array com os dados 
     * @param object $argOrder
     * @return array
     */
    public function _recuperarDadosComprador($argOrder){
		$dadosComprador = array();

        //Captura dados comprador
        $dadosSaffira = Mage::getSingleton('checkout/session')->getQuote();

        //Captura de formata data nascimento
        $dataNasc = substr($dadosSaffira->getCustomer()->_data["dob"], 0, 10);


		
		//dados postados
		$payment = $argOrder->getPayment();
		$additionaldata = unserialize($payment->getData('additional_data'));
		
		if (!isset($additionaldata['koin_termo_aceite']) && Mage::helper('customer')->isLoggedIn()){
			$additionaldata = Mage::getSingleton('customer/session')->getCustomer()->getData();
		}
		$koinCpf = Mage::helper('V3W_Koin')->formatarDocumento($dadosSaffira->getCustomerTaxvat());
		$koinBirthday = $dataNasc;
		$koinCnpj = Mage::helper('V3W_Koin')->formatarDocumento($dadosSaffira->getCustomerTaxvat());
		$koinFoundingDate = $dataNasc;;
		$koinTermoAceite = $additionaldata["koin_termo_aceite"];
			
		$dadosComprador = $argOrder->getBillingAddress()->getData();
		
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$customerData = Mage::getModel('customer/customer')->load($customer->getId())->getData();
			$dadosComprador['email'] = $customerData['email']; 
		}
		
		
			$dadosComprador['koin_cpf'] = $koinCpf;
		
		
			$dadosComprador['koin_birthday'] = $koinBirthday;
		
		
			$dadosComprador['koin_cnpj'] = $koinCnpj;
		
		
			$dadosComprador['koin_founding_date'] = $koinFoundingDate;
				
		if (!$dadosComprador['koin_termo_aceite'] && $koinTermoAceite)
			$dadosComprador['koin_termo_aceite'] = $koinTermoAceite;
			
		return $dadosComprador;
    }
    
    private function _saveKoinDataCustomer(){
		$info = $this->getInfoInstance();
			$order = $info->getQuote();
		
		$dadosComprador = array();

        //Captura dados comprador
        $dadosSaffira = Mage::getSingleton('checkout/session')->getQuote();

        //Captura de formata data nascimento
        $dataNasc = substr($dadosSaffira->getCustomer()->_data["dob"], 0, 10);
		  
		if($order->getCustomerId()){
			$payment = $order->getPayment();
			$additionaldata = unserialize($payment->getData('additional_data'));
			$koinCpf = Mage::helper('V3W_Koin')->formatarDocumento($dadosSaffira->getCustomerTaxvat());
			$koinBirthday = $dataNasc;
			$koinCnpj = Mage::helper('V3W_Koin')->formatarDocumento($dadosSaffira->getCustomerTaxvat());
			$koinFoundingDate = $dataNasc;
			$koinTermoAceite = $additionaldata["koin_termo_aceite"];
			
			$comprador = $dadosComprador = Mage::getSingleton('customer/session')->getCustomer();
			$dadosComprador = $comprador->getData();
				
			$atualizar = false;
		
			if (!$dadosComprador['koin_cpf'] && $koinCpf){
				$comprador->setKoinCpf($koinCpf);
				$atualizar = true;
			}
		
			if (!$dadosComprador['koin_birthday'] && $koinBirthday){
				$comprador->setKoinBirthday($koinBirthday);
				$atualizar = true;
			}
		
			if (!$dadosComprador['koin_cnpj'] && $koinCnpj){
				$comprador->setKoinCnpj($koinCnpj);
				$atualizar = true;
			}
		
			if (!$dadosComprador['koin_founding_date'] && $koinFoundingDate){
				$comprador->setKoinFoundingDate($koinFoundingDate);
				$atualizar = true;
			}
		
			if (!$dadosComprador['koin_termo_aceite'] && $koinTermoAceite){
				$comprador->setKoinTermoAceite($koinTermoAceite);
				$atualizar = true;
			}
		
			if ($atualizar)
				$comprador->save();
		}
    }
    
	/**
     * Faz o envio dos dados para Koin
     * @return void
     */
	public function getOrderPlaceRedirectUrl(){
		$this->_saveKoinDataCustomer();
		return Mage::getUrl('koin/pay/verify');
    }
}