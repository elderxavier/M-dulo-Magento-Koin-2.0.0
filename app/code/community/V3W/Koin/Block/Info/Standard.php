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
class V3W_Koin_Block_Info_Standard extends Mage_Payment_Block_Info {
    /**
     * Init default template for block
     */
    protected function _construct(){
        parent::_construct();
        $this->setTemplate('koin/info/standard.phtml');
    }
    
    public function toPdf(){
        $this->setTemplate('payment/info/pdf/cc.phtml');
        return $this->toHtml();
    }
}
