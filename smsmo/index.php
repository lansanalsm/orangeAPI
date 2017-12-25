<?php

	 include '../global.php';
	 include '../vars.php';
	 include '../orangeapi.php';
	 
	$senderName = 'makiti';

	// ## RESPONSE HEADER ##
	$response_header="HTTP/1.1 200 OK";
	
	// ## RESPONSE BODY ##	
	$response_body="OK";
	
	// ### REQUEST BODY ###
	$request_body = file_get_contents('php://input');
	$json_request= json_decode($request_body, TRUE ); //convert JSON into array
	// echo $request_body ;
	//print_r($request_body);
	  //$msg="o:moto  q:sinanya";
	  
	 
	 $json_request['inboundSMSMessageNotification']['senderAddress'];//='99900000017501';
	 $destinationAddress = $json_request['inboundSMSMessageNotification']['inboundSMSMessage']['destinationAddress'];//='99900000017502';
	 $msg=$json_request['inboundSMSMessageNotification']['inboundSMSMessage']['message'];//='o:moto q:sinanya tp';
	
	  $msg=strtolower($msg);
	  
	//print_r($json_request);
	if (isset($json_request['inboundSMSMessageNotification'])){
	
	//{ "inboundSMSMessageNotification": { "inboundSMSMessage": { "senderAddress": "tel:+26772333490", "destinationAddress": "+26716968",     "message": "BEGIN Test éèà@ END", "dateTime": "2014-04-15T07:40:13Z" } } }
		
		$senderAddress=$json_request['inboundSMSMessageNotification']['inboundSMSMessage']['senderAddress'];//='99900000017501';
		$senderAddress = str_replace('tel:','',$senderAddress);	
		$destinationAddress = $json_request['inboundSMSMessageNotification']['inboundSMSMessage']['destinationAddress'];
		$msg=$json_request['inboundSMSMessageNotification']['inboundSMSMessage']['message'];

		//celui qui a envoye le message
		//$senderAddress=99900000017501;
		
		$log = WriteLog($filelog, 'sender adresse :'.$senderAddress);
		$log = WriteLog($filelog, 'destination adresse :'.$destinationAddress);
		  
		
	  if(ereg("^(d:|o:)+([a-z0-9]{2,})[ ]+([v:|q:]+)([a-z0-9])+[ ]*([tp|td]*)$",$msg,$res)){
	         
				$type=$res[1];
				$produit=$res[2];
				$localite=$res[3];
				$adresse=$res[4];
				$trie=$res[5];
				
				$log = WriteLog($filelog, 'Regex validé ');
				
			if(!empty($produit)&& $produit!=''){
			  if(!empty($adresse)&& $adresse!=''){
			    
				// offre demande ou produit
				if($type=='o:'){ $typeReq=1; }else 
				if($type=='d:'){  $typeReq=2;}else 
				if($type=='p:'){ //un produit
			       $typeReq=1; 
				  // echo"inconnu<br/>";
				}
				
				//localité
				if($localite=='q:'){ $champ=' t_annonce.quartier ';}else
				if($localite=='v:'){ $champ=' t_ville.nom '; }
				
				
			      
		      try{  
				require('../../inc/connection_bd.php');
				
				//on lance la requette pour recuperer les information de la table departement
				 $requete = $bdd->prepare('SELECT id_annonce,titre,texte,type_annonce,quartier,t_ville.nom AS ville
											FROM t_annonce
											INNER JOIN t_ville ON t_ville.id_ville=t_annonce.id_ville
											WHERE (type_annonce = :type AND 
													('.$champ.'LIKE :loc AND
													(titre LIKE :titre OR texte LIKE :texte))
													)
											LIMIT 0,1
											');
											
					$varReqAdr='%'.$adresse.'%';
					$varReqPrd='%'.$produit.'%';
					
				  $requete->bindValue(':type', $typeReq, PDO::PARAM_INT);
				  $requete->bindValue(':loc', $varReqAdr, PDO::PARAM_STR);
				  $requete->bindValue(':titre', $varReqPrd, PDO::PARAM_STR);
				  $requete->bindValue(':texte', $varReqPrd, PDO::PARAM_STR);
				  $requete->execute();
				
				
				
				 if($requete->rowCount()>0){
				   $tmp = $requete->fetch();
					
					//echo"ya  des donnée<br/>";
					$log = WriteLog($filelog, 'ya des données');
					
					$prix=((!empty($tmp['prix']) && $tmp['prix']!='')? $tmp['prix'] : 'N/A');
					$titre=$tmp['titre'];
					$ville=$tmp['ville'];
					$texte=$tmp['texte'];
					$quartier=$tmp['quartier'];
					
					if($typeReq==2){
					 $lien="http://makiti.mobitech-gn.com/demande.php?ref=$tmp[id_annonce]";
					 }else{
					  $lien="http://makiti.mobitech-gn.com/offre.php?ref=$tmp[id_annonce]";
					 }
					
					$msg='Annonce la plus pertinente : ';
					$msg=' Titre: '.$titre;
					$msg.=' Ville: '.$ville;
					$msg.=' Quartier: '.$quartier;
					$msg.=' Prix: '.$prix;
					$msg.='lien : '.$lien;
					
					//echo"$msg<br/>";
					$log = WriteLog($filelog, $msg);
					
					//envoi du message
					 $returnedSMS = sendSMS($senderAddress, $msg, $senderName);
					if($returnedSMS[0]==201){ 
						 $log = WriteLog($filelog, "SMS envoyé avec succes");
					 }else{
					  	 $log = WriteLog($filelog, "erreur denvoi du SMS reponse");
					  print_r($returnedSMS);
					  }

					 
				}else{
				  //   echo"Aucune annonce pour votre requette<br/>";
				   	$log = WriteLog($filelog, 'Aucune annonce pour votre requette');
				   
				     $msg="votre requette ne correspond à aucune annonce publier sur makiti pour le moment, merci et a très bientot sur makiti";
				     //Orange API
					$returnedSMS = sendSMS($senderAddress, $msg, $senderName);
					if($returnedSMS[0]==201){ 
						// echo"SMS envoyé avec succes <br/>";
						 	 $log = WriteLog($filelog, "SMS envoyé avec succes");
					 }else{
					     //echo"erreur d'envoi du SMS reponse<br/>";
					  	 $log = WriteLog($filelog, "erreur denvoi du SMS reponse");
					  print_r($returnedSMS);
					  }
				   
				   
				   }
				//on suprime l'objet pdo
				$requete->closeCursor();
			}catch(Exception $e){ die('Erreur : '.$e->getMessage());}
		
		   }else{ 
		     //echo"la variable adresse est vide dans le message <br/>";
			 $log = WriteLog($filelog, 'la variable adresse est vide dans le message');
		   }
		}else{
		 //  echo"la variable produit est vide !<br/>";
		   $log = WriteLog($filelog, 'la variable produit est vide dans le message');
		 }
			  
		}//fin regex
		else{
		 // echo"le message ne repond pas au format donné !<br/>";
		 $log = WriteLog($filelog, 'le message ne repond pas au format donné');
		  }
		
		
		//on enregistre dans le log
		$logtxt = 'SMS MO received from ' . $senderAddress . ' with message=[' . $msg . '] from [' . $destinationAddress . '] SMS short code';
		$log = WriteLog($filelog, $logtxt);
		//le message a ete recu maintenant regex
		
		
	} else {
	 //  echo"ce n'est pas un message qui est recu<br>";
	   $log = WriteLog($filelog, 'ce ne pas un vrai message qui est recu');
	  // header('location:index.php');
	}


?>