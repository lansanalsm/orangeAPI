<?php

// Send the headers
header('Content-type: text/html');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');

$affiche_form=true;
$numero=null;

echo "<?xml version='1.0' encoding='utf-8' ?>";
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

if(isset($_GET['region']) and !empty($_GET['region'])){
	$id_region= (int) htmlspecialchars($_GET['region']);
	
	//n'affiche pas le reste
	$affiche_form=false;
	
	 try{ //connection a la base de donnee avec la methode pdo
		require('../../inc/connection_bd.php');
			  $requete2 = $bdd->prepare('SELECT id_ville,t_ville.nom as ville, t_region.nom as region FROM t_ville 
			                             INNER JOIN t_region ON t_region.id_region=t_ville.id_region
										 WHERE t_ville.id_region = ? ');
			  $requete2->execute(array($id_region));
			  if($requete2->rowCount()>0){
				$x=$requete2->fetch();
				echo"Choisissez l'une des ville de $x[region]<br/>";
				 while($tmp=$requete2->fetch()){
				  echo"<a href='affiche-produit.php?ville=$tmp[id_ville]'>$tmp[ville]</a><br/>";
				}//while
			  }else{
				  echo"Aucune ville pour cette region .<br/>";
				  echo"Verifier d'autres regions ";
			  }
				
		$requete2->closeCursor();
	    }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
 }//if region
 
 
 
 
 
 //affichage des produits par ville
 if(isset($_GET['ville']) and !empty($_GET['ville'])){
	$ville= (int) htmlspecialchars($_GET['ville']);

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
		 
		 $senderName='Alerte makiti';
		 $msg=null;
		
	 // echo"<br>avant try<br>";
	try{
			require('../../inc/connection_bd.php');
				$requete = $bdd->prepare(' SELECT 
				t_produit.id_produit,t_produit.id_sous_cat,t_produit.nom,prix,t_produit.description as desc_prod,
				t_sous_categorie_produit.nom as categorie,
				t_contenu_boutique.id_produit AS cont_id_produit,t_contenu_boutique.id_boutique as cont_id_boutique,
				t_boutique.nom as boutique,t_boutique.quartier AS quartier, t_boutique.id_ville AS bout_id_ville
				FROM t_produit
			    INNER JOIN  t_sous_categorie_produit ON t_produit.id_sous_cat = t_sous_categorie_produit.id_sous_cat
			    INNER JOIN  t_contenu_boutique ON t_contenu_boutique.id_produit=t_produit.id_produit
			    INNER JOIN  t_boutique ON t_boutique.id_boutique=t_contenu_boutique.id_boutique
			WHERE t_boutique.id_ville=:id_vil
			   ORDER BY t_produit.date_insertion DESC
			   LIMIT :debut,:fin
									    ');
			 
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
					  $msg.='Ref: #'.$tmp['id_produit'];
					  $msg.=' Nom:'.$tmp['nom'];
					  $msg.=' Cat:'.$tmp['categorie'];
					  $msg.=' Prix:'.$tmp['prix'];
					  $msg.=' Btique:'.$tmp['boutique'];
					  $msg.=' Quart:'.$tmp['quartier'];
					  
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
			   $msg="Aucun produit pour la ville selectionnee";
			   echo"$msg<br/>Merci d'avoir utilise makiti !";
		    }
		
	 }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
  
  }//if numero
  else{
	  echo"le num est bien vide !";
    }	
 
 }//if nombre
 else{
	 echo"Faites un choix svp. <br/>";
	 echo"<a href='affiche-produit.php?ville=$ville&amp;nombre=1'>Le dernier produit (200F)</a><br/>";
	 echo"<a href='affiche-produit.php?ville=$ville&amp;nombre=3'>Les 3 dernier produits (175F/prdt)</a><br/>";
	 echo"<a href='affiche-produit.php?ville=$ville&amp;nombre=5'>Les 5 dernier produits (150F/prdt)</a><br/>";
	 echo"<a href='affiche-produit.php?ville=$ville&amp;nombre=10'>Les 10 derniers produits (125F/prdt)</a><br/>";
 
 }
 

}//if ville



if(isset($_GET['ref']) && !empty($_GET['ref'])){
	$var=(int) htmlspecialchars($_GET['ref']);
	$affiche_form=false;
	
   try{  //recuperons tous les produits
		require('../../inc/connection_bd.php');
			 $requete = $bdd->prepare('SELECT id_produit,t_produit.id_sous_cat,t_produit.nom,prix,description,
 			                                t_sous_categorie_produit.nom as categorie 
			                         FROM t_produit
			                         INNER JOIN  t_sous_categorie_produit ON t_produit.id_sous_cat = t_sous_categorie_produit.id_sous_cat
									WHERE id_produit= :id_prod
									');
			$requette->execute(array('id_prod'=>$var));
		
		if($requete->rowCount()>0){
		while($tmp = $requete->fetch()){
		//recuperation de la boutique
		$selectBoutique=$bdd->prepare(' SELECT t_contenu_boutique.id_produit,t_boutique.id_boutique,t_boutique.nom
									    FROM t_contenu_boutique
									    INNER JOIN t_boutique ON t_boutique.id_boutique=t_contenu_boutique.id_boutique
									   WHERE t_contenu_boutique.id_produit = ? LIMIT 0,1 ');
		 $selectBoutique->execute(array($tmp['id_produit']));
		 $tmpBt=$selectBoutique->fetch();
		 echo"   Nom: $tmp[nom]<br/>
				Prix: $tmp[prix]<br/>
				Description: $tmp[description]<br/>
				Boutique  : $tmpBt[nom]<br/>
				Quartier: $tmpBt[quartier]
		";
		}//while
	
	  //on increment  le nombre de lecture de la news
	//  require_once('../../inc/fonctions.php');
	 // incrementeLectureAnnonce($_GET['ref']);
		
		
		}else{
			 echo"Une erreur s'est produite lors de l'affichage du produit<br/>";
		}
	   }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
			 	 	
}//if ref


if(isset($affiche_form) and $affiche_form==true){
echo'Choissez la ville dans laquelle vous vous trouvez pour afficher les offres de celle-ci<br/>';
 try{ //connection a la base de donnee avec la methode pdo
	require('../../inc/connection_bd.php');
	 //on lance la requette pour recuperer les information de la table departement
	 $requete = $bdd->query('SELECT * FROM t_region');
		while($tmp = $requete->fetch()) { 
		  echo"<a href='affiche-produit.php?region=$tmp[id_region]'>$tmp[nom]</a><br/>";
		  }
	//on suprime l'objet pdo
	$requete->closeCursor();
   }catch(Exception $e){ die('Erreur : '.$e->getMessage());}
}
echo '</body>';
echo '</html>';

?>