<template>
  <CallerInfo :ssServer="ssServer" @callerinfo="receptionCallerInfo"></CallerInfo>
  <v-app>
    <v-main>
      <v-app-bar color="primary" prominent density="compact" app>
        <v-toolbar-title>Annonces travaux. Import demande jaxForms vers affaire goéland</v-toolbar-title>

        <v-spacer></v-spacer>
        <div style="position: absolute; right: 16px;">
          Utilisateur: {{ callerInformation?.prenom }} {{ callerInformation?.nom }} ({{
            callerInformation?.login }}) - {{ callerInformation?.unite }}
        </div>
      </v-app-bar>

      <v-container>
        <!-- Dialog pour afficher les détails -->
        <v-dialog v-model="dialogFormsData" max-width="1200px">
          <v-card>
            <v-card-title class="d-flex align-center pe-2">
              <v-icon icon="mdi-file-document" class="me-2"></v-icon>
              Détails de la demande

              <v-spacer></v-spacer>

              <v-btn icon="mdi-close" variant="text" @click="dialogFormsData = false"></v-btn>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pa-4">
              <AnnonceTravauxData v-if="selectedItem" :id="selectedItem.id" :ssServer="ssServer"
                @dataForms="receptionDataForms" />
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" variant="text" @click="prepareImport()">
                Préparation import
              </v-btn>
              <v-btn color="primary" variant="text" @click="dialogFormsData = false" class="ml-2">
                Fermer
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Dialog pour préparation import -->
        <v-dialog v-model="dialogFormsImport" max-width="1200px">
          <v-card>
            <v-card-title class="d-flex align-center pe-2">
              <v-icon icon="mdi-file-document" class="me-2"></v-icon>
              Préparation import de la demande dans une affaire OPC - Annonce travaux

              <v-spacer></v-spacer>

              <v-btn icon="mdi-close" variant="text" @click="dialogFormsImport = false"></v-btn>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pa-4">
              <AnnonceTravauxImport :jsonDataForms="jsonDataForms" :ssServer="ssServer" @affaireimport="receptionAffaireImport" />
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" variant="text" @click="dialogFormsImport = false">
                Fermer sans importer
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Pendant le chargement -->
        <div v-if="jfFormsListeDataLoading" class="d-flex justify-center align-center" style="min-height: 200px;">
          <div class="text-center">
            <v-progress-circular indeterminate color="primary" :size="40"></v-progress-circular>
            <p class="mt-4">Chargement des données en cours...</p>
          </div>
        </div>

        <v-card v-if="jfFormsListeLoaded">
          <v-card-title class="d-flex align-center pe-2">
            <v-icon icon="mdi-file-document-multiple" class="me-2"></v-icon>
            Liste des annonces de travaux pas importées dans goéland

            <v-spacer></v-spacer>

            <v-text-field v-model="search" density="compact" label="Rechercher" prepend-inner-icon="mdi-magnify"
              variant="outlined" flat hide-details single-line clearable></v-text-field>
          </v-card-title>

          <v-divider></v-divider>

          <v-data-table :headers="headers" :items="affFormsListe" :search="search" item-value="id"
            items-per-page-text="Lignes par page" :items-per-page="10">
            <template v-slot:item.localisation="{ item }">
              <div class="text-truncate" style="max-width: 250px;">
                <v-icon icon="mdi-map-marker" size="small" class="me-1"></v-icon>
                {{ item.localisation }}
              </div>
            </template>

            <template v-slot:item.descriptiontravaux="{ item }">
              <v-tooltip location="top">
                <template v-slot:activator="{ props }">
                  <div v-bind="props" class="text-truncate" style="max-width: 300px;">
                    {{ item.descriptiontravaux }}
                  </div>
                </template>
                <span>{{ item.descriptiontravaux }}</span>
              </v-tooltip>
            </template>

            <template v-slot:item.created="{ item }">
              <v-chip size="small" color="blue-grey" variant="tonal">
                {{ formatDate(item.created) }}
              </v-chip>
            </template>

            <template v-slot:item.lastupdate="{ item }">
              <v-chip size="small" color="green" variant="tonal">
                {{ formatDate(item.lastupdate) }}
              </v-chip>
            </template>

            <template v-slot:item.emaildemandeur="{ item }">
              <a v-if="item.emaildemandeur !== '?'" :href="`mailto:${item.emaildemandeur}`"
                class="text-decoration-none">
                <v-icon icon="mdi-email" size="small" class="me-1"></v-icon>
                {{ item.emaildemandeur }}
              </a>
              <span v-else class="text-grey">{{ item.emaildemandeur }}</span>
            </template>

            <template v-slot:item.actions="{ item }">
              <v-btn icon="mdi-eye" variant="text" size="small" @click="viewDetails(item)"></v-btn>
            </template>

            <template v-slot:no-data>
              <v-alert type="info" variant="tonal">
                Aucune demande trouvée
              </v-alert>
            </template>
          </v-data-table>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import type { ApiResponseUI, UserInfo } from './CallerInfo.vue'
