<?php

// Send the headers
header('Content-type: text/html');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');


echo"<?xml version='1.0' encoding='utf-8' ?>
<html>
<body>
Bienvenue sur makiti, veuillez acceder aux section suivantes en utilisant les touches de votre clavier<br/>
<a href='affiche-offre.php'   accesskey='1'>Acceder aux offres</a><br/>
<a href='affiche-demande.php' accesskey='2'>Acceder aux demandes</a><br/>
<a href='affiche-produit.php' accesskey='3'>Acceder aux produits</a><br/>
<a href='affiche-info.php' accesskey='9'>Infos</a><br/>
</body>
</html>
";

?>