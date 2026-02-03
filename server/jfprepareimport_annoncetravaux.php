<?php
require_once 'gdt/cldbgoeland.php';
require_once '/data/dataweb/GoelandWeb/goeland/jaxforms/cncljaxforms.php';
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$bcontinue = true;
$messageErreur = '';
$preImportData = null;
$dbgo = new DBGoeland();

if (isset($_GET['jsondata'])) {
    $jsonData = $_GET['jsondata'];
} else {
    $bcontinue = false;
    $messageErreur = 'GET jsondata manquant';
}

if ($bcontinue) {
    $oData = json_decode($jsonData, false);
    $idJFDemande = $oData->idDemande;
    if (preg_match('/^[a-zA-Z0-9-]{1,50}$/', $idJFDemande)) {
        $sSql = "cn_affaire_idjaxforms_get_idaffaire '$idJFDemande'";
        $dbgo->queryRetInt($sSql);
        $idAffaire = $dbgo->resInt;
        if ($idAffaire > 0) {
            $bcontinue = false;
            $messageErreur = "formulaire $idJFDemande déjà importé, id affaire: $idAffaire";
        }
    } else {
        $bcontinue = false;
        $messageErreur = 'id jaxforms demande invalide';
    }
}

if ($bcontinue) {
    $oData->idsBatimentGo = '';
    $nbrBatimentGo = 0;
    $oData->idsParcelleGo = '';
    $nbrParcelleGo = 0;

    //localisation -> rue adresse
    if (isset($oData->localisationRue)) {
        $nomRue = $oData->localisationRue;
        //Tout ce qui suit pour remplacer l'éventuel type de rue abrégé par le type de rue complet
        if (substr($nomRue, 0, 3) == 'Av.') {
            $nomRue = 'Avenue ' . substr($nomRue, 3);
        } elseif (substr($nomRue, 0, 3) == 'Ch.') {
            $nomRue = 'Chemin ' . substr($nomRue, 3);
        } elseif (substr($nomRue, 0, 3) == 'Rte') {
            $nomRue = 'Route ' . substr($nomRue, 3);
        } elseif (substr($nomRue, 0, 3) == 'Pl.') {
            $nomRue = 'Place ' . substr($nomRue, 3);
        } elseif (substr($nomRue, 0, 4) == 'All.') {
            $nomRue = 'Allée ' . substr($nomRue, 4);
        } elseif (substr($nomRue, 0, 2) == 'Bd') {
            $nomRue = 'Boulevard ' . substr($nomRue, 2);
        } elseif (substr($nomRue, 0, 4) == 'Esc.') {
            $nomRue = 'Escaliers ' . substr($nomRue, 4);
        } elseif (substr($nomRue, 0, 4) == 'Gal.') {
            $nomRue = 'Galerie ' . substr($nomRue, 4);
        } elseif (substr($nomRue, 0, 4) == 'Rlle') {
            $nomRue = 'Ruelle ' . substr($nomRue, 4);
        } elseif (substr($nomRue, 0, 5) == 'Sent.') {
            $nomRue = 'Sentier ' . substr($nomRue, 5);
        } elseif (substr($nomRue, 0, 5) == 'Espl.') {
            $nomRue = 'Esplanade ' . substr($nomRue, 5);
        } elseif (substr($nomRue, 0, 5) == 'Prom.') {
            $nomRue = 'Promenade ' . substr($nomRue, 5);
        }

        $nomRue = trim(str_replace('  ', ' ', $nomRue)); //eventuel espace double
        $nomRue = str_replace("'", "''", $nomRue);
        $numeroRue = '';
        if (isset($oData->localisationNumero)) {
            $numeroRue = $oData->localisationNumero;
            $numeroRue = str_replace(' ', '', $numeroRue);
            $numeroRue = str_replace("'", "''", $numeroRue);
        }
        $nomRue = utf8go_decode($nomRue);
        $sSql = "cn_adresse_nompouraffaire_par_longname_num '$nomRue'";
        if ($numeroRue !== '') { $sSql .= ", '$numeroRue'";}
        else { $sSql .= ", NULL";}
        $dbgo->queryRetJson2($sSql);
        $oGoRueAdresse = json_decode($dbgo->resString, false);
        $oData->idRueGo = $oGoRueAdresse[0]->idrue;
        $oData->idAdresseGo = $oGoRueAdresse[0]->idadresse;
        $oData->rueAdresseNomAffaire = $oGoRueAdresse[0]->nomrueadr4affaire;
        if ($oGoRueAdresse[0]->idbatiment > 0) {
            $oData->idsBatimentGo = $oGoRueAdresse[0]->idbatiment;
            $nbrBatimentGo = 1;
        }
        if ($oGoRueAdresse[0]->idparcelle > 0) {
            $oData->idsParcelleGo = $oGoRueAdresse[0]->idparcelle;
            $nbrParcelleGo++;
        }
    }

    //numéro ECA
    if (isset($oData->numeroECA)) {
        $jfNumECA = trim($oData->numeroECA);
        if (preg_match('/^[0-9]{1,10}$/', $jfNumECA)) {
            $sSql = "cn_batiment_par_eca $jfNumECA";
            $dbgo->queryRetJson2($sSql);
            $oGoIdBatiment = json_decode($dbgo->resString, false);
            for ($i = 0; $i < count($oGoIdBatiment); $i++) {
                if ($nbrBatimentGo == 0) {
                    $oData->idsBatimentGo = $oGoIdBatiment[$i]->idbatiment;
                    $nbrBatimentGo = 1;
                } else {
                    //verification de doublon
                    $bDeja = false;
                    $aIdsBatimentGo = explode(',', $oData->idsBatimentGo);
                    for ($j = 0; $j < count($aIdsBatimentGo); $j++) {
                        if ($aIdsBatimentGo[$j] == $oGoIdBatiment[$i]->idbatiment) {
                            $bDeja = true;
                            break;
                        }
                    }
                    if (!$bDeja) {
                        $oData->idsBatimentGo .= ',' . $oGoIdBatiment[$i]->idbatiment;
                        $nbrBatimentGo++;
                    }
                }
            }
        } else {
            $jfNumECA = str_replace("'", '', $jfNumECA); //séparateur milier éliminé
            $bTrouveSeparateur = false;
            if (strpos($jfNumECA, "-") !== false) {
                $jfNumECA = str_replace('-', ',', $jfNumECA);
                $bTrouveSeparateur = true;
            }
            if (strpos($jfNumECA, ";") !== false) {
                $jfNumECA = str_replace(';', ',', $jfNumECA);
                $bTrouveSeparateur = true;
            }
            if (strpos($jfNumECA, "/") !== false) {
                $jfNumECA = str_replace('/', ',', $jfNumECA);
                $bTrouveSeparateur = true;
            }
            if (!$bTrouveSeparateur) {
                $jfNumECA = str_replace(' ', ',', $jfNumECA);
            }
            $ajfNumECA = explode(',', $jfNumECA);
            for ($ieca = 0; $ieca < count($ajfNumECA); $ieca++) {
                $numECA = trim($ajfNumECA[$ieca]);
                if (preg_match('/^[0-9]{1,10}$/', $numECA)) {
                    $sSql = "cn_batiment_par_eca $numECA";
                    $dbgo->queryRetJson2($sSql);
                    $oGoIdBatiment = json_decode($dbgo->resString, false);
                    for ($i = 0; $i < count($oGoIdBatiment); $i++) {
                        if ($nbrBatimentGo == 0) {
                            $oData->idsBatimentGo = $oGoIdBatiment[$i]->idbatiment;
                            $nbrBatimentGo = 1;
                        } else {
                            //verification de doublon
                            $bDeja = false;
                            $aIdsBatimentGo = explode(',', $oData->idsBatimentGo);
                            for ($j = 0; $j < count($aIdsBatimentGo); $j++) {
                                if ($aIdsBatimentGo[$j] == $oGoIdBatiment[$i]->idbatiment) {
                                    $bDeja = true;
                                    break;
                                }
                            }
                            if (!$bDeja) {
                                $oData->idsBatimentGo .= ',' . $oGoIdBatiment[$i]->idbatiment;
                                $nbrBatimentGo++;
                            }
                        }
                    }
                }
            }
        }
    }

    //Parcelle
    if (isset($oData->parcelle)) {
        $jfParcelle = strtoupper(trim($oData->parcelle));
        $jfParcelle = str_replace("'", '', $jfParcelle); //séparateur milier éliminé
        //Certaine parcelle domaine public ont comme numéro DP xxx, temporairement on remplace 'DP ' par 'DP_'
        $jfParcelle = str_replace('DP ', 'DP_', $jfParcelle);

        //On cherche d'éventuel séparateur si plusieurs parcelles passées
        $bTrouveSeparateur = false;
        if (strpos($jfParcelle, " - ") !== false) { // " - " et pas "-" utilisé pour les PPE
            $jfParcelle = str_replace('-', ',', $jfParcelle);
            $bTrouveSeparateur = true;
        }
        if (strpos($jfParcelle, ";") !== false) {
            $jfParcelle = str_replace(';', ',', $jfParcelle);
            $bTrouveSeparateur = true;
        }
        if (strpos($jfParcelle, "/") !== false) {
            $jfParcelle = str_replace('/', ',', $jfParcelle);
            $bTrouveSeparateur = true;
        }
        if (!$bTrouveSeparateur) {
            $jfParcelle = str_replace(' ', ',', $jfParcelle);
        }
        $ajfParcelle = explode(',', $jfParcelle);
        for ($ipar = 0; $ipar < count($ajfParcelle); $ipar++) {
            $parcelle = trim($ajfParcelle[$ipar]);
            $parcelle = str_replace('DP_', 'DP ', $parcelle); //on remet les DP correctement
            if (valideNumeroParcelle($parcelle)) {
                $sSql = "cn_parcelle_par_numero $parcelle";
                $dbgo->queryRetInt($sSql);
                $idParcelle = $dbgo->resInt;
                if ($idParcelle > 0) {
                    if ($nbrParcelleGo == 0) {
                        $oData->idsParcelleGo = $idParcelle;
                        $nbrParcelleGo = 1;
                    } else {
                        //verification de doublon
                        $bDeja = false;
                        $aIdsParcelleGo = explode(',', $oData->idsParcelleGo);
                        for ($j = 0; $j < count($aIdsParcelleGo); $j++) {
                            if ($aIdsParcelleGo[$j] == $idParcelle) {
                                $bDeja = true;
                                break;
                            }
                        }
                        if (!$bDeja) {
                            $oData->idsParcelleGo .= ',' . $idParcelle;;
                            $nbrParcelleGo++;
                        }
                    }
                }
            }
        }
    }

    //Acteur
    $oData->demandeur->idacteurGo = 0;
    $oData->demandeur->nomacteurGo = '';
    $emailDemandeur = '';
    $nomDemandeur = '';
    $nomDemSociete = '';
    $nomDemNom = '';
    $idTypeComplement = 0;
    if (isset($oData->demandeur)) {
        if (isset($oData->demandeur->societe)) {
            $nomDemSociete = $oData->demandeur->societe;
        }
        if (isset($oData->demandeur->nom)) {
            $nomDemNom = $oData->demandeur->nom;;
        }
        if (isset($oData->demandeur->email)) {
            $emailDemandeur = $oData->demandeur->email;
            $emailDemandeur = trim($emailDemandeur);
            $emailDemandeur = str_replace("'", "''", $emailDemandeur);
            $idTypeComplement = 5;
        }
        if (isset($oData->demandeur->societe)) {
            $nomDemandeur = $oData->demandeur->societe;
        } elseif (isset($oData->demandeur->nom)) {
            $nomDemandeur = $oData->demandeur->nom;
            if (isset($oData->demandeur->prenom)) {
                $nomDemandeur .= ' ' . $oData->demandeur->prenom;
            }
        }
        $emailDemandeur = str_replace("'", "''", trim($emailDemandeur));
        $nomDemSociete = str_replace("'", "''", trim($nomDemSociete));
        $nomDemNom = str_replace("'", "''", trim($nomDemNom));
        $nomDemandeur = trim(str_replace('  ', ' ', $nomDemandeur));
        $nomDemandeur = str_replace("'", "''", $nomDemandeur);
    }
    if ($emailDemandeur !== '' || $nomDemandeur !== '') {
        $sSql = "cn_acteur_cherche $idTypeComplement, '$emailDemandeur', '$nomDemandeur', '$nomDemSociete', '$nomDemNom'";
        $dbgo->queryRetJson2($sSql);
        $oGoActeur = json_decode($dbgo->resString, false);
        if (count($oGoActeur) > 0) {
            //Il peut potentiellement en avoir plusieurs, on prend le premier
            $oData->demandeur->idacteurGo = $oGoActeur[0]->idacteur;
            $oData->demandeur->nomacteurGo = $oGoActeur[0]->nomacteur;
        }
    }

    //Fichiers controle
    $oJaxForms = new CNJaxForms(
        jaxServer: 'api.lausanne.ch'
        ,idForms: 'URB_dispense_permis_construire'
    );
    for ($i = 0; $i < count($oData->fichiers); $i++) {
        if ($oData->fichiers[$i]->idjf !== '') {
            try {
                $jfFileAttachment = $oJaxForms->getFileAttachment(idFile: $oData->fichiers[$i]->idjf);
                $oData->fichiers[$i]->size = $jfFileAttachment['size'];
                $oData->fichiers[$i]->mimetype = $jfFileAttachment['mime_type'];
                $sha256 = hash('sha256', $jfFileAttachment['content']);
                $oData->fichiers[$i]->sha256 = $sha256;
                if ($i > 0) {
                    //Recherche de doublon, même fichier passé plusieurs fois
                    $jmax = $i;
                    for ($j = 0; $j < $jmax; $j++) {
                        if ($oData->fichiers[$j]->sha256 == $sha256) {
                            $noFichier = $j+1;
                            $oData->fichiers[$i]->infoDoublon = "doublon avec fichier $noFichier";
                            break;
                        }
                    }
                }
                $sSql = "cn_document_id_parsha256 '$sha256'";
                $dbgo->queryRetInt($sSql);
                $oData->fichiers[$i]->idDocGo = $dbgo->resInt;
            } catch (Exception $e) {
            }
        }
    }

    $preImportData = $oData;
}
unset($dbgo);