import type { JFFormsListe, ApiResponseJFFL, Row } from '@/axioscalls.ts'
import type { JFFormsData, ApiResponseJFFD } from '@/axioscalls.ts'
import type { ApiResponseNumber } from '@/axioscalls.ts'

import { ref, onMounted } from 'vue'
import { getJFFormsListe, getListeFieldValue } from '@/axioscalls.ts'
import { getJFFormsData, getDataContentByGroupAndVarId } from '@/axioscalls.ts'
import { getIdAffaireGoeland } from '@/axioscalls.ts'

interface GoFormsListe {
  id?: string
  created?: string
  lastupdate?: string
  localisation?: string
  descriptiontravaux?: string
  emaildemandeur?: string
}

interface Props {
  pagesize?: number
  offset?: number
  ssServer?: string
  ssPageListe?: string
  ssPageData?: string
  ssPageIdAffGo?: string

}
const props = withDefaults(defineProps<Props>(), {
  pagesize: 100,
  offset: 0,
  ssServer: '',
  ssPageListe: '/goeland/jaxforms/axios/jfsearch_annoncetravaux.php',
  ssPageData: '/goeland/jaxforms/axios/jfdata_annoncetravaux.php',
  ssPageIdAffGo: '/goeland/jaxforms/axios/idaffaire_par_idjaxforms.php'
})

//Data caller
const callerInformation = ref<UserInfo | null | undefined>(null)

const jfFormsListeDataLoading = ref<boolean>(false);
const jfFormsListeLoaded = ref<boolean>(false);
let jfFormsListe: JFFormsListe
const pageSize = ref<number>(props.pagesize)
const hasNext = ref<boolean>(false)
const totalSize = ref<number>(0)
const size = ref<number>(0)
const offset = ref<number>(props.offset)

const jsonDataForms = ref<string>('')
const affFormsListe = ref<GoFormsListe[]>([])

onMounted(() => {
  loadData();
})

