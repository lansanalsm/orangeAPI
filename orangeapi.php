<?php

function chargeAmountUser($msisdn, $price){
   
	include 'vars.php';
	$msisdn = str_replace("+","",$msisdn);
	
	$url = $urlChargeAmount;
	date_default_timezone_set('UTC');
	$thisDate = date("YmdHis");
	$data = '{"endUserId":"tel:+'.$msisdn.'","transactionOperationStatus":"Charged","chargingInformation":{"description":"service de test","amount": '.$price.',"currency": "'.$cur.'"},"chargingMetaData":{"serviceID": "Test ServiceID #1","productID":"Test ProductID #1" },"referenceCode":"REF-'.$thisDate.'","clientCorrelator":"'.$thisDate.'"}';
	$headerAuth = 'Authorization: Bearer '.$token;
	$data_string = $data;  //json_encode($data);
	
	$ch = curl_init();	//  Initiate curl
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                              
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',$headerAuth));
	curl_setopt($ch, CURLOPT_HEADER, 0);  //TRUE to include the header in the output.
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	// Will return the response, if false it print the response
	curl_setopt($ch, CURLOPT_URL,$url);	// Set the url
	$result=curl_exec($ch);	// Execute
	$parsed_json = json_decode($result, true);
	$httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	$curl_errno = curl_errno($ch);
	if ($curl_errno > 0) {
		if (isset($_GET["debug"]) && $_GET["debug"]==1){
		   echo "<br/>cURL Error ($curl_errno): $curl_error\n<br/><br/>";
		}
	}
	
	$data_array[0]=$httpstatus;
	$data_array[1]=$result;
	curl_close($ch);	// Closing
	return $data_array;
}

//cette fonction charge le compte d'un client

function crediteCompteClient($msisdn, $price){
   
	include 'vars.php';
	$msisdn = str_replace("+","",$msisdn);
	
	$url = $urlChargeAmount;
	date_default_timezone_set('UTC');
	$thisDate = date("YmdHis");
	$data = '{"endUserId":"tel:+'.$msisdn.'","transactionOperationStatus":"Reserved","chargingInformation":{"description":"service de test","amount": '.$price.',"currency": "'.$cur.'"},"chargingMetaData":{"serviceID": "Test ServiceID #1","productID":"Test ProductID #1" },"referenceCode":"REF-'.$thisDate.'","clientCorrelator":"'.$thisDate.'"}';
	$headerAuth = 'Authorization: Bearer '.$token;
	$data_string = $data;  //json_encode($data);
	
	$ch = curl_init();	//  Initiate curl
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                              
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',$headerAuth));
	curl_setopt($ch, CURLOPT_HEADER, 0);  //TRUE to include the header in the output.
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	// Will return the response, if false it print the response
	curl_setopt($ch, CURLOPT_URL,$url);	// Set the url
	$result=curl_exec($ch);	// Execute
	$parsed_json = json_decode($result, true);
	$httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	$curl_errno = curl_errno($ch);
	if ($curl_errno > 0) {
		if (isset($_GET["debug"]) && $_GET["debug"]==1){
		   echo "<br/>cURL Error ($curl_errno): $curl_error\n<br/><br/>";
		}
	}
	
	$data_array[0]=$httpstatus;
	$data_array[1]=$result;
	curl_close($ch);	// Closing
	return $data_array;

}





function sendSMS($msisdn, $msg, $senderName){

	include 'vars.php';
	$msisdn = str_replace("+","",$msisdn);
	$callbackdata = date("Y-m-dTH:i:s"); //initialize callbackData with current datetime, in order to retrieve it in the SMS DR

	$url = $urlSendSMS;
	date_default_timezone_set('UTC');
	$thisDate = date("YmdH:is");
	$thisDateMsg = date("Y-m-dTH:i:s");
	$data = '{"address":["tel:+'.$msisdn.'"],"message":"'.$msg.'","senderName":"'.$senderName.'","callbackData":"'.$callbackdata.'"}';
	$headerAuth = 'Authorization: Bearer '.$token;
	$data_string = $data;  //json_encode($data);
	$ch = curl_init();	//  Initiate curl
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                              
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',$headerAuth));
	curl_setopt($ch, CURLOPT_HEADER, 0);  //TRUE to include the header in the output.
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	// Will return the response, if false it print the response
	curl_setopt($ch, CURLOPT_URL,$url);	// Set the url
	$result=curl_exec($ch);	// Execute
	$parsed_json = json_decode($result, true);
	$httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
	$curl_errno = curl_errno($ch);
	if ($curl_errno > 0) {
		if (isset($_GET["debug"]) && $_GET["debug"]==1){
		   echo "<br/>cURL Error ($curl_errno): $curl_error\n<br/><br/>";
		}
	}

	$data_array[0]=$httpstatus;
	$data_array[1]=$result;
	
	curl_close($ch);	// Closing
	return $data_array;
}


?>