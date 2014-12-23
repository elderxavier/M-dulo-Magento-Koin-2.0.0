<?php
/**
 *
 * @package     default_koin
 * @copyright   E-Lustre www.e-lustre.com.br
 * @Autor     	Elder Xavier
 * @Email		eldersxavier@gmail.com / desenv@e-lustre.com.br
 * @Created at 	2014-17-12
 * @Update		2014-17-12
 */
require_once "app/Mage.php";
Mage::app('default');
umask(0);
	 
$email= $_POST["email"];
$day = $_POST["day"];
$mont = $_POST["mont"];
$year = $_POST["year"];
$retorna=0;
$customer = Mage::getModel('customer/customer');
$websiteId = Mage::app()->getWebsite()->getId();
$customer->setWebsiteId($websiteId);
$customer->loadByEmail($email);  
  if ($customer->getId()) {
		$customer->setDob($year.'-'.$mont.'-'.$day);
		$customer->save();
		$retorna=1;	
    }	
	
	echo $retorna;
 //echo $email;

?>