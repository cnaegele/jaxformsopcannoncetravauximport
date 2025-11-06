import axios from 'axios'
import type { AxiosResponse, AxiosError } from 'axios'
export interface Acteur {
  acteurid: number
  acteurnom: string
  bactif: number
  acteuradresse?: string
  acteurcommentaire?: string
  datecreation?: string
  datemodification?: string
  datedesactivation?: string
}
export interface ActeurComplement {
  acteurcomplementtypeid: number
  acteurcomplementtype: string
  acteurcomplement: string
}
export interface ActeurAdresse {
  actadidadresse: number
  actadidtypeadresse: number
  actadbprincipal: number
  actadruenumero?: string
  actadcpville?: string
  actadpays?: string
  actadcomplement? : string
  acadcalification?: string
}
export interface ActeurActLie {
  actaclisenslien: string
  actaclidacteur: number
  actaclidrolecontact: number
  actaclnom: string
  actaclrolecontact: string
}
export interface ActeurRole {
  acroleid: number
  acroleidrole: number
  acrolerole: string
  aroleidobjet: number
  aroledatecreation: string
  aroleobjet: string
  aroleobjetnom:string
  aroleobjetbactif?:number
  aroleobjetdroitacces: number
  aroleobjeturl: string  
}

export interface ApiResponseDL {
  success: boolean
  message: string
  data?: Acteur[]
}
export interface ApiResponseDc {
  success: boolean
  message: string
  data?: ActeurComplement[]
}
export interface ApiResponseDa {
  success: boolean
  message: string
  data?: ActeurAdresse[]
}
export interface ApiResponseDal {
  success: boolean
  message: string
  data?: ActeurActLie[]
}
export interface ApiResponseDar {
  success: boolean
  message: string
  data?: ActeurRole[]
}
// Interface générique pour les réponses API
interface ApiResponse<T> {
  success: boolean
  message: string
  data?: T[]
}

export async function getActeursListe(server: string = '', page: string, jsonCriteres: string = '{}'): Promise<ApiResponseDL> {
    console.log(jsonCriteres)
    const urlacl: string = `${server}${page}`
    const params = new URLSearchParams([['jsoncriteres', jsonCriteres]])
    try {
        const response: AxiosResponse<Acteur[]> = await axios.get(urlacl, { params })
        const respData: ApiResponseDL= {
            "success": true,
            "message": `ok`,
            "data": response.data
        }
        console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function getActeurData<T>(
  server: string, 
  page: string, 
  idActeur: number
): Promise<ApiResponse<T>> {
  const url: string = `${server}${page}`
  const params = new URLSearchParams([['idacteur', idActeur.toString()]])
  try {
    const response: AxiosResponse<T[]> = await axios.get(url, { params })
    return {
      success: true,
      message: 'ok',
      data: response.data
    }
  } catch (error) {
    return traiteAxiosError<T>(error as AxiosError)
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
  return respData
}


