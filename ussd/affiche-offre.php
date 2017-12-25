<?php

// Send the headers
header('Content-type: text/html');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');
echo '<?xml version="1.0" encoding="utf-8"?>';

if (isset($_GET["response"])){
	$name = $_GET["response"];
}
$affiche_form=true;

echo '<html>';
echo '<head>';
echo '  <meta name="nav" content="end"/>';
echo '</head>';
echo '<body>';

//$_GET['region']=5;
//$_GET['ville']=7;
//$_GET['ref']=28;

require_once('../../inc/fonctions.php');
$numero=getNumeroFromUSSD();
//$numero=99900000017501;
$type_annonce=1; //les offres

//1er menu
if(isset($_GET['choix']) && !empty($_GET['choix'])){
	$choix = (int) htmlspecialchars($_GET['choix']);
	$affiche_form=false;
	
	switch($choix){
		case 1:{ //affichage par localité
			try{ require('../../inc/connection_bd.php');
				 $requete = $bdd->query('SELECT * FROM t_region');
				  echo"Choisissez une localité SVP.<br/>";
					while($tmp = $requete->fetch()) { 
					  echo"<a href='affiche-offre.php?region=$tmp[id_region]'>$tmp[nom]</a><br/>";
					  }
				$requete->closeCursor();
			   }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
		 
         break;
		 }
	    case 2:{ //affichage par categorie
			
			try{ require('../../inc/connection_bd.php');
				 $requete = $bdd->query('SELECT * FROM t_categorie_produit');
				  echo"Choisissez une categorie SVP.<br/>";
					while($tmp = $requete->fetch()) { 
					  echo"<a href='affiche-offre.php?categorie=$tmp[id_cat]'>$tmp[nom]</a><br/>";
					  }
				$requete->closeCursor();
			   }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
		 break;	
		   }//
	  default:{ $affiche_form=true;}
	}//switch	
}//choix

