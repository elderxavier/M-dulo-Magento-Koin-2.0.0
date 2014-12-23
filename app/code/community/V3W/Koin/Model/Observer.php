<?php
class V3W_Koin_Model_Observer {
	
	/**
	 * @param Varien_Object $observer
	 */
	public function requestKoin(Varien_Object $observer)
	{
		
		$order = $observer->getQuote();
		
		if ($order->getPayment()->getMethodInstance()->getCode() != 'V3W_Koin_Standard') {
			return;
		}
		
		$storeId = $order->getStoreId();
		
		$dadosComprador = Mage::getModel('V3W_Koin/Standard')->_recuperarDadosComprador($order);
		
		$additionaldata = unserialize($order->getPayment()->getData('additional_data'));
		$dadosComprador['koin_fraud_id'] = $additionaldata["koin_fraud_id"];
		
        $dadosSaffira = Mage::getSingleton('checkout/session')->getQuote();
		$saffiraBilling = $dadosSaffira->getShippingAddress();
		$saffiraUf = $saffiraBilling->getRegion();
		$saffiraNumber = $saffiraBilling->getStreet(2);
		
		  function limpa_string($var, $enc = 'UTF-8')
        {
            $acentos = array(
                'a' => '/À|Á|Â|Ã|Ä|Å/',
                'a' => '/à|á|â|ã|ä|å/',
                'c' => '/Ç/',
                'c' => '/ç/',
                'e' => '/È|É|Ê|Ë/',
                'e' => '/è|é|ê|ë/',
                'i' => '/Ì|Í|Î|Ï/',
                'i' => '/ì|í|î|ï/',
                'n' => '/Ñ/',
                'n' => '/ñ/',
                'o' => '/Ò|Ó|Ô|Õ|Ö/',
                'o' => '/ò|ó|ô|õ|ö/',
                'u' => '/Ù|Ú|Û|Ü/',
                'u' => '/ù|ú|û|ü/',
                'y' => '/Ý/',
                'y' => '/ý|ÿ/',
                'a.' => '/ª/',
                'o.' => '/º/'
            );

            $var = preg_replace($acentos, array_keys($acentos), $var);

            $var = strtolower($var);

            $var = str_replace(" ", "_", $var);

            return $var;
		}
		
		
		 $_state_sigla = array(
        		'acre' => 'AC',
        		'alagoas' => 'AL',
        		'amapa' => 'AP',
        		'amazonas' => 'AM',
        		'bahia' => 'BA',
        		'ceara' => 'CE',
        		'distrito_federal' => 'DF',
        		'espirito_santo' => 'ES',
        		'goias' => 'GO',
        		'maranhao' => 'MA',
        		'mato_grosso' => 'MT',
        		'mato_grosso_do_sul' => 'MS',
        		'minas_gerais' => 'MG',
        		'para' => 'PA',
        		'paraiba' => 'PB',
        		'parana' => 'PR',
        		'pernambuco' => 'PE',
        		'piaui' => 'PI',
        		'rio_de_janeiro' => 'RJ',
        		'rio_grande_do_norte' => 'RN',
        		'rio_grande_do_sul' => 'RS',
        		'rondonia' => 'RO',
        		'roraima' => 'RR',
        		'santa_catarina' => 'SC',
        		'sao_paulo' => 'SP',
        		'sergipe' => 'SE',
        		'tocatins' => 'TO'
        );
		
		
		 if(strlen($saffiraUf) !=2){
		
		$saffiraUf = limpa_string($saffiraUf);
		$saffiraUf = $_state_sigla[$saffiraUf ];
		}
		
		
		
		
		
		$enderecoEntrega = $order->getShippingAddress()->getData();
		$dadosEndereco = Mage::helper('V3W_Koin')->formatarEnderecoOSC($enderecoEntrega['street']);
		
		$koinConfigModel = Mage::getModel('V3W_Koin/KoinConfig');
		$buyerModel = Mage::getModel('V3W_Koin/Buyer');
		
		$tipoPessoa = $buyerModel->getTipoPessoa($dadosComprador);
		$telefone = Mage::helper('V3W_Koin')->getTelefone($dadosComprador['telephone'], $dadosComprador['celular']); //verificar qdo checkout padrão

		$reprocessorId = intval(Mage::getSingleton('core/session')->getReprocessorId());
        if ($reprocessorId == NULL){
            $reprocessorId = 1;
        }
		
		$webServiceOrderData = array (
			//autenticacao
			'consumerKey' => Mage::getStoreConfig('payment/V3W_Koin_Standard/consumer_key', $storeId),
			'secretKey'   => Mage::getStoreConfig('payment/V3W_Koin_Standard/secret_key', $storeId),
	
			//dados transação
			'reference'   => $reference = $order->getReservedOrderId().'_'.str_pad($customerId,1,'0',STR_PAD_LEFT).''.$reprocessorId,
			'currency'    => $koinConfigModel->getKoinCurrency(),
			'requestDate' => date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())),
			'price'       => Mage::helper('V3W_Koin')->formatarValorKoin($order->getGrandTotal()),
			'paymentType' => trim(Mage::getModel('V3W_Koin/Standard')->getConfigAdvancedData('koin_produto_codigo', $storeId)),
			'fraudId'     => $dadosComprador['koin_fraud_id'],
				
