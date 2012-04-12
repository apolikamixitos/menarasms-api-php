<?php

class Menara {

    private $cookie;
    private $Solde;
    private $Expediteur;
    private $Destinataires;

    //Contructeur génère une exception en cas d'échec

    function Menara($user, $pass, $dest = null) {
        $url = "https://sso.menara.ma/Login/login?service=http%3A%2F%2Fsms.menara.ma%2Ffreesms%2F";
        $f = fopen(md5($user) . "-Menara.txt", "w+");
        fclose($f);
        $this->cookie = md5($user) . "-Menara.txt";

        $h = curl_init();
        curl_setopt($h, CURLOPT_URL, $url);
        curl_setopt($h, CURLOPT_AUTOREFERER, true);
        curl_setopt($h, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8 (.NET CLR 3.5.30729)");
        curl_setopt($h, CURLOPT_HEADER, 1);
        curl_setopt($h, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($h, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($h, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($h, CURLOPT_COOKIEJAR, $this->cookie);
        curl_setopt($h, CURLOPT_COOKIEFILE, $this->cookie);
        $re = curl_exec($h);
        preg_match_all('/<input type="hidden" name="lt" value="([^`]*?)" \/>/', $re, $f);
        if (isset($f[1][0]))
            $lt = $f[1][0];
        else
            $lt = "";

        $data = "username=" . $user . "&password=" . $pass . "&lt=" . $lt . "&_eventId=submit&submit=";
        curl_setopt($h, CURLOPT_POST, true);
        curl_setopt($h, CURLOPT_POSTFIELDS, $data);
        $re = curl_exec($h);
        curl_close($h);

        preg_match_all('/class="contour-conect">([^`]*?)SMS/', $re, $f);
        if (isset($f[1][0])) {
            preg_match_all('/<input name="expediteur" type="text" value="([^`]*?)" class="sms-formenvoi"/', $re, $r);
            $this->Expediteur = trim($r[1][0]);
            $this->Solde = trim($f[1][0]);
            $this->Destinataires = array();
            if ($dest != null && is_array($dest))
                foreach ($dest as $sDest)
                    $this->Destinataires[] = $this->VerifIAMNumber($sDest);
        }
        else
            throw new Exception("Impossible de se connecter. Verifiez le login SVP");
    }

    //Retourne le solde restant en cas de succes
    //Exception en cas d'échec
    function EnvoieMenaraSMS($msg) {
        $url = "http://sms.menara.ma/freesms/envoyersms";
        $IndexDest = 0;
        foreach ($this->Destinataires as $Res) {
            $pre = substr($Res, 2, 2); //Prefixe (61,12...)
            $dest = substr($Res, 4);   //Le numero sans le prefixe
            $data = "expediteur=" . $this->getExpediteur() . "&aa=06" . $pre . "&numtel=" . $dest . "&listeNum=06" . $pre . "" . $dest . "%3B&contenu=" . $msg . "&theDate2=&heure=00&minute=00&submit=";
            $h = curl_init();
            curl_setopt($h, CURLOPT_URL, $url);
            curl_setopt($h, CURLOPT_AUTOREFERER, true);
            curl_setopt($h, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8 (.NET CLR 3.5.30729)");
            curl_setopt($h, CURLOPT_HEADER, 1);
            curl_setopt($h, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($h, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($h, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($h, CURLOPT_COOKIEJAR, $this->cookie);
            curl_setopt($h, CURLOPT_COOKIEFILE, $this->cookie);
            curl_setopt($h, CURLOPT_POSTFIELDS, $data);
            curl_setopt($h, CURLOPT_POST, true);
            $re = curl_exec($h);
            preg_match_all('/<span class="sms-texteresult">([^`]*?)<\/span>/', $re, $f);
            if (isset($f[1][0]) && strpos($f[1][0], "Votre envoi a bien")) {
                $this->Solde--;
                $IndexDest++;
            }else
                throw new Exception("Le message n'a pas abouti à tous les destinataires ! From [" . $IndexDest . "]: " . $Res);
        }
        return $this->Solde;
    }

    //Retourne le numero valide en cas de succes
    //Exception en cas d'échec
    protected function VerifIAMNumber($des) {

        if ($des[0] . $des[1] == "06" && strlen($des) == 10 && is_numeric($des)) {
            $pre = "";
            $pref = array("10", "11", "13", "15", "16", "18", "41", "42", "48", "50", "51", "52", "53", "54", "55", "58", "59", "61", "62", "66", "67", "68", "70", "71", "72", "73", "76", "77", "78");

            foreach ($pref as $spref) {
                if ($spref == $des[2] . $des[3]) {
                    $dest = "";
                    for ($i = 4; $i < 10; $i++)
                        $dest = $dest . $des[$i];
                    $Res = array();
                    return "06" . $spref . $dest;
                }
            }
        }
        throw new Exception("Numero de telephone invalide ! {" . $des . "}"); //S'il n'y aura pas de return ==> Exception TROLOLOL
    }

    #Getters && Setters

    function getSolde() {
        return $this->Solde;
    }

    public function setSolde($Solde) {
        $this->Solde = $Solde;
    }

    function getExpediteur() {
        return $this->Expediteur;
    }

    public function setExpediteur($Expediteur) {
        $this->Expediteur = $Expediteur;
    }

    public function getCookie() {
        return $this->cookie;
    }

    public function setCookie($cookie) {
        $this->cookie = $cookie;
    }

    public function getDestinataires() {
        return $this->Destinataires;
    }

    public function setDestinataires($Destinataires) {
        if (is_array($Destinataires))
            foreach ($Destinataires as $sDest)
                $this->Destinataires[] = $this->VerifIAMNumber($sDest);
    }

}

?>