const loadData = async () => {
  jfFormsListeDataLoading.value = true
  const jsonParamsL: string = `{"pagesize":${pageSize.value},"offset":${offset.value}}`
  const responseL: ApiResponseJFFL = await getJFFormsListe(props.ssServer, props.ssPageListe, jsonParamsL)
  jfFormsListeLoaded.value = true
  console.log(responseL.data)
  if (responseL.data !== undefined) {
    jfFormsListe = responseL.data
    pageSize.value = jfFormsListe.info.pageSize
    hasNext.value = jfFormsListe.info.hasNext
    totalSize.value = jfFormsListe.info.totalSize
    size.value = jfFormsListe.info.size
    offset.value = jfFormsListe.info.offset
    for (let i = 0; i < size.value; i++) {
      const therow: Row | undefined = jfFormsListe.row[i]
      if (therow !== undefined) {
        const idForms: string | undefined = getListeFieldValue(therow, 'AccessID')
        if (idForms !== undefined) {
          //Si une affaire goéland existe pour cet idForms, on affiche pas la ligne
          const responseIdGo: ApiResponseNumber = await getIdAffaireGoeland(props.ssServer, props.ssPageIdAffGo, idForms)
          if (responseIdGo.data === 0 || responseIdGo.data === undefined || responseIdGo.data === null) {
            const jsonParamsD: string = `{"idformselement":"${idForms}"}`
            const responseD: ApiResponseJFFD = await getJFFormsData(props.ssServer, props.ssPageData, jsonParamsD)
            const jfFormsData: JFFormsData | undefined = responseD.data
            let localisationRue: string | number | undefined
            let localisationNumRue: string | number | undefined
            let descriptionTravaux: string | number | undefined
            let emailDemandeur: string | number | undefined
            if (jfFormsData !== undefined) {
              localisationRue = getDataContentByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'map_address_rue') ?? '?'
              localisationNumRue = getDataContentByGroupAndVarId(jfFormsData, 'Localisation_objet_concerne_travaux', 'map_address_numeroDeRue') ?? ''
              descriptionTravaux = getDataContentByGroupAndVarId(jfFormsData, 'information_projet', 'travaux_description') ?? '?'
              emailDemandeur = getDataContentByGroupAndVarId(jfFormsData, 'coordonnees_demandeur', 'coordonnees_email_proprietaire') ?? '?'
            }
            //console.log(responseD)
            const goFormsListe: GoFormsListe = {
              id: getListeFieldValue(therow, 'AccessID') ?? '?',
              created: getListeFieldValue(therow, 'Created') ?? '',
              lastupdate: getListeFieldValue(therow, 'LastUpdate'),
              localisation: `${localisationRue?.toString()} ${localisationNumRue?.toString()}`,
              descriptiontravaux: descriptionTravaux?.toString(),
              emaildemandeur: emailDemandeur?.toString()
            }
            affFormsListe.value.push(goFormsListe)
          }
        }
      }
    }
    jfFormsListeDataLoading.value = false
  }
}

const search = ref('')
const dialogFormsData = ref(false)
const dialogFormsImport = ref(false)
const selectedItem = ref<GoFormsListe | null>(null)

const headers = [
  { title: 'Localisation', key: 'localisation', sortable: true },
  { title: 'Description des travaux', key: 'descriptiontravaux', sortable: true },
  { title: 'Email demandeur', key: 'emaildemandeur', sortable: true },
  { title: 'Créé le', key: 'created', sortable: true },
  { title: 'Dernière mise à jour', key: 'lastupdate', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' as const }
]

const formatDate = (dateString?: string) => {
  if (dateString !== undefined) {
    if (dateString === '') {
      return ''
    }
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-CH', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const viewDetails = (item: GoFormsListe) => {
  selectedItem.value = item
  dialogFormsData.value = true
}

const receptionDataForms = (receptedJsonDataForms: string) => {
  jsonDataForms.value = receptedJsonDataForms
}

const receptionAffaireImport = (sjson: string) => {
  dialogFormsImport.value = false
  const oRecep: {"idjaxformsdemande": string, "idaffaire": string} = JSON.parse(sjson)
  const idAffaire = oRecep.idaffaire
  const idjf = oRecep.idjaxformsdemande

  const index: number = affFormsListe.value.findIndex(obj => obj.id === idjf);
  if (index !== -1) {
    affFormsListe.value.splice(index, 1);
  }

  window.open(`${props.ssServer}/goeland/affaire2/affaire_data.php?idaffaire=${idAffaire}`)
}

const prepareImport = () => {
  dialogFormsData.value = false
  dialogFormsImport.value = true
}

const receptionCallerInfo = (jsonData: string) => {
  const retCallerInformation = ref<ApiResponseUI>(JSON.parse(jsonData))
  if (retCallerInformation.value.success) {
    callerInformation.value = retCallerInformation.value.data
  }
}
</script>
