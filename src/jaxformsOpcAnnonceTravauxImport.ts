import type { JFFormsListe } from '@/axioscalls.ts'

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
    status: string
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

export function getUUIDAndStatus(
  formsListe: JFFormsListe,
  accessID: string
): { uuid: string | null; status: string | null } {
  // Parcourir toutes les rows
  for (const row of formsListe.row) {
    // Chercher le field AccessID dans cette row
    const accessIDField = row.field.find(f => f.id === "AccessID");
    
    // Si l'AccessID correspond
    if (accessIDField?.value === accessID) {
      // Récupérer UUID et Status
      const uuidField = row.field.find(f => f.id === "UUID");
      const statusField = row.field.find(f => f.id === "Status");
      
      return {
        uuid: uuidField?.value ?? null,
        status: statusField?.value ?? null
      };
    }
  }
  
  // Si aucune row ne correspond
  return { uuid: '', status: '' };
}