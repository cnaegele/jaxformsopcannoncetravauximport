<template>
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

        <v-col cols="12" md="6">
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


      <v-divider class="my-4"></v-divider>

      <div v-if="nombreFichiers > 0">
        <h3 class="text-subtitle-1 mb-3">
          <v-icon icon="mdi-paperclip" size="small" class="me-1"></v-icon>
          Fichiers joints
        </h3>

        <v-list density="compact" class="bg-transparent">
          <v-list-item v-for="(idfichier, index) in idsfichier" :key="idfichier" @click="voirFichier(idfichier)"
            class="mb-2" rounded border>
            <template v-slot:prepend>
              <v-avatar color="primary" size="36">
                <v-icon icon="mdi-file-eye" size="20"></v-icon>
              </v-avatar>
            </template>

            <v-list-item-title>
              Fichier {{ index + 1 }}
            </v-list-item-title>

            <v-list-item-subtitle class="text-caption">
              ID: {{ idfichier }}
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
</template>

<script setup lang="ts">
import type { JFFormsData, ApiResponseJFFD, Group } from '@/axioscalls.ts'
import type { DataForms, Fichier } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { getJFFormsData, getDataContentByGroupAndVarId } from '@/axioscalls.ts'
import { ref, onMounted } from 'vue'

interface Props {
  id: string
  ssServer?: string
  ssPage?: string
}

const props = withDefaults(defineProps<Props>(), {
  ssServer: '',
  ssPage: '/goeland/jaxforms/axios/jfdata_annoncetravaux.php'
})

const localisation = ref<string>('?')
const numeroECA = ref<string>('')
const parcelle = ref<string>('')
const descriptionTravaux = ref<string>('?')
const demandeur = ref<string>('')
const nombreFichiers = ref<number>(0)
const idsfichier = ref<string[]>([])

let dataForms: DataForms = {idDemande: '', demandeur: {}, fichiers: []}

const emit = defineEmits<{
  (e: 'dataForms', jsonData: string): void
}>()

onMounted(() => {
  loadData();
})

const loadData = async () => {
  dataForms.idDemande = props.id
  const jsonParamsD: string = `{"idformselement":"${props.id}"}`
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
    const jfTravauxDescription: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'information_projet', 'travaux_description')
    if (jfTravauxDescription !== undefined) {
      descriptionTravaux.value = jfTravauxDescription.toString()
      dataForms.descriptionTravaux = jfTravauxDescription.toString()
    }

    //Coordonnées demandeur
    let jfDemandeurNom: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_proprietaire_nom')
    let jfDemandeurPrenom: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_proprietaire_prenom')
    let jfDemandeurSociete: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_proprietaire_societe')
    let jfDemandeurRue: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_rue')
    let jfDemandeurNumero: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_numero')
    let jfDemandeurNpa: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_npa')
    let jfDemandeurLocalite: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_localite')
    let jfDemandeurTelephone: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_telephone_telephone_mobile')
    let jfDemandeurEmail: string | number | undefined = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_email_proprietaire')

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

    //Fichiers
    const gFichiers: Group | undefined = jfFormsData.data.group.find(g => g.id === "GRP_3")
    if (gFichiers !== undefined) {
      const vars = Array.isArray(gFichiers.var) ? gFichiers.var : [gFichiers.var]
      const nbrPotentielFichiers: number = vars.length
      for (let i: number = 0; i < nbrPotentielFichiers; i++) {
        const idf: string | number | undefined = vars[i]?.content
        if (idf !== undefined) {
          const idfile: string = idf.toString()
          if (idfile.trim() !== '') {
            nombreFichiers.value++
            idsfichier.value.push(idf.toString())
            const tmpFichier: Fichier = {idjf: idf.toString(), b64content: '', mimetype: '', size: 0, sha256: '', infoDoublon: '', idFamille: 0, idDocGo: 0 }
            dataForms.fichiers.push(tmpFichier)
          }
        }
      }
    }
  }
  emit('dataForms', JSON.stringify(dataForms))
}

const voirFichier = (idFichier: string): void => {
  window.open(`${props.ssServer}/goeland/jaxforms/jffileattachmentview_annoncetravaux.php?idfileattachment=${idFichier}`)
}

</script>
