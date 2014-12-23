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
class V3W_Koin_Model_WebService {
	public $consumerKey;
	public $secretKey;
	
	protected $_jsonResponse;
	protected $_webServiceURL;
	protected $_transactionError;
	
	function __construct($params){
		$this->status = Mage::getModel('V3W_Koin/KoinConfig')->getStatusKoinFalhacomunicacao();
	}
	
	/**
	 * Funcao utilizada para atribuir os valores base do pedido da koin
	 * @param string $index
	 * @param string $value
	 */
	public function setData($index, $value = null){
		if(is_array($index)){
			foreach($index as $i => $v){
				$this->$i = $v;
			}
		} else {
			$this->$index = $value;
		}
	}
	
	/**
	 * Limpa os atributos do objeto para evitar armazenar dados desnecessários na 
	 * sessão e causar estouro de memória, com excessão do status
	 * @return void
	 */
	protected function limpaObjeto(){
		foreach ($this as $key => $value) {
			if ($key != 'status'){
				unset($this->$key);
			}
		}
	}
	
	/**
	 * Verifica se a string é um json válido
	 * @param string $strJson
	 * @return bool
	 */
	private function _isValidJson($strJson) {
		$data = json_decode($strJson);
		return (!is_null($data));
	}
	
	/**
	 * Função responsavel por conferir se houve erro na requisicao
	 * @return bool
	 */
	protected function hasConsultationError(){
		$resposta = $this->_jsonResponse;
		
		// tempo de requisicao expirou
		if($resposta == null){
			$this->_transactionError = "Tempo de espera na requisição expirou.";
			return true;
		}
		
		if(!$this->_isValidJson($resposta)){
			$this->_transactionError = "Retorno do WS inválido.";
			return true;
		}
		
		return false;
	}
	
	/**
	 * Retorna a msg de erro da requisicao
	 * @return string
	 */
	public function getError(){
		return $this->_transactionError;
	}
	
	/**
	 * Faz a requisição no WS da Koin
	 * @param string $postMsg JSON
	 * @return bool
	 */
	protected function sendRequest($postMsg=''){
		$authorization = $this->_getAuthorization();
		
		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_session, CURLOPT_URL, $this->_webServiceURL);
		curl_setopt($curl_session, CURLOPT_FAILONERROR, true);
		curl_setopt($curl_session, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_session, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl_session, CURLOPT_POST, true);
		if ($postMsg){
			curl_setopt($curl_session, CURLOPT_POSTFIELDS, $postMsg);
		}
		curl_setopt($curl_session, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=utf-8',
															'Content-Length:'.strlen($postMsg),
															"Authorization: {$authorization}"));
		
		$this->limpaObjeto();
		$this->_jsonResponse = curl_exec($curl_session);
		if(!$this->_jsonResponse){
			//echo 'Curl error: ' . curl_error($curl_session);
			return false;
		}
		
		curl_close($curl_session);
		return true;
	}		
	
	/**
	 * Monta e retorna o hash de autenticação do WS Koin
	 * @return string
	 */
	private function _getAuthorization(){
		$consumerKey = $this->consumerKey;
		$secretKey   = $this->secretKey;
		$url         = $this->_webServiceURL;
		
		//o timestamp a ser enviado precisa ser UTC
		$timezonePadrao =  date_default_timezone_get();
		date_default_timezone_set("UTC");
		$time = time();
		date_default_timezone_set($timezonePadrao);
		
		$binaryHash = hash_hmac('sha512', $url . $time, $secretKey, true);
		$hash = base64_encode($binaryHash);
		return "{$consumerKey},{$hash},{$time}";
	}
}