if ($messageErreur === '') {
    echo json_encode($preImportData);
} else {
    echo $messageErreur;
}

function valideNumeroParcelle($chaine) {
    // Vérifier la longueur maximale
    if (strlen($chaine) > 20) {
        return false;
    }

    // Vérifier si la chaîne est vide
    if (empty($chaine)) {
        return false;
    }

    // Cas 1 : Commence par "DP " (DP suivi d'un espace)
    if (strpos($chaine, 'DP ') === 0) {
        $reste = substr($chaine, 3); // On récupère ce qui suit "DP "

        // Le reste doit commencer par un chiffre différent de 0
        if (empty($reste) || !ctype_digit($reste[0]) || $reste[0] === '0') {
            return false;
        }

        // Le reste doit contenir uniquement des chiffres
        if (!ctype_digit($reste)) {
            return false;
        }

        return true;
    }

    // Cas 2 : Commence par un chiffre (mais pas 0)
    if (ctype_digit($chaine[0])) {
        // Ne doit pas commencer par 0
        if ($chaine[0] === '0') {
            return false;
        }

        // Ne doit pas finir par un tiret
        if (substr($chaine, -1) === '-') {
            return false;
        }

        // Vérifier que la chaîne contient uniquement des chiffres et des tirets
        if (!preg_match('/^[0-9\-]+$/', $chaine)) {
            return false;
        }

        // Vérifier qu'un tiret n'est pas suivi de 0
        if (preg_match('/-0/', $chaine)) {
            return false;
        }

        return true;
    }

    // Aucun des cas valides
    return false;
}