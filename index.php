<?php

	include 'global.php';
	include 'orangeapi.php';
	include 'vars.php';
	
	/**
	* @param $value
	* @return mixed
	*/
	function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
		$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}

	if (isset($_POST["btnSubmitSMS"])) {
		$senderName = 'MyService';
		$callbackdata = date("Y-m-dTH:i:s"); //initialize callbackData with current datetime, in order to retrieve it in the SMS DR
		// send SMS with the message to the provided MSISDN
		$returnedSMS = sendSMS($_POST["address"], $_POST["msg"], $_POST["senderName"], $callbackdata, $_POST["token"]);
		
		// write the API response
		echo '<br/><br/><span STYLE="color: white; font-size: 10pt">';
		echo 'API Response is HTTP code </span><span STYLE="color: orange; font-size: 10pt"><b>' . $returnedSMS[0] . '</b></span>
		<span STYLE="color: white; font-size: 10pt"> and Response body is:</span>
		<span STYLE="color: orange; font-size: 10pt"><br/>'.$returnedSMS[1];
		echo '</span><hr/>';
	}
	
	if (isset($_POST["btnSubmitChargeAmount"])) {
		// chargeAmount the entered Amount to the provided MSISDN
		$returnedCharging = chargeAmountUser($_POST["address"], $_POST["amount"], 'XOF', $_POST["token"]);
		// write the API response
		echo '<br/><br/><span STYLE="color: white; font-size: 10pt">';
		echo 'API Response is HTTP code </span><span STYLE="color: orange; font-size: 10pt"><b>' . $returnedCharging[0] . '</b></span><span STYLE="color: white; font-size: 10pt"> and Response body is:</span><span STYLE="color: orange; font-size: 10pt"><br/>'.$returnedCharging[1];
		echo '</span><hr>';
	}	
	
?>


<html>
	<head>
		<title>Use Challenge Orange APIs</title>
		<link rel="stylesheet" type="text/css" media="all" href="index.css?v=0.1" />
	</head>
	<body>
		<br/>
		<table align="center" border="1" cellpadding="20">
		<tr><td valign="top" align="center">
		<h2>sendSMS - SMS API</h2><br/>
		<form method="POST" id="myform">
			To:&nbsp;<input type="text" name="address" id="address" maxlength="15" value="<?php if (isset($_POST["address"])){echo $_POST["address"];}else{ echo "+99900000xxxxxx";}?>" style="text-align: center;" required>
			&nbsp;enter international msisdn such as +99xxxxx<br/>
			with Token:&nbsp;<input type="text" name="token" id="token" maxlength="32" value="<?php if (isset($_POST["token"])){echo $_POST["token"];}?>" style="text-align: center;" required>
			<br/>
			with senderName:&nbsp;<input type="text" name="senderName" id="senderName" maxlength="11" value="<?php if (isset($_POST["senderName"])){echo $_POST["senderName"];}else{ echo "1234";}?>" style="text-align: center;" required>
			&nbsp;
			<br/><br/>
			Message:&nbsp;<br/>
			<input type="text" name="msg" id="msg" maxlength="160" value="<?php if (isset($_POST["msg"])){echo $_POST["msg"];}?>" style="text-align: center; width: 100%; padding: 2px; margin: 0px;" required>
			<br/><br/>
			<input type="Submit" name="btnSubmitSMS" id="btnSubmitSMS" value="send SMS" >
		</form>
		</td><td valign="top" align="center">
		<h2>chargeAmount - Payment API</h2><br/>
		<form method="POST" id="myform">
			To:&nbsp;<input type="text" name="address" id="address" maxlength="15" value="<?php if (isset($_POST["address"])){echo $_POST["address"];}else{ echo "+99900000xxxxxx";}?>" style="text-align: center;" required>
			&nbsp;enter international msisdn such as +99xxxxx<br/>
			with Token:&nbsp;<input type="text" name="token" id="token" maxlength="32" value="<?php if (isset($_POST["token"])){echo $_POST["token"];}?>" style="text-align: center;" required>
			&nbsp;(value from your self-service portal application)
			<br/><br/>
			charge:&nbsp;
			<input type="text" name="amount" id="amount" maxlength="10" value="10" style="text-align: center; width: 100px; padding: 2px; margin: 0px;" required>&nbsp;XOF
			<br/><br/>
			<input type="Submit" name="btnSubmitChargeAmount" id="btnSubmitChargeAmount" value="chargeAmount" >
		</form>
		</tr>
		</table>
	</body>
</html>

