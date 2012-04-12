<?php

////////////////////////////////////////////////////////////////////////////////
///
///Coded By : Ayoub DARDORY (Apolikamixitos)
///Email : AYOUBUTO@Gmail.com
///Description : Une Classe PHP pour envoyer vos SMS via SMS.Menara.ma
///Follow me : http://www.twitter.com/Apolikamixitos
///GitHub: http://github.com/apolikamixitos
//
////////////////////////////////////////////////////////////////////////////////
require("menara.class.php");

$util = "NOMUSER";
$pass = "MDPUSER";

try {
    $Session = new Menara($util, $pass, array("NUMTEL1", "NUMTEL2"));

    echo "Votre solde est de : " . $Session->getSolde() . "<br />";
    echo "Numero d'expediteur : " . $Session->getExpediteur() . "<br />";
    echo "Numeros des destinataires : <br />";
    var_dump($Session->getDestinataires());

    //$Session->setDestinataires(array("NUMTEL3"));

    $message = "cava 3lik ana labas 3lia :D";
    $Session->EnvoieMenaraSMS($message);

    echo "Après l'envoie:<br />";
    echo "Votre nouveau solde est de : " . $Session->getSolde() . "<br />";
    
} catch (Exception $e) {
    
    echo $e->getMessage();
    
}
?>