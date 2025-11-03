interface Demandeur {
    societe?: string
    nom?: string
    prenom?: string
    rue?: string
    numero?: string
    npa?: string
    localite?: string
    email?: string
    telephone?: string
    idacteurGo?: number
    nomActeurGo?: string
}
export interface Fichier {
    idjf: string
    b64content: string
    mimetype: string
    size: number
    sha256: string
    idDocGo: number
}

export interface DataForms {
    idDemande: string
    localisationRue?: string
    localisationNumero?: string
    idRueGo?: number
    idAdresseGo?: number
    rueAdresseNomAffaire?: string
    numeroECA?: string
    idsBatimentGo?: string
    parcelle?: string
    idsParcelleGo?: string
    descriptionTravaux?: string
    demandeur: Demandeur 
    fichiers: Fichier[]
}