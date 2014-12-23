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
class V3W_Koin_PayController extends Mage_Core_Controller_Front_Action {
	/**
	 * Funcao responsavel por tratar o retorno do webservice de  pagamento da Koin.
	 * @return void
	 */
	public function verifyAction(){
		//se não possui o objeto do webservice carregado redireciona
		if(!Mage::getSingleton('core/session')->getData('koin-transaction')){
			$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			Mage::app()->getFrontController()->getResponse()->setRedirect($url);
			return;
		}
		
		$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->load($orderId);
		
		// recupera o pedido armazenado
		$webServiceOrder = Mage::getSingleton('core/session')->getData('koin-transaction');
		Mage::getSingleton('core/session')->unsetData('koin-transaction');
		$this->loadLayout();
		
		$block = $this->getLayout()->getBlock('V3W_Koin.success');
		$status = $webServiceOrder->status;
		
		$payment = $order->getPayment();
		$payment->setAdditionalInformation('Koin_status', $status);
		$payment->save();
		
		$koinConfigModel = Mage::getModel('V3W_Koin/KoinConfig');
		
		if($status == $koinConfigModel->getStatusKoinAprovado()){
			// envia email de nova compra
			$order->sendNewOrderEmail();
			$order->setEmailSent(true);
			$order->save();

			$invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
			$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
			
			$this->setKoinOrderStatus($order);
			
			// envia email de confirmacao de fatura
			//$invoice->sendEmail(true);
			//$invoice->setEmailSent(true);
			//$invoice->save();
		} else {
			//se não retornou código de aprovação cancela
			$order->cancel()->save();
		}
		
		$this->renderLayout();
	}
	
	/**
	 * Atribui o status de processamento Koin caso configurado
	 * @param unknown $order
	 */
	private function setKoinOrderStatus($order)
	{
		$koinStatus = Mage::getStoreConfig('payment/V3W_Koin_Standard/processing_order_status', $order->getStoreId());
		if ($koinStatus) {
			//recarrega a order pois quando um invoice é gerado o status e estado são modificados
			$order = Mage::getModel('sales/order')->load($order->getId());
			if ($order->getStatus() != $koinStatus){
				$order->setStatus($koinStatus);
				$history = $order->addStatusHistoryComment(null, false);
				$history->setIsCustomerNotified(false);
				$order->save();
			}
		}
	}
}