//menu 1-> 1
//affichage des villes par region
if(isset($_GET['region']) and !empty($_GET['region'])){
	$id_region= (int) htmlspecialchars($_GET['region']);
	//n'affiche pas le reste
	$affiche_form=false;
	
	 try{ //connection a la base de donnée avec la methode pdo
		require('../../inc/connection_bd.php');
			  $requete2 = $bdd->prepare('SELECT id_ville,t_ville.nom as ville, t_region.nom as region FROM t_ville 
			                             INNER JOIN t_region ON t_region.id_region=t_ville.id_region
										 WHERE t_ville.id_region = ? ');
			  $requete2->execute(array($id_region));
			  if($requete2->rowCount()>0){
				$x=$requete2->fetch();
				echo"Choisissez l'une des ville de $x[region]<br/>";
				 while($tmp=$requete2->fetch()){
				  echo"<a href='affiche-offre.php?ville=$tmp[id_ville]'>$tmp[ville]</a><br/>";
				}//while
			  }else{
				  echo"Aucune ville pour cette region .<br/>";
				  echo"Verifier d'autres regions ";
			  }
				
		$requete2->closeCursor();
	    }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
 }//if region
 
 //menu 1 -> 2
 //par categorie
 if(isset($_GET['categorie']) and !empty($_GET['categorie'])){
	$id_cat= (int) htmlspecialchars($_GET['categorie']);
	//n'affiche pas le reste
	$affiche_form=false;
	
	 try{ 
		require('../../inc/connection_bd.php');
			  $requete2 = $bdd->prepare('SELECT id_sous_cat,t_sous_categorie_produit.nom as sous_categorie,t_categorie_produit.nom AS categorie
										  FROM t_sous_categorie_produit 
			                             INNER JOIN t_categorie_produit ON t_sous_categorie_produit.id_cat=t_categorie_produit.id_cat
										 WHERE t_sous_categorie_produit.id_cat = ? ');
			  $requete2->execute(array($id_cat));
			  if($requete2->rowCount()>0){
				$x=$requete2->fetch();
				echo"Choisissez l'une des categories de $x[categorie]<br/>";
				 while($tmp=$requete2->fetch()){
				  echo"<a href='affiche-offre.php?sous_cat=$tmp[id_sous_cat]'>$tmp[sous_categorie]</a><br/>";
				}//while
			  }else{
				  echo"Aucune Sous categorie pour cette categorie .<br/>";
				  echo"Verifier d'autres categorie ";
			  }
				
		$requete2->closeCursor();
	    }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
 }//if categorie
 
 
 //MENU 1 -> 1 -> 1
 
 //par ville
if(isset($_GET['ville']) and !empty($_GET['ville'])){
	$ville= (int) htmlspecialchars($_GET['ville']);
    //n'affiche pas le reste
	 $affiche_form=false;
	
  if(isset($_GET['nombre']) && !empty($_GET['nombre'])){
	$nombre= (int) htmlspecialchars($_GET['nombre']);
	//n'affiche pas le reste
	$affiche_form=false;
	
	$debut=0;
	$fin=(($nombre <=10)?$nombre:10);
	$coutSMS=0;
	switch($nombre){
		case  3: $coutSMS=175; break;
		case  5: $coutSMS=150; break;
		case 10: $coutSMS=125; break;
		default: $coutSMS=200;
	}
	
	$total=$coutSMS*$nombre;
	
	if(isset($numero) && !empty($numero)){
		
		//inclusion des api
		//Orange API
		
		 
		 include ('../global.php');
		 include ('../orangeapi.php');
		 include ('../vars.php');
		 
		 $senderName='Makiti';
		 $msg=null;
		
	 // echo"<br>avant try<br>";
	try{
			require('../../inc/connection_bd.php');
				$requete = $bdd->prepare('SELECT id_annonce,titre,prix, quantite,t_annonce.id_sous_cat AS id_cat,t_sous_categorie_produit.nom AS categorie,t_annonce.quartier AS quart, t_ville.nom AS ville, t_unite_produit.nom AS unite
											FROM t_annonce
											INNER JOIN t_sous_categorie_produit ON t_annonce.id_sous_cat=t_sous_categorie_produit.id_sous_cat
											INNER JOIN t_ville ON t_annonce.id_ville=t_ville.id_ville
											LEFT JOIN t_unite_produit ON t_unite_produit.id_unite=t_annonce.id_unite
											WHERE type_annonce = :type 
												  AND t_annonce.id_ville = :id_vil
											ORDER BY date_insertion DESC
											LIMIT :debut,:fin
									    ');
			  $requete->bindValue(':type', $type_annonce, PDO::PARAM_INT);
			  $requete->bindValue(':id_vil', $_GET['ville'], PDO::PARAM_INT);
			  $requete->bindValue(':debut', $debut, PDO::PARAM_INT);
		      $requete->bindValue(':fin', $fin, PDO::PARAM_INT);
			  $requete->execute();
			 
			 //le nombre de reponse obtenu
			 $nb=$requete->rowCount();
			  
			 if($nb>0 ){
				 
				 $total=(($nb<$nombre)?$nb * $coutSMS : $total);
				 
				  $reponseChargement = chargeAmountUser($numero, $total);
				   if($reponseChargement[0]==201){
					  echo"Traitement en cours....<br/>";
					  echo"Vous allez recevoir dans quelques instant des SMS";

					 while($tmp = $requete->fetch()) {
						
						//pour l'unité et le prix:
					   $prix=null;
					   if($tmp['prix']==null or $tmp['prix']==''){
						   $tmp['unite']=null;
						   } //pas d'unite san prix
						else{ 
							 if($tmp['unite']==null or $tmp['unite']==''){
								$prix = $tmp['prix']." GNF"; 
							}else{
								$prix = $tmp['prix']." GNF / ".$tmp['unite'];
							 }
						}
						
						 //pour l'unité et le prix:
			   $prix=null;
			   if($tmp['prix']==null or $tmp['prix']==''){
				  $tmp['unite']=null;
				  $prix="N/A";
				  }
				  else{ 
					 if($tmp['unite']==null or $tmp['unite']==''){
						$prix = $tmp['prix']." GNF"; 
					}else{
						$prix = $tmp['prix']." GNF / ".$tmp['unite'];
					 }
				}
			
				//gestion de l'email et du telephone
				$telephone="N/A";
				if($tmp['tel_ann']!=null OR $tmp['tel_ann']!='' ){
					$telephone= $tmp['tel_ann'];
				 }else if($tmp['tel_ins']!=null OR $tmp['tel_ins']!='' ){
					$telephone= $tmp['tel_ins'];
				  }
						
					  $msg.='Ref: #'.$tmp['id_annonce'];
					  $msg.=' Titre:'.$tmp['titre'];
					  $msg.=' Cat:'.$tmp['categorie'];
					  $msg.=' Prix:'.$prix;
					  $msg.=' Quart:'.$tmp['quart'];
					  $msg.=' Tel:'.$telephone;
					  
					  $returnedSMS = sendSMS($numero, $msg, $senderName);
						if($returnedSMS[0]==201){ 
						   //on incremente le nomre de sms envoyes
						}else{
						  //echo"erreur d'envoi du SMS d'alerte<br/>";
						  //print_r($returnedSMS);
						 }
					}//while
			    }else{
					   echo"Impossible de faire le prelevement sur votre compte<br/>";
					   echo"Assurez vous d'avoir du credit dans votre compte et autorise le prelevement du montant requis par makiti";
			    }
		
		    }else{
			   $msg="Aucune offre pour la ville selectionnee";
			   echo"$msg<br/>Merci d'avoir utilise makiti !";
		    }
		
	 }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
  
  }//if numero
  else{ echo"le num est bien vide !"; }	
  
}else if(isset($_GET['change']) && !empty($_GET['change'])){
     //section abonnement 
	 if(isset($_GET['abonnement']) && !empty($_GET['abonnement'])){
		$abonnement = (int) htmlspecialchars($_GET['abonnement']);
		
		if(isset($_GET['confirm'])&& $_GET['confirm']==true){
			    //traitement de la requette
				 
				 try{ require('../../inc/connection_bd.php');
				      
					  //les api orange
					  include ('../global.php');
					  include ('../orangeapi.php');
					  include ('../vars.php');
					 
					  $senderName='Makiti';
					  $msg=null;
				    
					//verificatio du num
					$req = $bdd->prepare('SELECT * FROM t_abonne_ussd WHERE numero = ?');
				    $req->execute(array($numero));
					
					if($req->rowCount()>0){
						$tmp=$req->fetch();
						echo"Vous etes deja abonne <br/>";
						echo"Votre abonnement exiperar le $tmp[date_expiration]<br/>";
						 
					 }else{ //ce num ne pas inscrit
					     
						 $cout=500; $interval= ' 1 DAY'; $m=' 24h a partir de maintenant';
							switch($abonnement){
								case  2: $cout = 2500;   $interval= ' 7 DAY';    $m=' 7 jours a partir de maintenant';break;
								case  3: $cout = 10000;  $interval= ' 1 MONTH';  $m=' 1 mois a partir de maintenant'; break;
								case  4: $cout = 100000; $interval= ' 1 YEAR';   $m=' 1 an a partir de maintenant'; break;
								default: $cout = 500;    $interval= ' 1 DAY';    $m=' 24h a partir de maintenant';
							}
					    
						 $reponseChargement = chargeAmountUser($numero, $cout);
				         if($reponseChargement[0]==201){
					     
						 echo"Traitement en cours....<br/>";
					      echo"Vous allez recevoir un SMS dans quelques instants<br/>";
						  
						  $sous_cat=((isset($sous_cat))?$sous_cat:0);
						  $ville=((isset($ville))?$ville:0);
						 
						  $req=$bdd->prepare('INSERT IGNORE INTO t_abonne_ussd 
								(id_abonne,id_categorie,id_ville,numero,etat_activation,date_abonnement,date_expiration,type_abonne,sms_envoye)
								 VALUES ("",:id_sous_cat,:id_ville,:numero,1,NOW(),DATE_ADD(NOW(), INTERVAL '.$interval.'),:type_abonne,0)
										   ');
						  $req->execute(array('id_sous_cat'=>$sous_cat,'id_ville'=>$ville,'numero'=>$numero,'type_abonne'=>$type_annonce));
						 
						  //envoi de la notification
						  $msg='Abonnement effectuee avec succes';
						  $msg.='il expirera dans '.$m;
						  $returnedSMS = sendSMS($numero, $msg, $senderName);
						   if($returnedSMS[0]==201){ 
						    //on incremente le nomre de sms envoyes
							}else{
							  //echo"erreur d'envoi du SMS d'alerte<br/>";
							  //print_r($returnedSMS);
							 }
						
						
						}else{
						   echo"Impossible de faire le prelevement sur votre compte<br/>";
						   echo"Assurez vous d'avoir du credit dans votre compte et autorise le prelevement du montant requis pour votre abonnement";
						 }
						
				   }//else
				  

				   $req->closeCursor();
			       }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
				
			}else{ //il na pas confirmer dbr
				 
			   echo"SVP confirmer votre abonnement a ce service <br/>";
			   echo"<a href='affiche-offre.php?ville=$ville&amp;change=1&amp;abonnement=$abonnement&amp;confirm=true'>Confirmer</a><br/>";
			   echo"<a href='affiche-offre.php?ville=$ville'>Annuler</a><br/>";
				
	      }//else confirm
	 
	}else{ //le var abonnement n'existe pas
		
		 echo"Faite un choix svp.<br/>";
		 echo"<a href='affiche-offre.php?ville=$ville&amp;change=1&amp;abonnement=1'>Abonnement journalier(500F)</a><br/>";
		 echo"<a href='affiche-offre.php?ville=$ville&amp;change=1&amp;abonnement=2'>Abonnement Somaire(2500F)</a><br/>";
		 echo"<a href='affiche-offre.php?ville=$ville&amp;change=1&amp;abonnement=3'>Abonnement Mensuel(10000F)</a><br/>";
		 echo"<a href='affiche-offre.php?ville=$ville&amp;change=1&amp;abonnement=4'>Abonnement Annuel(100000F)</a><br/>";
    }


	}//fin change
 else{
	 //on affiche les choix de nombre
	
	 echo"Faites un choix SVP.<br/>";
	 echo"<a href='affiche-offre.php?ville=$ville&amp;nombre=1'>La derniere offre (200F)</a><br/>";
	 echo"<a href='affiche-offre.php?ville=$ville&amp;nombre=3'>Les 3 dernieres offres (175F/prdt)</a><br/>";
	 echo"<a href='affiche-offre.php?ville=$ville&amp;nombre=5'>Les 5 dernieres offres (150F/prdt)</a><br/>";
	 echo"<a href='affiche-offre.php?ville=$ville&amp;nombre=10'>Les 10 derniers offres (125F/prdt)</a><br/>";
	 echo"---------------<br/>";
	 echo"<a href='affiche-offre.php?ville=$ville&amp;change=1'>Abonnement</a><br/>";
	 // echo"votre numero est $numero";
	 
 }
}// if ville


 //MENU 1 -> 1 -> 2
 
 //par sous categorie
if(isset($_GET['sous_cat']) and !empty($_GET['sous_cat'])){
	$sous_cat= (int) htmlspecialchars($_GET['sous_cat']);
	//n'affiche pas le reste
	$affiche_form=false;
	
  if(isset($_GET['nombre']) && !empty($_GET['nombre'])){
	
	$nombre= (int) htmlspecialchars($_GET['nombre']);
	$debut=0;
	$fin=(($nombre <=10)?$nombre:10);
	$coutSMS=0;
	switch($nombre){
		case  3: $coutSMS=175; break;
		case  5: $coutSMS=150; break;
		case 10: $coutSMS=125; break;
		default: $coutSMS=200;
	}
	
	$total=$coutSMS*$nombre;
	
	if(isset($numero) && !empty($numero)){
		
		//inclusion des api
		//Orange API
		
		 
		 include ('../global.php');
		 include ('../orangeapi.php');
		 include ('../vars.php');
		 
		 $senderName='Makiti';
		 $msg=null;
		
	 // echo"<br>avant try<br>";
	try{
			require('../../inc/connection_bd.php');
				$requete = $bdd->prepare('SELECT id_annonce,titre,prix, quantite,t_annonce.id_sous_cat AS id_cat,t_sous_categorie_produit.nom AS categorie,t_annonce.quartier AS quart, t_ville.nom AS ville, t_unite_produit.nom AS unite
											FROM t_annonce
											INNER JOIN t_sous_categorie_produit ON t_annonce.id_sous_cat=t_sous_categorie_produit.id_sous_cat
											INNER JOIN t_ville ON t_annonce.id_ville=t_ville.id_ville
											LEFT JOIN t_unite_produit ON t_unite_produit.id_unite=t_annonce.id_unite
											WHERE type_annonce = :type 
												  AND t_annonce.id_sous_cat = :id_sous_cat
											ORDER BY date_insertion DESC
											LIMIT :debut,:fin
									    ');
			  $requete->bindValue(':type', $type_annonce, PDO::PARAM_INT);
			  $requete->bindValue(':id_sous_cat', $sous_cat, PDO::PARAM_INT);
			  $requete->bindValue(':debut', $debut, PDO::PARAM_INT);
		      $requete->bindValue(':fin', $fin, PDO::PARAM_INT);
			  $requete->execute();
			 
			 //le nombre de reponse obtenu
			 $nb=$requete->rowCount();
			  
			 if($nb>0 ){
				 
				 $total=(($nb<$nombre)?$nb * $coutSMS : $total);
				 
				  $reponseChargement = chargeAmountUser($numero, $total);
				   if($reponseChargement[0]==201){
					  echo"Traitement en cours....<br/>";
					  echo"Vous allez recevoir dans quelques instant des SMS";

					 while($tmp = $requete->fetch()) {
						
						//pour l'unité et le prix:
					   $prix=null;
					   if($tmp['prix']==null or $tmp['prix']==''){
						   $tmp['unite']=null;
						   } //pas d'unite san prix
						else{ 
							 if($tmp['unite']==null or $tmp['unite']==''){
								$prix = $tmp['prix']." GNF"; 
							}else{
								$prix = $tmp['prix']." GNF / ".$tmp['unite'];
							 }
						}
						
						 //pour l'unité et le prix:
			   $prix=null;
			   if($tmp['prix']==null or $tmp['prix']==''){
				  $tmp['unite']=null;
				  $prix="N/A";
				  }
				  else{ 
					 if($tmp['unite']==null or $tmp['unite']==''){
						$prix = $tmp['prix']." GNF"; 
					}else{
						$prix = $tmp['prix']." GNF / ".$tmp['unite'];
					 }
				}
			
				//gestion de l'email et du telephone
				$telephone="N/A";
				if($tmp['tel_ann']!=null OR $tmp['tel_ann']!='' ){
					$telephone= $tmp['tel_ann'];
				 }else if($tmp['tel_ins']!=null OR $tmp['tel_ins']!='' ){
					$telephone= $tmp['tel_ins'];
				  }
						
					  $msg.='Ref: #'.$tmp['id_annonce'];
					  $msg.=' Titre:'.$tmp['titre'];
					  $msg.=' Cat:'.$tmp['categorie'];
					  $msg.=' Prix:'.$prix;
					  $msg.=' Quart:'.$tmp['quart'];
					  $msg.=' Tel:'.$telephone;
					  
					  $returnedSMS = sendSMS($numero, $msg, $senderName);
						if($returnedSMS[0]==201){ 
						   //on incremente le nomre de sms envoyes
						}else{
						  //echo"erreur d'envoi du SMS d'alerte<br/>";
						  //print_r($returnedSMS);
						 }
					}//while
			    }else{
					   echo"Impossible de faire le prelevement sur votre compte<br/>";
					   echo"Assurez vous d'avoir du credit dans votre compte et autorise le prelevement du montant requis par makiti";
			    }
		
		    }else{
			   $msg="Aucune offre pour la categorie selectionnee";
			   echo"$msg<br/>Merci d'avoir utilise makiti !";
		    }
		
	 }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
  
  }//if numero
  else{ echo"le num est bien vide !"; }	
  
}else if(isset($_GET['change']) && !empty($_GET['change'])){
     //section abonnement 
	 if(isset($_GET['abonnement']) && !empty($_GET['abonnement'])){
		$abonnement = (int) htmlspecialchars($_GET['abonnement']);
		
		if(isset($_GET['confirm'])&& $_GET['confirm']==true){
			   
			  //traitement de la requette
				 
				 try{ require('../../inc/connection_bd.php');
				      
					  //les api orange
					  include ('../global.php');
					  include ('../orangeapi.php');
					  include ('../vars.php');
					 
					  $senderName='Makiti';
					  $msg=null;
				    
					//verificatio du num
					$req = $bdd->prepare('SELECT * FROM t_abonne_ussd WHERE numero = ?');
				    $req->execute(array($numero));
					
					if($req->rowCount()>0){
						$tmp=$req->fetch();
						echo"Vous etes deja abonne <br/>";
						echo"Votre abonnement exiperar le $tmp[date_expiration]<br/>";
						 
					 }else{ //ce num ne pas inscrit
					     
						 $cout=500; $interval= ' 1 DAY'; $m=' 24h a partir de maintenant';
							switch($abonnement){
								case  2: $cout = 2500;   $interval= ' 7 DAY';    $m=' 7 jours a partir de maintenant';break;
								case  3: $cout = 10000;  $interval= ' 1 MONTH';  $m=' 1 mois a partir de maintenant'; break;
								case  4: $cout = 100000; $interval= ' 1 YEAR';   $m=' 1 an a partir de maintenant'; break;
								default: $cout = 500;    $interval= ' 1 DAY';    $m=' 24h a partir de maintenant';
							}
					    
						 $reponseChargement = chargeAmountUser($numero, $cout);
				         if($reponseChargement[0]==201){
					     
						 echo"Traitement en cours....<br/>";
					      echo"Vous allez recevoir un SMS dans quelques instants<br/>";
						  
						  $sous_cat=((isset($sous_cat))?$sous_cat:0);
						  $ville=((isset($ville))?$ville:0);
						 
						  $req=$bdd->prepare('INSERT IGNORE INTO t_abonne_ussd 
								(id_abonne,id_categorie,id_ville,numero,etat_activation,date_abonnement,date_expiration,type_abonne,sms_envoye)
								 VALUES ("",:id_sous_cat,:id_ville,:numero,1,NOW(),DATE_ADD(NOW(), INTERVAL '.$interval.'),:type_abonne,0)
										   ');
						  $req->execute(array('id_sous_cat'=>$sous_cat,'id_ville'=>$ville,'numero'=>$numero,'type_abonne'=>$type_annonce));
						 
						  //envoi de la notification
						  $msg='Abonnement effectuee avec succes';
						  $msg.='il expirera dans '.$m;
						  $returnedSMS = sendSMS($numero, $msg, $senderName);
						   if($returnedSMS[0]==201){ 
						    //on incremente le nomre de sms envoyes
							}else{
							  //echo"erreur d'envoi du SMS d'alerte<br/>";
							  //print_r($returnedSMS);
							 }
						
						
						}else{
						   echo"Impossible de faire le prelevement sur votre compte<br/>";
						   echo"Assurez vous d'avoir du credit dans votre compte et autorise le prelevement du montant requis pour votre abonnement";
						 }
						
				   }//else
				  

				   $req->closeCursor();
			       }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
			
			
			
			
			
			
			}else{ //il na pas confirmer dbr
				 
				 switch($abonnement){
					case 1:{
						 echo"Confirmer votre abonnement aux alertes de offres journalieres.<br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=1&amp;confirm=true'>Confirmer</a><br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat'>Annuler</a><br/>";
						break;
					}
					case 2:{
						 echo"Confirmer votre abonnement aux alertes de offres Sommaires.<br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=2&amp;confirm=true'>Confirmer</a><br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat'>Annuler</a><br/>";
						break;
					}
					case 3:{
						 echo"Confirmer votre abonnement aux alertes de offres Mensuelles.<br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=3&amp;confirm=true'>Confirmer</a><br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat'>Annuler</a><br/>";
						break;
					}
					case 4:{
						 echo"Confirmer votre abonnement aux alertes de offres Annuelles.<br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=4&amp;confirm=true'>Confirmer</a><br/>";
						 echo"<a href='affiche-offre.php?sous_cat=$sous_cat'>Annuler</a><br/>";
						break;
					}
				}//switech
	      }//else confirm
	 
	}else{ //le var abonnement n'existe pas
		
		 echo"Faite un choix svp.<br/>";
		 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=1'>Abonnement journalier(500F)</a><br/>";
		 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=2'>Abonnement Somaire(2500F)</a><br/>";
		 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=3'>Abonnement Mensuel(10000F)</a><br/>";
		 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1&amp;abonnement=4'>Abonnement Annuel(100000F)</a><br/>";
    }


	}//fin change
 else{
	 //on affiche les choix de nombre
	
	 echo"Faites un choix SVP.<br/>";
	 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;nombre=1'>La derniere offre (200F)</a><br/>";
	 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;nombre=3'>Les 3 dernieres offres (175F/prdt)</a><br/>";
	 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;nombre=5'>Les 5 dernieres offres (150F/prdt)</a><br/>";
	 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;nombre=10'>Les 10 derniers offres (125F/prdt)</a><br/>";
	 echo"---------------<br/>";
	 echo"<a href='affiche-offre.php?sous_cat=$sous_cat&amp;change=1'>Abonnement</a><br/>";
	 // echo"votre numero est $numero";
	 
 }
}// if sous categorie

//affichage du menu
if(isset($affiche_form) and $affiche_form==true){
	echo'Choissez la ville dans laquelle vous vous trouvez pour afficher les offres de celle-ci<br/>';
	echo"<a href='affiche-offre.php?choix=1'>Afficher par localite</a><br/>";
	echo"<a href='affiche-offre.php?choix=2'>Afficher par Categorie</a><br/>";
	//echo"<a href='affiche-offre.php?choix=3'>Abonnement</a><br/>";

}
echo '</body>';
echo '</html>';

?>