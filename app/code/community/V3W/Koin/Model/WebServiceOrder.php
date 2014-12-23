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
class V3W_Koin_Model_WebServiceOrder extends V3W_Koin_Model_WebService {
	public $reference;
	public $currency;
	public $requestDate;
	public $price;
	public $paymentType;
	public $fraudId;
	
	public $name;
	public $isFirstPurchase;
	public $isReliable;
	public $buyerType;
	public $emai;
	public $ip;
	
	public $documentType;
	public $documentValue;
	
	public $birthday;
	public $foudingDate;
	public $razaoSocial;
	
	public $areaCode;
	public $number;
	public $phoneType;
	
	public $city;
	public $state;
	public $country;
	public $street;
	public $zipCode;
	public $district;
	public $addressNumber;
	public $complement;
	
	public $items;
	
	public $status;
	public $message;
	
	function __construct($params){
		$baseURL = (isset($params['enderecoBase'])) ? $params['enderecoBase'] : "http://api.koin.com.br/V1/TransactionService.svc/Request";
		$this->_webServiceURL = $baseURL;
		parent::__construct();
	}
	
	/**
	 * Recupera os dados da transação
	 * @return array
	 */
	private function _getDadosTransacao(){
		return array(
			'Reference'   => $this->reference,
			'Currency'    => $this->currency,
			'RequestDate' => $this->requestDate,
			'Price'       => $this->price,
			'PaymentType' => $this->paymentType,
			'FraudId'     => $this->fraudId
		);
	}
	
	/**
	 * Recupera os dados do comprador
	 * @return array
	 */
	private function _getDadosBuyer(){
		return array(
			'Name'            => $this->name,
			'IsFirstPurchase' => $this->isFirstPurchase,
			'IsReliable'      => $this->isReliable,
			'BuyerType'       => $this->buyerType,
			'Email'           => $this->email,
			'Ip'              => $this->ip
		);
	}
	
	/**
	 * Recupera os dados de decumento do comrpador
	 * @return array
	 */
	private function _getDocumentsBuyer(){
		return array(
			array(
				'Key'   => $this->documentType,
				'Value' => $this->documentValue
			)
		);
	}
	
	/**
	 * Recupera os dados adicionais do comprador baseado no tipo de pessoa
	 * @return array
	 */
	private function _getAdditionalInfoBuyer(){
		$tipo_pessoa_juridica = Mage::getModel('V3W_Koin/Buyer')->getTipoPessoaJuridica();
		if ($this->buyerType == $tipo_pessoa_juridica['tipo_pessoa']){
			$retorno = array(
				array(
					'Key'   => 'FoundingDate',
					'Value' => $this->foudingDate
				),
				array(
					'Key'   => 'RazaoSocial',
					'Value' => $this->razaoSocial
				)
			);
		} else {
			$retorno = array(
				array(
					'Key'   => 'Birthday',
					'Value' => $this->birthday
				)
			);
		}
		return $retorno;
	}
	
	/**
	 * Recupera os dados de telefone do comprador
	 * @return array
	 */
	private function _getPhoneBuyer(){
		return array(
			array(
				'AreaCode'  => $this->areaCode,
				'Number'    => $this->number,
				'PhoneType' => $this->phoneType
			)
		);
	}
	
	/**
	 * Recupera os dados de endereço do comprador
	 * @return array
	 */
	private function _getAddressBuyer(){
		$address[] = array();
		
		return array(
			'City'       => $this->city,
			'State'      => $this->state,
			'Country'    => $this->country,
			'Street'     => $this->street,
			'ZipCode'    => $this->zipCode,
			'District'   => $this->district,
			'Number'     => $this->addressNumber,
			'Complement' => $this->complement,
			'AddressType' => 1
		);
	}
	
	/**
	 * Recupera os itens da transação
	 * @return array
	 */
	private function _getItems(){
		$items = array();
		foreach ($this->items as $item){
			$item = array(
				'Reference'   => $item->getSku(),
				'Description' => $item->getName(),
				'Quantity'    => $item->getQty(),
				'Price'       => Mage::helper('V3W_Koin')->formatarValorKoin($item->getProduct()->getFinalPrice()),
				'Category'    => $this->_getItemCategories($item->getProduct())
			);
			
			$items[] = $item;
			
		}
		return $items;
	}
	
	/**
	 * Recupera a lista de categorias do produto.
	 * Limita-se a 50 caracters.
	 * @param unknown $argProduct
	 * @return string
	 */
	private function _getItemCategories($argProduct){
		$categoryIds = $argProduct->getCategoryIds();
		
		if (!is_array($categoryIds) || count($categoryIds)==0){
			return 'Não definida';
		}
		
		foreach($categoryIds as $categoryId) {
			$category = Mage::getModel('catalog/category')->load($categoryId)->getName();
			
			$temp = (isset($categoria)) ? $categoria.", ".$category : $category;
			if (strlen($temp) > 50){
				break;
			} else {
				$categoria = $temp;
			}
		}
		
		return $categoria;
	}
	
	/**
	 * Função responsavel por montar o json de requisicao e 
	 * realizar o envio da transação para Koin
	 * @return int|bool
	 */
	public function requestTransaction(){
		$msg = $this->_getDadosTransacao();
		$msg['Buyer']                   = $this->_getDadosBuyer();
		$msg['Buyer']['Documents']      = $this->_getDocumentsBuyer();
		$msg['Buyer']['AdditionalInfo'] = $this->_getAdditionalInfoBuyer();
		$msg['Buyer']['Phones']         = $this->_getPhoneBuyer();
		$msg['Buyer']['Address']        = $this->_getAddressBuyer();
		$msg['Items']                   = $this->_getItems();

		$msg = json_encode($msg);
		$maxAttempts = 3;
		
		while($maxAttempts > 0){
			if($this->sendRequest($msg)){
				if($this->hasConsultationError()){
					Mage::log($this->_transactionError);
					return false;
				}
				$retorno = json_decode($this->_jsonResponse);
				
				$this->status  = (string) $retorno->Code;
				$this->message = (string) $retorno->Message;
				
				return $this->status;
			}
			
			$maxAttempts--;
		}
		
		if($maxAttempts == 0){
			Mage::log("[KOIN] Não conseguiu consultar o servidor.");
			$this->limpaObjeto();
		}
		
		return false;
	}
}
