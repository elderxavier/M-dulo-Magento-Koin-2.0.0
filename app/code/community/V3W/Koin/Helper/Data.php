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
class V3W_Koin_Helper_Data extends Mage_Core_Helper_Abstract {
	private $_phoneTypeResidencial = 1;
	private $_phoneTypeCelular = 3;
	
    /**
     * Formata o valor da compra de acordo com a definicao da Koin
     * @param   string $argValorOriginal
     * @return  string
     */
    public function formatarValorKoin($argValorOriginal){
		str_replace(',', '.', $argValorOriginal);
		if(strpos($argValorOriginal, ".") == false){
			$valor = $argValorOriginal . ".00";
		} else {
			list($integers, $decimais) = explode(".", $argValorOriginal);
			
			if(strlen($decimais) > 2){
				$decimais = substr($decimais, 0, 2);
			}
			
			while(strlen($decimais) < 2){
				$decimais .= "0";
			}
			
			$valor = $integers .'.'. $decimais;
		}
		return (float) $valor;
    }
    
    /**
     * Retorna mensagem adequada ao codigo de retorno da Koin
     * @param string $statusCode
     * @return string
     */
    public function getStatusMessage($statusCode=''){
    	$koinConfigModel = Mage::getModel('V3W_Koin/KoinConfig');
		switch($statusCode){
			case $koinConfigModel->getStatusKoinFalhaComunicacao():
				$label = "Erro na comunicação com o Webservice Koin";
				break;
			case $koinConfigModel->getStatusKoinAprovado():
				$label = "Aprovado";
				break;
			default:
				$label = "Negado";
		}
		return $label;
    }
    
    /**
     * Retorna o número do telefone e o tipo com base nos dados do cliente
     * @param string $argNumero
     * @param string $argCelular
     * @return array
     */
    private function _verificaTelefone($argNumero='', $argCelular=''){
    	$number = $this->_limpaTelefone($argNumero);
    	$phoneType = $this->_phoneTypeResidencial;
    	
    	$celular = $this->_limpaTelefone($argCelular);
    	
    	if ((strlen($number) < 10) && (strlen($celular) >= 10)){
    		$number = $celular;
    		$phoneType = $this->_phoneTypeCelular;
    	}
    	return array('number'=>$number, 'phoneType'=>$phoneType);
    }
    
    /**
     * Retira os caracteres especiais de máscara do telefone e retorna apenas os números
     * @param string $argTelefone
     * @return string
     */
    private function _limpaTelefone($argTelefone=''){
    	return preg_replace("/[^0-9]/", "", $argTelefone);
    }
    
    /**
     * Separa o número do telefone em codigo de área, número e tipo
     * @param string $argNumero
     * @param string $argCelular
     * @return array
     */
    public function getTelefone($argNumero='', $argCelular=''){
    	$arrayTelefone = $this->_verificaTelefone($argNumero, $argCelular);
    	$numero = $arrayTelefone['number'];
		if(strlen($numero) < 10){
			$numero = str_pad($numero, 10, "0", STR_PAD_LEFT);
		}
		
		$area_code = substr($numero, 0, 2);
		$number    = substr($numero, 2, (strlen($numero) - 2));
		
    	return array('areaCode'=>$area_code, 'number'=>$number, 'phoneType'=>$arrayTelefone['phoneType']);
    }
    
    /**
     * Retira os caracteres especiais do CEP e retorna apenas os números 
     * @param string $argCep
     * @return string
     */
    public function formatarCep($argCep=''){
    	return preg_replace("/[^0-9]/", "", $argCep);
    }
    
    /**
     * Retira os caracteres especiais do documento e retorna apenas os números
     * @param string $argCep
     * @return string
     */
    public function formatarDocumento($argDocumento=''){
    	return preg_replace("/[^0-9]/", "", $argDocumento);
    }
    
    /**
     * Retorna em uma única linha o endereço multi-linhas do magento
     * @deprecated
     * @param string $argEndereco
     * @return string
     */
    public function formatarEndereco($argEndereco=''){
    	$linhas = is_array($argEndereco) ? $argEndereco : explode("\n", $argEndereco);
    	$retorno = '';
    	$i = 0;
    	foreach ($linhas as $linha){
    		if (($i > 0) && trim($linha)){
    			$retorno .= ' - ';
    		}
    		if (trim($linha)){
    			$retorno .= $linha;
    		}
    		$i++;
    	}
    	return $retorno;
    }
    
    /**
     * Retorna um array com o endereço baseando-se no endereço multi-linhas do OneStepCheckout
     * @param string $argEndereco
     * @return array
     */
    public function formatarEnderecoOSC($argEndereco=''){
    	$linhas = is_array($argEndereco) ? $argEndereco : explode("\n", $argEndereco);
    	$retorno = array();
    	$i = 0;
    	
    	//ordem de campos do OSC
    	$retorno['rua']         = trim($linhas[0]);
    	$retorno['numero']      = trim($linhas[1]);
    	$retorno['complemento'] = trim($linhas[2]);
    	$retorno['bairro']      = trim($linhas[3]);
    	
    	return $retorno;
    }
    
    /**
     * Recebe uma data no formato dd/mm/yyyy e verifica se é uma data válida
     * @param string $argData
     * @return boolean
     */
    public function validarData($argData=''){
    	if (strlen($argData) != 10){
    		return false;
    	}
    	$data = explode("/",$argData);
    	$d = $data[0];
    	$m = $data[1];
    	$y = $data[2];
    	
    	return checkdate($m,$d,$y);
    }
    
