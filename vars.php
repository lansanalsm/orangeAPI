<?php
// Logs
$filelog = "logschallengetest.txt";
$logpaiement='logpaiement.txt';

//Orange APIs endpoints
$ip_apis = "https://api.sdp.orange.com";    
$urlSendSMS = $ip_apis . "/smsmessaging/v1/outbound/200/requests";
$urlChargeAmount = $ip_apis . "/payment/v1/200/transactions/amount";

//devise virtuelle
$cur='XOF';

//les clés
$token="votre token ici";
$consumerKey="votre cle publique ici";
$consumerSecret="Votre cle secrete ici";

?>
