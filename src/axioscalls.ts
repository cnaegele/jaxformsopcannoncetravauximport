import axios from 'axios'
import type { AxiosResponse, AxiosError } from 'axios'

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
    console.log(`${urlfd}?jsonparams=${jsonParams}`)
    const params = new URLSearchParams([['jsonparams', jsonParams]])
    try {
        const response: AxiosResponse<JFFormsData> = await axios.get(urlfd, { params })
        const respData: ApiResponseJFFD = response
        console.log(respData)
        return respData
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