    /**
     * Faz a validação de números de CPF.
     * @param string $argCpf
     * @return bool
     */
    public function validarCpf($argCpf){
    	$status = false;
    	
    	//retira a máscara
    	$argCpf = $this->formatarDocumento($argCpf);
    
    	if (!is_numeric($argCpf)){
    		return false;
    	}
    
    	if (strlen($argCpf) <> 11){
    		return false;
    	}
    
    	if ($argCpf == '01234567890' || $argCpf == '1234567890') {
    		return false;
    	}
    	
    	$tam_cpf = strlen($argCpf);
    	for($i=0; $i<=$tam_cpf; $i++){
    		if ($argCpf == "$i$i$i$i$i$i$i$i$i$i$i"){
    			return false;
    		}
    	}
    
    	$dv_informado = substr($argCpf, 9, 2);
    
    	for($i=0; $i<=8; $i++){
    		$digito[$i] = substr($argCpf, $i, 1);
    	}
    
    	$posicao = 10;
    	$soma = 0;
    	for($i=0; $i<=8; $i++){
    		$soma = $soma + $digito[$i] * $posicao;
    		$posicao = $posicao - 1;
    	}
    
    	$digito[9] = $soma % 11;
    	if ($digito[9] < 2){
    		$digito[9] = 0;
    	} else {
    		$digito[9] = 11 - $digito[9];
    	}
    
    	$posicao = 11;
    	$soma = 0;
    	for($i = 0; $i <= 9; $i ++){
    		$soma = $soma + $digito [$i] * $posicao;
    		$posicao = $posicao - 1;
    	}
    
    	$digito[10] = $soma % 11;
    	if ($digito[10] < 2) {
    		$digito[10] = 0;
    	} else {
    		$digito[10] = 11 - $digito[10];
    	}
    
    	$dv = $digito[9] * 10 + $digito[10];
    	if ($dv != $dv_informado){
    		return false;
    	} else {
    		return true;
    	}
    }
    
    /**
     * Faz a validação de números de CNPJ.
     * @param string $CampoNumero
     * @return bool
     */
    public function validarCNPJ($CampoNumero) {
    	$return = true;
    
    	$recebeCnpj = ${"CampoNumero"};
    
    	if (strlen($recebeCnpj) != 14){
    		$return = false;
    	} else if ($recebeCnpj == "00000000000000") {
    		$return = false;
    	} else {
    		$numero[1] = intval(substr($recebeCnpj, 1 - 1, 1));
    		$numero[2] = intval(substr($recebeCnpj, 2 - 1, 1));
    		$numero[3] = intval(substr($recebeCnpj, 3 - 1, 1));
    		$numero[4] = intval(substr($recebeCnpj, 4 - 1, 1));
    		$numero[5] = intval(substr($recebeCnpj, 5 - 1, 1));
    		$numero[6] = intval(substr($recebeCnpj, 6 - 1, 1));
    		$numero[7] = intval(substr($recebeCnpj, 7 - 1, 1));
    		$numero[8] = intval(substr($recebeCnpj, 8 - 1, 1));
    		$numero[9] = intval(substr($recebeCnpj, 9 - 1, 1));
    		$numero[10] = intval(substr($recebeCnpj, 10 - 1, 1));
    		$numero[11] = intval(substr($recebeCnpj, 11 - 1, 1));
    		$numero[12] = intval(substr($recebeCnpj, 12 - 1, 1));
    		$numero[13] = intval(substr($recebeCnpj, 13 - 1, 1));
    		$numero[14] = intval(substr($recebeCnpj, 14 - 1, 1));
    			
    		$soma = $numero[1] * 5 + $numero[2] * 4 + $numero[3] * 3 + $numero[4] * 2 + $numero[5] * 9 + $numero[6] * 8 + $numero[7] * 7 + $numero[8] * 6 + $numero[9] * 5 + $numero[10] * 4 + $numero[11] * 3 + $numero[12] * 2;
    			
    		$soma = $soma - (11 * (intval($soma / 11)));
    		if ($soma == 0 || $soma == 1){
    			$resultado1 = 0;
    		} else {
    			$resultado1 = 11 - $soma;
    		}
    			
    		if ($resultado1 == $numero[13]){
    			$soma = $numero[1] * 6 + $numero[2] * 5 + $numero[3] * 4 + $numero[4] * 3 + $numero[5] * 2 + $numero[6] * 9 + $numero[7] * 8 + $numero[8] * 7 + $numero[9] * 6 + $numero[10] * 5 + $numero[11] * 4 + $numero[12] * 3 + $numero[13] * 2;
    			$soma = $soma - (11 * (intval($soma / 11)));
    			if ($soma == 0 || $soma == 1){
    				$resultado2 = 0;
    			} else {
    				$resultado2 = 11 - $soma;
    			}
    			if ($resultado2 != $numero[14]){
    				$return = false;
    			}
    		} else {
    			$return = false;
    		}
    	}
    	return $return;
    }
    
	/**
	 * Recebe uma data no formato dd/mm/yyyy e retorna no formato de banco
	 * @param string $argData
	 * @return string
	 */
    public function formatarData($argData){
    	if ($argData){
    		if (strpos($argData, "/")){
				$retorno = substr($argData, 6, 4) . '-' . substr($argData, 3, 2) . '-' . substr($argData, 0, 2);
    		} else {
    			$retorno = substr($argData, 0, 10);
    		}
    	} else {
   		 	$retorno = "";
   		}
    	return $retorno;
    }
}
