<template>
  <div v-if="messageErreur === ''">
    <v-card>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                ID demande
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium">
                {{ props.id }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>

          <v-col cols="12" md="3">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Numéro de demande
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium">
                {{ props.uuid }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>

          <v-col cols="12" md="3">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Nombre de fichiers
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium">
                {{ nombreFichiers }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" md="6">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Localisation
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium">
                {{ localisation }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>

          <v-col cols="12" md="3" v-if="numeroECA !== ''">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Numero ECA
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium">
                {{ numeroECA }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>

          <v-col cols="12" md="3" v-if="parcelle !== ''">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Parcelle
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium">
                {{ parcelle }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" md="12">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Description des travaux
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium">
                {{ descriptionTravaux }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" md="12">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Demandeur
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium" style="white-space: pre-line;">
                {{ demandeur }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>
        </v-row>

        <v-row v-if="infofacturation !== ''">
          <v-col cols="12" md="12">
            <v-list-item density="compact">
              <v-list-item-title class="text-body-2 text-grey">
                Informations facturation
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-1 font-weight-medium" style="white-space: pre-line;">
                {{ infofacturation }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-col>
        </v-row>


        <v-divider class="my-4"></v-divider>

        <div v-if="nombreFichiers > 0">
          <h3 class="text-subtitle-1 mb-3">
            <v-icon icon="mdi-paperclip" size="small" class="me-1"></v-icon>
            Fichiers joints
          </h3>

          <v-list density="compact" class="bg-transparent">
            <v-list-item v-for="(fichier, index) in listeFichiers" :key="fichier.idfichier"
              @click="voirFichier(fichier.idfichier)" class="mb-2" rounded border>
              <template v-slot:prepend>
                <v-avatar color="primary" size="36">
                  <v-icon icon="mdi-file-eye" size="20"></v-icon>
                </v-avatar>
              </template>

              <v-list-item-title>
                {{ fichier.nomfichier }}
              </v-list-item-title>

              <v-list-item-subtitle class="text-caption">
                ID: {{ fichier.idfichier }}
              </v-list-item-subtitle>

              <template v-slot:append>
                <v-btn icon="mdi-eye" variant="text" color="primary" size="small"></v-btn>
              </template>
            </v-list-item>
          </v-list>
        </div>

        <v-alert v-else type="info" variant="tonal" density="compact" icon="mdi-information" class="mt-2">
          Aucun fichier joint à cette demande
        </v-alert>
      </v-card-text>
    </v-card>
  </div>
  <div v-else id="divErreur">{{ messageErreur }}</div>
</template>

<script setup lang="ts">
import type { JFFormsData, ApiResponseJFFD, Group, ApiResponseJFFL } from '@/axioscalls.ts'
import type { DataForms, Fichier } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { getJFFormsData, getDataContentByGroupAndVarId, getIdFileByGroupAndVarId, getJFFormsListe } from '@/axioscalls.ts'
import { getUUIDAndStatus, getIDAndStatus } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { ref, onMounted } from 'vue'

interface Props {
  id: string
  uuid: string
  status: string
  ssServer?: string
  ssPage?: string
  ssPageListe?: string
}

const props = withDefaults(defineProps<Props>(), {
  ssServer: '',
  ssPage: '/goeland/jaxforms/axios/jfdata_annoncetravaux.php',
  ssPageListe: '/goeland/jaxforms/axios/jfsearch_annoncetravaux.php'
})

interface ListeFichiers {
  idfichier: string
  nomfichier: string
}

const messageErreur = ref<string>('')
const localisation = ref<string>('?')
const numeroECA = ref<string>('')
const parcelle = ref<string>('')
const descriptionTravaux = ref<string>('?')
const demandeur = ref<string>('')
const infofacturation = ref<string>('')
const nombreFichiers = ref<number>(0)
const idsfichier = ref<string[]>([])
const listeFichiers = ref<ListeFichiers[]>([])

let dataForms: DataForms = { idDemande: '', numeroDemande: '', status: '', demandeur: {}, infoFacturation: {}, fichiers: [] }

const emit = defineEmits<{
  (e: 'dataForms', jsonData: string): void
}>()

onMounted(() => {
  loadData();
})

const loadData = async () => {
  dataForms.idDemande = props.id
  dataForms.numeroDemande = props.uuid
  dataForms.status = props.status
  if (dataForms.idDemande === '' && dataForms.numeroDemande !== '') {
    //Le numero de formulaire a été passé mais le id. Il faut essayer de retrouver ce id en utilisant le search de l'API.
    const jsonParamsL: string = `{"pagesize":500,"offset":0,"demandestatus":40}`
    const responseL: ApiResponseJFFL = await getJFFormsListe(props.ssServer, props.ssPageListe, jsonParamsL)
    console.log("responseL de data", JSON.stringify(responseL))
    if (responseL.data !== undefined && typeof responseL.data !== "string") {
      const result = getIDAndStatus(responseL.data, dataForms.numeroDemande)
      dataForms.idDemande = result.id ?? ''
      dataForms.status = result.status ?? ''
      if (dataForms.idDemande === '') {
        messageErreur.value = `Aucun formulaire trouvé avec le numéro ${dataForms.numeroDemande} et le statut traité`
      }
    }
  }
  if (dataForms.idDemande !== '') {
    const jsonParamsD: string = `{"idformselement":"${dataForms.idDemande}"}`
    const responseD: ApiResponseJFFD = await getJFFormsData(props.ssServer, props.ssPage, jsonParamsD)
    const jfFormsData: JFFormsData | undefined = responseD.data
    if (jfFormsData !== undefined) {
      //Localisation
      const jfLocalisationRue: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'map_address_rue')
      if (jfLocalisationRue !== undefined) {
        const rue: string = jfLocalisationRue.toString()
        dataForms.localisationRue = rue
        localisation.value = rue
        let numero: string
        const jfLocalisationNumeroRue: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'map_address_numeroDeRue')
        if (jfLocalisationNumeroRue !== undefined) {
          numero = jfLocalisationNumeroRue.toString()
          dataForms.localisationNumero = numero
          localisation.value += ` ${numero}`
        }
      }

      //numéro ECA
      let jfNumeroECA: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'numero_eca')
      if (jfNumeroECA !== undefined) {
        jfNumeroECA = jfNumeroECA.toString().trim()
        dataForms.numeroECA = jfNumeroECA
        numeroECA.value = jfNumeroECA
      }

      //parcelle
      let jfParcelle: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'numero_parcelle')
      if (jfParcelle !== undefined) {
        jfParcelle = jfParcelle.toString().trim()
        dataForms.parcelle = jfParcelle
        parcelle.value = jfParcelle
      }

      //Description travaux
      const jfTravauxDescription: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'travaux_description')
      if (jfTravauxDescription !== undefined) {
        descriptionTravaux.value = jfTravauxDescription.toString()
        dataForms.descriptionTravaux = jfTravauxDescription.toString()
      }

      //Coordonnées demandeur selon requerant qui est optionnel ou proprietaire qui est obligatoire
      let jfDemandeurNom: string | number | undefined
      let jfDemandeurPrenom: string | number | undefined
      let jfDemandeurSociete: string | number | undefined
      let jfDemandeurRue: string | number | undefined
      let jfDemandeurNumero: string | number | undefined
      let jfDemandeurNpa: string | number | undefined
      let jfDemandeurLocalite: string | number | undefined
      let jfDemandeurTelephone: string | number | undefined
      let jfDemandeurEmail: string | number | undefined

      let bRequerentSaisi: boolean = false
      let jfDemandeurCoordonneeRequerent: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_adresse_requerent_choix')
      if (jfDemandeurCoordonneeRequerent !== undefined) {
        jfDemandeurCoordonneeRequerent = jfDemandeurCoordonneeRequerent.toString().trim()
        if (jfDemandeurCoordonneeRequerent === 'oui') {
          bRequerentSaisi = true
        }
      }

      if (bRequerentSaisi) {
        jfDemandeurNom = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_nom')
        jfDemandeurPrenom = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_prenom')
        jfDemandeurSociete = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_societe')
        jfDemandeurRue = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_rue')
        jfDemandeurNumero = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_numero')
        jfDemandeurNpa = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_npa')
        jfDemandeurLocalite = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_localite')
        jfDemandeurTelephone = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_telephone_mobile')
        jfDemandeurEmail = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_requerant', 'coordonnees_requerant_email')
      } else {
        jfDemandeurNom = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_proprietaire_nom')
        jfDemandeurPrenom = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_proprietaire_prenom')
        jfDemandeurSociete = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnées_proprietaite_societe') //Oui, il y a un é. Merci SOI
        jfDemandeurRue = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_rue')
        jfDemandeurNumero = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_numero')
        jfDemandeurNpa = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_npa')
        jfDemandeurLocalite = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_localite')
        jfDemandeurTelephone = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_telephone_telephone_mobile')
        jfDemandeurEmail = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_email_proprietaire')
      }

      if (jfDemandeurSociete !== undefined) {
        jfDemandeurSociete = jfDemandeurSociete.toString().trim()
        dataForms.demandeur.societe = jfDemandeurSociete
        if (jfDemandeurSociete !== '') {
          demandeur.value = jfDemandeurSociete
        }
      }
      let jfDemandeurNomPrenom: string = ''
      if (jfDemandeurNom !== undefined) {
        jfDemandeurNom = jfDemandeurNom.toString().trim()
        if (jfDemandeurNom !== '') {
          jfDemandeurNomPrenom = jfDemandeurNom
          dataForms.demandeur.nom = jfDemandeurNom
        }
      }
      if (jfDemandeurPrenom !== undefined) {
        jfDemandeurPrenom = jfDemandeurPrenom.toString().trim()
        if (jfDemandeurPrenom !== '') {
          dataForms.demandeur.prenom = jfDemandeurPrenom
          if (jfDemandeurNomPrenom !== '') {
            jfDemandeurNomPrenom += ` ${jfDemandeurPrenom}`
          } else {
            jfDemandeurNomPrenom = jfDemandeurPrenom
          }
        }
      }
      if (jfDemandeurNomPrenom !== '') {
        if (demandeur.value !== '') {
          demandeur.value += `\n${jfDemandeurNomPrenom}`
        } else {
          demandeur.value = jfDemandeurNomPrenom
        }
      }

      if (demandeur.value === '') {
        demandeur.value = '?'
      }

      let jfDemandeurRueNumero: string = ''
      if (jfDemandeurRue !== undefined) {
        jfDemandeurRue = jfDemandeurRue.toString().trim()
        if (jfDemandeurRue !== '') {
          dataForms.demandeur.rue = jfDemandeurRue
          jfDemandeurRueNumero = jfDemandeurRue
        }
      }
      if (jfDemandeurNumero !== undefined) {
        jfDemandeurNumero = jfDemandeurNumero.toString().trim()
        if (jfDemandeurNumero !== '') {
          dataForms.demandeur.numero = jfDemandeurNumero
          if (jfDemandeurRueNumero !== '') {
            jfDemandeurRueNumero += ` ${jfDemandeurNumero}`
          } else {
            jfDemandeurRueNumero = jfDemandeurNumero
          }
        }
      }
      if (jfDemandeurRueNumero !== '') {
        demandeur.value += `\n${jfDemandeurRueNumero}`
      }

      let jfDemandeurNpaLocalite: string = ''
      if (jfDemandeurNpa !== undefined) {
        jfDemandeurNpa = jfDemandeurNpa.toString().trim()
        if (jfDemandeurNpa !== '') {
          dataForms.demandeur.npa = jfDemandeurNpa
          jfDemandeurNpaLocalite = jfDemandeurNpa
        }
      }
      if (jfDemandeurLocalite !== undefined) {
        jfDemandeurLocalite = jfDemandeurLocalite.toString().trim()
        if (jfDemandeurLocalite !== '') {
          dataForms.demandeur.localite = jfDemandeurLocalite
          if (jfDemandeurNpaLocalite !== '') {
            jfDemandeurNpaLocalite += ` ${jfDemandeurLocalite}`
          } else {
            jfDemandeurNpaLocalite = jfDemandeurLocalite
          }
        }
      }
      if (jfDemandeurNpaLocalite !== '') {
        demandeur.value += `\n${jfDemandeurNpaLocalite}`
      }

      let jfDemandeurEmailTel: string = ''
      if (jfDemandeurEmail !== undefined) {
        jfDemandeurEmail = jfDemandeurEmail.toString().trim()
        if (jfDemandeurEmail !== '') {
          dataForms.demandeur.email = jfDemandeurEmail
          jfDemandeurEmailTel = jfDemandeurEmail
        }
      }
      if (jfDemandeurTelephone !== undefined) {
        jfDemandeurTelephone = jfDemandeurTelephone.toString().trim()
        if (jfDemandeurTelephone !== '') {
          dataForms.demandeur.telephone = jfDemandeurTelephone
          if (jfDemandeurEmailTel !== '') {
            jfDemandeurEmailTel += ` / ${jfDemandeurTelephone}`
          } else {
            jfDemandeurEmailTel = jfDemandeurTelephone
          }
        }
      }
      if (jfDemandeurNpaLocalite !== '') {
        demandeur.value += `\n${jfDemandeurEmailTel}`
      }

      //Coordonnées facturation
      let jfDemandeurCoordonneeFacturation: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_du_formulaire', 'coordonnees_adresse_facturation')
      if (jfDemandeurCoordonneeFacturation !== undefined) {
        jfDemandeurCoordonneeFacturation = jfDemandeurCoordonneeFacturation.toString().trim()
        if (jfDemandeurCoordonneeFacturation === 'oui') {
          //Des données pour la facturation existent
          let jfCoordFacturationNom: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_facturation', 'coordonnees_facturation_nom')
          let jfCoordFacturationPrenom: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_facturation', 'coordonnees_facturation_prenom')
          let jfCoordFacturationSociete: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_facturation', 'coordonnees_facturation_societe')
          let jfCoordFacturationRue: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_facturation', 'coordonnees_facturation_rue')
          let jfCoordFacturationNumero: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_facturation', 'coordonnees_facturation_numero')
          let jfCoordFacturationNpa: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_facturation', 'coordonnees_facturation_npa')
          let jfCoordFacturationLocalite: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_facturation', 'VAR_10')

          if (jfCoordFacturationSociete !== undefined) {
            jfCoordFacturationSociete = jfCoordFacturationSociete.toString().trim()
            dataForms.infoFacturation.societe = jfCoordFacturationSociete
            if (jfCoordFacturationSociete !== '') {
              infofacturation.value = jfCoordFacturationSociete
            }
          }
          let jfCoordFacturationNomPrenom: string = ''
          if (jfCoordFacturationNom !== undefined) {
            jfCoordFacturationNom = jfCoordFacturationNom.toString().trim()
            if (jfCoordFacturationNom !== '') {
              jfCoordFacturationNomPrenom = jfCoordFacturationNom
              dataForms.infoFacturation.nom = jfCoordFacturationNom
            }
          }
          if (jfCoordFacturationPrenom !== undefined) {
            jfCoordFacturationPrenom = jfCoordFacturationPrenom.toString().trim()
            if (jfCoordFacturationPrenom !== '') {
              dataForms.infoFacturation.prenom = jfCoordFacturationPrenom
              if (jfCoordFacturationNomPrenom !== '') {
                jfCoordFacturationNomPrenom += ` ${jfCoordFacturationPrenom}`
              } else {
                jfCoordFacturationNomPrenom = jfCoordFacturationPrenom
              }
            }
          }
          if (jfCoordFacturationNomPrenom !== '') {
            if (infofacturation.value !== '') {
              infofacturation.value += `\n${jfCoordFacturationNomPrenom}`
            } else {
              infofacturation.value = jfCoordFacturationNomPrenom
            }
          }

          if (infofacturation.value === '') {
            infofacturation.value = '?'
          }

          let jfCoordFacturationRueNumero: string = ''
          if (jfCoordFacturationRue !== undefined) {
            jfCoordFacturationRue = jfCoordFacturationRue.toString().trim()
            if (jfCoordFacturationRue !== '') {
              dataForms.infoFacturation.rue = jfCoordFacturationRue
              jfCoordFacturationRueNumero = jfCoordFacturationRue
            }
          }
          if (jfCoordFacturationNumero !== undefined) {
            jfCoordFacturationNumero = jfCoordFacturationNumero.toString().trim()
            if (jfCoordFacturationNumero !== '') {
              dataForms.infoFacturation.numero = jfCoordFacturationNumero
              if (jfCoordFacturationRueNumero !== '') {
                jfCoordFacturationRueNumero += ` ${jfCoordFacturationNumero}`
              } else {
                jfCoordFacturationRueNumero = jfCoordFacturationNumero
              }
            }
          }
          if (jfCoordFacturationRueNumero !== '') {
            infofacturation.value += `\n${jfCoordFacturationRueNumero}`
          }

          let jfCoordFacturationNpaLocalite: string = ''
          if (jfCoordFacturationNpa !== undefined) {
            jfCoordFacturationNpa = jfCoordFacturationNpa.toString().trim()
            if (jfCoordFacturationNpa !== '') {
              dataForms.infoFacturation.npa = jfCoordFacturationNpa
              jfCoordFacturationNpaLocalite = jfCoordFacturationNpa
            }
          }
          if (jfCoordFacturationLocalite !== undefined) {
            jfCoordFacturationLocalite = jfCoordFacturationLocalite.toString().trim()
            if (jfCoordFacturationLocalite !== '') {
              dataForms.infoFacturation.localite = jfCoordFacturationLocalite
              if (jfCoordFacturationNpaLocalite !== '') {
                jfCoordFacturationNpaLocalite += ` ${jfCoordFacturationLocalite}`
              } else {
                jfCoordFacturationNpaLocalite = jfCoordFacturationLocalite
              }
            }
          }
          if (jfCoordFacturationNpaLocalite !== '') {
            infofacturation.value += `\n${jfCoordFacturationNpaLocalite}`
          }
        }
      }

      //Fichiers
      const ajoutFichiers = (ajfFichiers: string[], nomBase: string): void => {
        ajfFichiers.forEach((idfile: string, index: number) => {
          if (idfile.trim() === '') {
            return
          }

          const nomfile: string = ajfFichiers.length > 1 ? `${nomBase} ${index + 1}` : nomBase

          nombreFichiers.value++
          const fichier: ListeFichiers = { idfichier: idfile, nomfichier: nomfile }
          listeFichiers.value.push(fichier)
          idsfichier.value.push(idfile)
          const tmpFichier: Fichier = { idjf: idfile, filename: nomfile, b64content: '', mimetype: '', size: 0, sha256: '', infoDoublon: '', idFamille: 0, idDocGo: 0, docGoLie: 1 }
          dataForms.fichiers.push(tmpFichier)
        })
      }

      let ajfFichiers: string[] = []

      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'accord_administration_ppe_copropriete_document')
      ajoutFichiers(ajfFichiers, 'Accord de l\'administration PPE/copropriété')

      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'upload_cas_un')
      ajoutFichiers(ajfFichiers, 'Préavis DGIP-Monuments et sites')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'upload_cas_deux')
      ajoutFichiers(ajfFichiers, 'Préavis délégation à la protection du patrimoine')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'document_arbre_pres_chantier')
      ajoutFichiers(ajfFichiers, 'Préavis Service des parcs et domaines')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_panneaux_solaire_document')
      ajoutFichiers(ajfFichiers, 'Formulaire panneaux solaires')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_panneaux_solaire_document_deux')
      ajoutFichiers(ajfFichiers, 'Documentation technique panneaux solaires')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_assainissement_terrain_pollue_dioxine_preavis_favorable_dge')
      ajoutFichiers(ajfFichiers, 'Préavis DGE dioxine')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_assainissement_terrain_pollue_dioxine_plan_elimination_dechets')
      ajoutFichiers(ajfFichiers, 'Plan d\'élimination des déchets')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_remplacement_producteur_chaleur')
      ajoutFichiers(ajfFichiers, 'Préavis SIL - Division énergie - Remplacement producteur de chaleur')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_changement_renovation_toiture_document')
      ajoutFichiers(ajfFichiers, 'Préavis SIL - Division énergie - Rénovation toiture')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_changement_affectation_logement_document')
      ajoutFichiers(ajfFichiers, 'Préavis Office communal du logement - Changement affectation logement')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_transformation_modifiant_nombre_logement')
      ajoutFichiers(ajfFichiers, 'Préavis Office communal du logement - Transformation modifiant le nombre de logements')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_installation_pompes_chaleur')
      ajoutFichiers(ajfFichiers, 'Formulaire annonce pompe à chaleur')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'type_travaux', 'type_travaux_installation_pompes_chaleur_deux')
      ajoutFichiers(ajfFichiers, 'Documentation technique pompe à chaleur')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'document_a_joindre_a_la_demande', 'devis_travaux_document')
      ajoutFichiers(ajfFichiers, 'Devis travaux')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'document_a_joindre_a_la_demande', 'photographie_avant_travaux_document')
      ajoutFichiers(ajfFichiers, 'Photographie avant travaux')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'document_a_joindre_a_la_demande', 'extrait_cadastral_document')
      ajoutFichiers(ajfFichiers, 'Extrait cadastral')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'document_a_joindre_a_la_demande', 'plans_coupes_esquisses_document')
      ajoutFichiers(ajfFichiers, 'Plans, coupes, esquisses')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'document_a_joindre_a_la_demande', 'diagnostic_amiante_document')
      ajoutFichiers(ajfFichiers, 'Diagnostic amiante')
      
      ajfFichiers = getIdFileByGroupAndVarId(jfFormsData, 'document_a_joindre_a_la_demande', 'autre_document_a_upload')
      ajoutFichiers(ajfFichiers, 'Autre document')

      console.log('fichiers', dataForms.fichiers)
    }   

    if (dataForms.numeroDemande === '' || dataForms.status === '') {
      //Appel direct du formulaire, pas de uuid et status retourné avec la methode data de l'API (SOI...!!!)
      const jsonParamsL: string = `{"pagesize":500,"offset":0,"demandestatus":40}`
      const responseL: ApiResponseJFFL = await getJFFormsListe(props.ssServer, props.ssPageListe, jsonParamsL)
      //console.log("responseL de data", JSON.stringify(responseL))
      if (responseL.data !== undefined && typeof responseL.data !== "string") {
        const result = getUUIDAndStatus(responseL.data, dataForms.idDemande)
        dataForms.numeroDemande = result.uuid ?? ''
        dataForms.status = result.status ?? ''
      }
    }
    console.log('emit dataForms')
    emit('dataForms', JSON.stringify(dataForms))
  }
}

const voirFichier = (idFichier: string): void => {
  window.open(`${props.ssServer}/goeland/jaxforms/jffileattachmentview_annoncetravaux.php?idfileattachment=${idFichier}`)
}
</script>
<style scoped>
#divErreur {
  background-color: lightsalmon;
  margin-left: 5px;
  margin-right: 5px;
  margin-top: 0px;
  padding: 5px;
  border-style: solid;
  border-width: thin;
  border-color: black;
  border-radius: 20px;
  white-space: pre-line;
  /* Convertit les \n en sauts de ligne */
}
</style>