<?php
require 'gdt/gautentificationf5.php';
require_once '/data/dataweb/GoelandWeb/webservice/employe/clCNWSEmployeSecurite.php';
require_once 'gdt/cldbgoeland.php';
require_once '/data/dataweb/GoelandWeb/goeland/jaxforms/cncljaxforms.php';
require_once '/data/dataweb/GoelandWeb/goeland/affaire2/clcnaffaireT327.php';
require_once '/data/dataweb/GoelandWeb/goeland/affaire2/clcnaffaireT230.php';
require_once '/data/dataweb/GoelandWeb/webservice/affaire/sauvedataspec/sauveDataSpecT327.php';
require_once '/data/dataweb/GoelandWeb/webservice/affaire/clCNWSAffaireSpecialisation04.php';
require_once '/data/dataweb/GoelandWeb/webservice/geodata/clGetGeoData.php';
require_once 'gdt/gwssecurity.php';
require_once '/data/dataweb/GoelandWeb/goeland/document/clcndocumentpost.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:  *");
header("Access-Control-Allow-Methods:  POST");
$idCaller = 0;
if (array_key_exists('empid', $_SESSION)) {
    $idCaller = $_SESSION['empid'];
}
$messageErreur = '';
$idAffOPCAnnonceTravaux = 0;
$idTypeAffaire = 327;
if ($idCaller > 0) {
    $pseudoWSEmployeSecurite = new CNWSEmployeSecurite();
    if ($pseudoWSEmployeSecurite->isInGroupe($idCaller, 'OPCAnnonceTravauxImport')) {
        $jsonData = file_get_contents('php://input');
        if ($oData = json_decode($jsonData, false)) {
            $dbgo = new DBGoeland();

            //Affaire
            $idJaxformsDemande = $oData->idJaxformsDemande;
            $uuidJaxformsDemande = $oData->numeroJaxformsDemande;

            if (preg_match('/^[a-zA-Z0-9-]{1,50}$/', $idJaxformsDemande)
                && preg_match('/^[a-zA-Z0-9-]{1,50}$/', $uuidJaxformsDemande)) {
                $dbgo = new DBGoeland();
                $sSql = "cn_affaire_idjaxforms_get_idaffaire '$idJaxformsDemande'";
                $dbgo->queryRetInt($sSql);
                $idAffaireDeja = $dbgo->resInt;
                if ($idAffaireDeja == 0) {
                    $nomAffaire = $oData->nomAffaire;
                    $description = $oData->descriptionAffaire;
                    $sCode = calculSecurityCode($idCaller);
                    $oAffOPCAnnonceTravaux = new CNAffaire(0, true, $idCaller, $sCode);
                    $idAffOPCAnnonceTravaux = $oAffOPCAnnonceTravaux->reserveId($idTypeAffaire);
                    if ($idAffOPCAnnonceTravaux > 0) {
                        $sSql = "cn_affaire_idjaxforms_insert $idAffOPCAnnonceTravaux, '$idJaxformsDemande', '$uuidJaxformsDemande'";
                        $dbgo->queryRetNothing($sSql, 'W');
                        //On enlève le créateur des employé concernés
                        $oAffOPCAnnonceTravaux->supprimeEmployeConcerne($idCaller);

                        //Gestionnaire et technicien
                        $idEmployeGestionnaire = $oData->idEmployeGestionnaire;
                        $idEmployeTechnicien = $oData->idEmployeTechnicien;
                        if ($idEmployeGestionnaire > 0) {
                            $sXmlData = "<Data><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdEmploye>$idEmployeGestionnaire</IdEmploye><IdRole>5</IdRole></Data>";
                            $oAffOPCAnnonceTravaux->sauveEmployeConcerne($sXmlData);
                        }
                        if ($idEmployeTechnicien > 0) {
                            $sXmlData = "<Data><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdEmploye>$idEmployeTechnicien</IdEmploye><IdRole>9</IdRole></Data>";
                            $oAffOPCAnnonceTravaux->sauveEmployeConcerne($sXmlData);
                        }

                        //nom, description
                        $nomAff = rawurlencode(utf8go_decode($nomAffaire));
                        $descAff = rawurlencode(utf8go_decode($description));;
                        $sXmlData = "<Data><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdType>$idTypeAffaire</IdType><Nom>$nomAff</Nom><Description>$descAff</Description></Data>";
                        $oAffOPCAnnonceTravaux->sauveData($sXmlData);

                        //Acteur client
                        $idActeurClient = $oData->idActeurClient;
                        if ($idActeurClient > 0) {
                            $sXmlData = "<Data><IdActeurRole>0</IdActeurRole><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdActeur>$idActeurClient</IdActeur><IdRole>10</IdRole></Data>";
                            $oAffOPCAnnonceTravaux->sauveActeurConcerne($sXmlData);
                        }

                        //Batiment lié
                        $aBatiment = $oData->idBatimentLie;
                        foreach ($aBatiment as $idObjet) {
                            $sXmlData = "<Data><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdObjet>$idObjet</IdObjet></Data>";
                            $oAffOPCAnnonceTravaux->sauveObjetLie($sXmlData);
                        }
                        //Parcelle liée
                        $aParcelle = $oData->idParcelleLie;;
                        foreach ($aParcelle as $idObjet) {
                            $sXmlData = "<Data><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdObjet>$idObjet</IdObjet></Data>";
                            $oAffOPCAnnonceTravaux->sauveObjetLie($sXmlData);
                        }

                        //Fichiers
                        $oJaxForms = new CNJaxForms(
                            jaxServer: 'api-vali.lausanne.ch'
                            ,idForms: 'URB_dispense_permis_construire'
                        );
                        $a_config_section_production = parse_ini_file('/data/config/goeland.ini', false);
                        $upload_path = $a_config_section_production['documents.uploaddir'];

                        //taille maximun fichier
                        $sizeMax = 5242880; //5 Mo
                        $filename = '/data/dataweb/GoelandWeb/goeland/document/nouveau/json/opcannoncetravauximportdejaxforms.json';
                        if (file_exists($filename)) {
                            if ($docsPrms = json_decode(file_get_contents($filename))) {
                                $sizeMaxDoc = $docsPrms->sizemax;
                            }
                        }
                        $aFichiers = $oData->fichiers;
                        foreach ($aFichiers as $oFichier) {
                            $idJaxformsFile = $oFichier->idJaxforms;
                            $idFamille = $oFichier->idFamille;
                            $clientFilename = $oFichier->filename;
                            $jfFileAttachment = $oJaxForms->getFileAttachment(idFile: $idJaxformsFile);
                            $size = $jfFileAttachment['size'];
                            $mimetype = $jfFileAttachment['mime_type'];
                            $fileContent = $jfFileAttachment['content'];
                            //$sha256 = hash('sha256', $jfFileAttachment['content']);
                            $tmpfilename = $upload_path . $idJaxformsFile;
                            if (file_put_contents($tmpfilename, $fileContent)) {
                                $mimetype = mime_content_type($tmpfilename);
                                //je vais voir si j'accepte ce mimetype et ensuite, j'ajoute l'extension correspondante au nom de fichier.
                                $sSql = "cn_typedocument_idtypeextension_parmimetype '$mimetype'";
                                $dbgo->queryRetJson2($sSql);
                                $oDocType = json_decode($dbgo->resString, false);
                                if (count($oDocType) == 1) {
                                    $idDocType = $oDocType[0]->idtypedocument;
                                    $extension = $oDocType[0]->extension;
                                    rename($tmpfilename, $tmpfilename . '.' . $extension);
                                    $tmpfilename = $tmpfilename . '.' . $extension;
                                    $titreDoc = "$clientFilename - $nomAffaire";
                                    $dateDuJour = date('Y-m-d');
                                    //Post du document
                                    $sXmlDataDocument = "<Data>";
                                    $sXmlDataDocument .= "<Titre>" . rawurlencode(utf8go_decode($titreDoc)) . "</Titre>";
                                    $sXmlDataDocument .= "<IdFamille>$idFamille</IdFamille>"; //Photo
                                    $sXmlDataDocument .= "<IdType>$idDocType</IdType>";
                                    $sXmlDataDocument .= "<DateOf>$dateDuJour</DateOf>";
                                    $sXmlDataDocument .= "<DocExterne>1</DocExterne>";
                                    if ($idActeurClient > 0) {
                                        $sXmlDataDocument .= "<IdAuteurExt>$idActeurClient</IdAuteurExt>";
                                    }
                                    $sXmlDataDocument .= "<IdNivConf>1</IdNivConf>";
                                    $sXmlDataDocument .= "<IdEmploye>$idCaller</IdEmploye>";
                                    $sXmlDataDocument .= "<IdAffaireL>$idAffOPCAnnonceTravaux</IdAffaireL>";
                                    $sXmlDataDocument .= "</Data>";
                                    $oDocPost = new CNDocumentPost('', $sizeMaxDoc);
                                    $oDocPost->post($tmpfilename, $sXmlDataDocument);
                                    if ($oDocPost->_getResErreur() != '') {
                                        $messageErreur .= $oDocPost->_getResErreur() . "\n";
                                    }
                                    unset($oDocPost);
                                }
                            }
                        }

                        //Fichiers repérés comme déjà dans goéland
                        $aDocumentsLies = $oData->documentsLies;
                        foreach ($aDocumentsLies as $oDocumentLie) {
                            $idDocument = $oDocumentLie->idDocGo;
                            if ($idDocument > 0) {
                                $sXmlData = "<Data><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdDocument>$idDocument</IdDocument></Data>";
                                $oAffOPCAnnonceTravaux->sauveDocumentLie($sXmlData);
                            }
                        }

                        //Données spécialisée Secteurs
                        $numSecteurOPC = '0';
                        $numSecteurARCH = '0';
                        $idSecteurArch = 0;
                        $oAffT230 = new CNAffaireT230($idAffOPCAnnonceTravaux, false);
                        $domD = new domdocument();
                        $domD->loadXML(utf8go_encode(rawurldecode($oAffT230->getSecteurs())));
                        unset($oAffT230);
                        $nodes = $domD->getElementsByTagName("NumSecteurOPC");
                        if ($nodes->length == 1) {
                            $numSecteurOPC = $nodes->item(0)->nodeValue;
                        }
                        $nodes = $domD->getElementsByTagName("NumSecteurARCH");
                        if ($nodes->length == 1) {
                            $numSecteurARCH = $nodes->item(0)->nodeValue;
                            $sSql = "CN_DicoZoneIdZoneParNomZone 'Secteur ARCH $numSecteurARCH'";
                            $dbgo->queryRetInt($sSql);
                            $idSecteurArch = $dbgo->resInt;
                        }
                        unset($domD);

                        $sXmlDataSpec = '<Data><Specialisation>';
                        $sXmlDataSpec .= '<SecteurOPC>' . $numSecteurOPC . '</SecteurOPC>';
                        $sXmlDataSpec .= '<IdSecteurArch>' . $idSecteurArch . '</IdSecteurArch>';
                        $sXmlDataSpec .= '<BLogement></BLogement>';
                        $sXmlDataSpec .= '<BMonumentH></BMonumentH>';
                        $sXmlDataSpec .= '<NumeroCamac></NumeroCamac><InfoFacturation></InfoFacturation>';
                        $sXmlDataSpec .= '</Specialisation></Data>';
                        $domD = new domdocument();
                        $domD->loadXML($sXmlDataSpec);
                        $nodeSpec = $domD->getElementsByTagName("Specialisation")->item(0);
                        sauveDataSpec($idAffOPCAnnonceTravaux, $nodeSpec);
                        unset($domD);

                        //Eventuelle participation PATBAT
                        $pseudoWSAffaireSpecialisation04 = new CNWSAffaireSpecialisation04();
                        $bParticipePATBAT = false;
                        //Batiment lié recherche si note recensement
                        $aBatiment = $oData->idBatimentLie;
                        foreach ($aBatiment as $idObjet) {
                            $noteRA = $pseudoWSAffaireSpecialisation04->affT230NoteRAObjet($idObjet);
                            if ($noteRA <= 4) {
                                $bParticipePATBAT = true;
                                break;
                            }
                        }

                        if (!$bParticipePATBAT) {
                            //Parcelle liée recherche si valeur ISOS
                            $aParcelle = $oData->idParcelleLie;
                            foreach ($aParcelle as $idObjet) {
                                $idValeurISOS = $pseudoWSAffaireSpecialisation04->affT230ISOSValeurObjet($idObjet); //Méthode existe déjà pour type 230, on ne la reécrit pas.
                                if ($idValeurISOS > 0) {
                                    $bParticipePATBAT = true;
                                    break;
                                }
                            }

                            if (!$bParticipePATBAT) {
                                //Recherche si ensemble bâti
                                //Coordonnée "moyenne"
                                $cOE = 0;
                                $cSN = 0;
                                $abBox = json_decode($oAffOPCAnnonceTravaux->bBoxData(),true);
                                unset($oAff);
                                $nbrEnsBat = 0;
                                if ($abBox[0]['MinOE'] > 0)
                                {
                                    $cOE = round(($abBox[0]['MinOE'] + $abBox[0]['MaxOE']) / 200);
                                    $cSN = round(($abBox[0]['MinSN'] + $abBox[0]['MaxSN']) / 200);
                                    $wsgeoclient = new GetGeoData();
                                    $domDEnsBat = new domdocument();
                                    $retwsgeoclient = rawurldecode($wsgeoclient->getNumeroEnsembleBatisByPos($cOE,$cSN));
                                    if ($retwsgeoclient != '')
                                    {
                                        if (strpos($retwsgeoclient,'<RESULTS>') !== false)
                                        {
                                            $domDEnsBat->loadXML(utf8go_encode($retwsgeoclient));
                                            $nodes = $domDEnsBat->getElementsByTagName("RECORD");
                                            $nbrEnsBat = $nodes->length;
                                            if ($nbrEnsBat > 0) {
                                                $bParticipePATBAT = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        unset($pseudoWSAffaireSpecialisation04);
                        if ($bParticipePATBAT) {
                            //Unité PATBAT participe
                            $sXmlData = "<Data><IdAffaire>$idAffOPCAnnonceTravaux</IdAffaire><IdUO>110</IdUO><IdRole>1</IdRole></Data>";
                            $oAffOPCAnnonceTravaux->sauveUniteOrgConcerne($sXmlData);
                        }

                    } else {
                        $messageErreur .= "ERREUR A LA CREATION DE L'AFFAIRE : " . $oAffOPCAnnonceTravaux->resErreur;
                    }
             }  else {
                    $messageErreur .= "LE FORMULAIRE $idJaxformsDemande DEJA IMPORTE DANS L'AFFAIRE $idAffaireDeja \n";
                }
            } else {
                $messageErreur .= "identifiant formulaire jaxforms invalide \n";
            }
            unset($dbgo);
        } else {
            $messageErreur .= "DONNEES JSON INVALIDES\n";
        }
    } else {
        $messageErreur .= "UTILISATEUR NON AUTORISE (OPCAnnonceTravauxImport)\n";
    }
} else {
    $messageErreur .= "UTILISATEUR NON IDENTIFIE\n";
}
if ($messageErreur === '') {
    echo $idAffOPCAnnonceTravaux;
    $oJaxForms->putStatusArchives($idJaxformsDemande);
} else {
    if ($idAffOPCAnnonceTravaux > 0) {
        echo "AFFAIRE CREEE: $idAffOPCAnnonceTravaux\n$messageErreur";
        $oJaxForms->putStatusArchives($idJaxformsDemande);
    } else {
        echo $messageErreur;
    }
}

