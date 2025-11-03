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
}
export interface Fichier {
    idjf: string
    b64content: string
    mimetype: string
    size: number
    sha256: string
}

export interface DataForms {
    idDemande: string
    localisationRue?: string
    localisationNumero?: string
    numeroECA?: string
    parcelle?: string
    descriptionTravaux?: string
    demandeur: Demandeur 
    fichiers: Fichier[]
}