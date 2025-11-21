import axios from 'axios'
import type { AxiosResponse, AxiosError } from 'axios'
import type { DataForms } from '@/jaxformsOpcAnnonceTravauxImport.ts'


//Interfaces pour la liste des formulaires
interface Field {
  id: string;
  value: string;
}

export interface Row {
  field: Field[];
  xml: string | null;
  json: string | null;
  hasPDF: boolean | null;
  hasAttachment: boolean | null;
  seqID: number | null;
}

interface Info {
  pageSize: number;
  hasNext: boolean;
  totalSize: number;
  size: number;
  offset: number;
}

export interface JFFormsListe {
  info: Info;
  row: Row[];
}
export interface ApiResponseJFFL {
    success?: boolean;
    message?: string;
    data?: JFFormsListe;
}

//Interfaces pour les données d'un formulaire
export interface Group {
  var: Variable | Variable[];
  id: string;
}

interface Variable {
  id: string;
  content?: string | number;
}

interface PersonalInfo {
  value: string;
  key: string;
}

export interface JFFormsData {
  data: {
    group: Group[];
  };
  fingerprint: {
    formID: string;
    CREATED: string;
    originURL: string;
    GUID: string;
  };
  pi: PersonalInfo[];
}
export interface ApiResponseJFFD {
    success?: boolean;
    message?: string;
    data?: JFFormsData;
}
export interface ApiResponseNumber {
    success?: boolean;
    message?: string;
    data?: number;
}
export interface ApiResponseNumStr {
    success?: boolean;
    message?: string;
    data?: number | string;
}
//Interface pour les donnéés a importer (formulaire et vérification goéland)
export interface ApiResponseIFD {
    success?: boolean;
    message?: string;
    data?: DataForms | string;
}

//Interface pour les liste d'employe par unité
export interface EmployeParUO {
  idemploye: number
  titrepol: string
  nom: string
  prenom: string
  DescTreeDenorm: string  
}
export interface ApiResponseEU {
    success?: boolean;
    message?: string;
    data?: EmployeParUO[];
}

//interface pour la liste des famille de document et la taille maximun autorisée
export interface DocumentImportParams {
  sizemax: number
  familles: [{id: number, label: string}]  
}
export interface ApiResponseDIP {
    success?: boolean;
    message?: string;
    data?: DocumentImportParams;
}

// Interface générique pour les réponses API
export interface ApiResponse<T> {
    success: boolean
    message: string
    data?: T
}

export function getListeFieldValue(row: Row, fieldId: string): string | undefined {
  return row.field.find(f => f.id === fieldId)?.value;
}

export function getDataContentByGroupAndVarId(
  formData: JFFormsData, 
  groupId: string, 
  varId: string
): string | number | undefined {
  // Trouver le groupe correspondant
  const group = formData.data.group.find(g => g.id === groupId);
  
  if (!group) {
    return undefined;
  }
  
  // Normaliser var en tableau
  const vars = Array.isArray(group.var) ? group.var : [group.var];
  
  // Chercher la variable dans ce groupe
  const variable = vars.find(v => v.id === varId);
  
  return variable?.content;
}

export async function getJFFormsListe(server: string = '', page: string, jsonParams: string = '{}'): Promise<ApiResponseJFFL> {
    const urlfl: string = `${server}${page}`
    const params = new URLSearchParams([['jsonparams', jsonParams]])
    try {
        const response: AxiosResponse<JFFormsListe> = await axios.get(urlfl, { params })
        const respData: ApiResponseJFFL = response
        console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function getJFFormsData(server: string = '', page: string, jsonParams: string = '{}'): Promise<ApiResponseJFFD> {
    const urlfd: string = `${server}${page}`
    const params = new URLSearchParams([['jsonparams', jsonParams]])
    try {
        const response: AxiosResponse<JFFormsData> = await axios.get(urlfd, { params })
        const respData: ApiResponseJFFD = response
        console.log(respData.data)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function getIdAffaireGoeland(server: string = '', page: string, idJaxForms: string) : Promise<ApiResponseNumber> {
    const urlig: string = `${server}${page}`
    const params = new URLSearchParams([['idjaxforms', idJaxForms]])
    try {
        const response: ApiResponseNumber = await axios.get(urlig, { params })
        //console.log(response)
        return response
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function getImportFormsData(server: string = '', page: string, jsonData: string = '{}'): Promise<ApiResponseIFD> {
    const urlifd: string = `${server}${page}`
    const params = new URLSearchParams([['jsondata', jsonData]])
    try {
        const response: AxiosResponse<DataForms> = await axios.get(urlifd, { params })
        const respData: ApiResponseIFD = response
        //console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function getListeEmployeParUO(server: string = '', page: string, idunite: number): Promise<ApiResponseEU> {
    const urlle: string = `${server}${page}`
    const jsoncriteres: string = `{"iduniteorg":${idunite}}`
    const params = new URLSearchParams([['jsoncriteres', jsoncriteres]])
    try {
        const response: AxiosResponse<EmployeParUO[]> = await axios.get(urlle, { params })
        const respData: ApiResponseEU = response
        //console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function getDocImportParams(server: string = '', page: string): Promise<ApiResponseDIP> {
    const urlle: string = `${server}${page}`
    try {
        const response: AxiosResponse<DocumentImportParams> = await axios.get(urlle)
        const respData: ApiResponseDIP = response
        //console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function importAffaire(server: string = '', page: string, jsonData: string = '{}'): Promise<ApiResponseNumStr> {
    const url: string = `${server}${page}`
    try {
        const response: ApiResponseNumStr = await axios.post(url, jsonData, {
            headers: {
                'Content-Type': 'application/json'
            }
        })
        console.log(response)
        return response
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

function traiteAxiosError<T>(error: AxiosError): ApiResponse<T> {
    let msgErr: string = ''
    if (error.response) {
        msgErr = `${error.response.data}<br>${error.response.status}<br>${error.response.headers}`
    } else if (error.request.responseText) {
        msgErr = error.request.responseText
    } else {
        msgErr = error.message
    }
    const respData: ApiResponse<T> = {
        "success": false,
        "message": `ERREUR. ${msgErr}`,
    }
    return (respData)
}