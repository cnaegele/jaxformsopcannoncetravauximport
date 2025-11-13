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
    nomacteurGo?: string
}
export interface Fichier {
    idjf: string
    filename: string
    b64content: string
    mimetype: string
    size: number
    sha256: string
    infoDoublon: string
    idFamille: number
    idDocGo: number
}

export interface DataForms {
    idDemande: string
    numeroDemande: string
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

export interface FichierImport {
    idJaxforms: string
    idFamille: number
    filename: string
}
export interface AffaireDataImport {
    idJaxformsDemande: string
    numeroJaxformsDemande: string
    nomAffaire: string
    descriptionAffaire: string
    idEmployeGestionnaire: number
    idEmployeTechnicien: number
    idActeurClient: number
    idBatimentLie: number[]
    idParcelleLie: number[]
    fichiers: FichierImport[]    
}

export interface EmployeParticipe {
    id: number
    nom: string
}

export function stringToPositiveInteger(str: string): number | null {
    // Vérifie que c'est une string non vide
    if (!str || str.trim() === '') {
        return null;
    }
    
    // Convertit en number
    const num = Number(str);
    
    // Vérifie que c'est un nombre valide, un entier et positif
    if (isNaN(num) || !Number.isInteger(num) || num < 0) {
        return null;
    }
    
    return num;
}