			//buyer
			'name'            => $dadosComprador['firstname']." ".$dadosComprador['lastname'],
			'isFirstPurchase' => $buyerModel->isFirstPurchase($customerId),
			'isReliable'      => $buyerModel->isReliable(),
			'buyerType'       => $tipoPessoa,
			'email'           => $dadosComprador['email'],
			'ip'              => $_SERVER['REMOTE_ADDR'],
				
			//documentos
			'documentType'  => $buyerModel->getTipoDocumento($tipoPessoa),
			'documentValue' => Mage::helper('V3W_Koin')->formatarDocumento($buyerModel->getDocumento($dadosComprador, $tipoPessoa)),
		
			//dados adicionais
			//-fisica
			'birthday'   => $dadosComprador['koin_birthday'],
				
			//-juridica
			'foudingDate' => $dadosComprador['koin_founding_date'],
			'razaoSocial' => $dadosComprador['empresa'],
				
			//telefone
			'areaCode'  => $telefone['areaCode'],
			'number'    => $telefone['number'],
			'phoneType' => $telefone['phoneType'],
				
			//endereço
			'city'          => $enderecoEntrega['city'],
			'state'         => $saffiraUf,
			'country'       => Mage::getModel('directory/country')->load($enderecoEntrega['country_id'])->getName(),
				
			//formato concatenando os dados em uma linha
			//'street'        => Mage::helper('V3W_Koin')->formatarEndereco($enderecoEntrega['street']),
						
			//formato separando os dados de endereço na ordem do OneStepcheckout
			'street'        => $dadosEndereco['rua'],
			'district'      => $dadosEndereco['bairro'],
			'addressNumber' => $saffiraNumber,
			'complement'    => $dadosEndereco['complemento'],
				
			'zipCode'       => Mage::helper('V3W_Koin')->formatarCep($enderecoEntrega['postcode']),
				
			//itens
			'items' => $order->getAllVisibleItems()
		);

		$webServiceOrder = Mage::getModel('V3W_Koin/webServiceOrder', array('enderecoBase' => Mage::getStoreConfig('payment/V3W_Koin_Standard/url', $storeId)));
		$webServiceOrder->setData($webServiceOrderData);
		$webServiceOrder->requestTransaction();
		
		$this->verifyKoinStatus($observer, $webServiceOrder->status, $webServiceOrder->message);
		
		Mage::getSingleton('core/session')->setData('koin-transaction', $webServiceOrder);
	}
	
	private function verifyKoinStatus($observer, $status, $message)
	{
		$koinConfigModel = Mage::getModel('V3W_Koin/Koinconfig');
		if($status != $koinConfigModel->getStatusKoinAprovado()) {
			$message = ($message) ? ' - ' . $message : '';
			$errorMessage = 'Não foi possível realizar o pagamento utilizando Koin. Motivo: ' . Mage::helper('V3W_Koin')->getStatusMessage($status) . ' (' . $status . $message . ')';
            Mage::getSingleton('core/session')->setReprocessorId(Mage::getSingleton('core/session')->getReprocessorId() + 1);
			Mage::throwException($errorMessage);
		}else{
            Mage::getSingleton('core/session')->unsReprocessorId();
        }
	}
}