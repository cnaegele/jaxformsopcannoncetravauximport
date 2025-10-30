<template>
  <v-container>
    <!-- Dialog pour afficher les détails -->
    <v-dialog v-model="dialogFormsData" max-width="1200px">
      <v-card>
        <v-card-title class="d-flex align-center pe-2">
          <v-icon icon="mdi-file-document" class="me-2"></v-icon>
          Détails de la demande
          
          <v-spacer></v-spacer>
          
          <v-btn
            icon="mdi-close"
            variant="text"
            @click="dialogFormsData = false"
          ></v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text class="pa-4">
          <AnnonceTravauxData v-if="selectedItem" :id="selectedItem.id" :ssServer="ssServer" />
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="text"
            @click="dialogFormsData = false"
          >
            Fermer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-card>
      <v-card-title class="d-flex align-center pe-2">
        <v-icon icon="mdi-file-document-multiple" class="me-2"></v-icon>
        Liste des demandes

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
          <a v-if="item.emaildemandeur !== '?'" :href="`mailto:${item.emaildemandeur}`" class="text-decoration-none">
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
</template>

<script setup lang="ts">
import type { JFFormsListe, ApiResponseJFFL, Row } from '@/axioscalls.js'
import type { JFFormsData, ApiResponseJFFD } from '@/axioscalls.js'

import { ref } from 'vue'
import { getJFFormsListe, getListeFieldValue } from '@/axioscalls.js'
import { getJFFormsData, getDataContentByGroupAndVarId } from '@/axioscalls.js'

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
}
const props = withDefaults(defineProps<Props>(), {
  pagesize: 10,
  offset: 0,
  ssServer: '',
  ssPageListe: '/goeland/jaxforms/axios/jfsearch_annoncetravaux.php',
  ssPageData: '/goeland/jaxforms/axios/jfdata_annoncetravaux.php'
})

let jfFormsListe: JFFormsListe
const pageSize = ref<number>(props.pagesize)
const hasNext = ref<boolean>(false)
const totalSize = ref<number>(0)
const size = ref<number>(0)
const offset = ref<number>(props.offset)

const affiche = ref<string>('')
const affFormsListe = ref<GoFormsListe[]>([])

const jsonParamsL: string = `{"pagesize":${pageSize.value},"offset":${offset.value}}`
const responseL: ApiResponseJFFL = await getJFFormsListe(props.ssServer, props.ssPageListe, jsonParamsL)
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
  console.log(affFormsListe.value)
}

const search = ref('')
const dialogFormsData = ref(false)
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
</script>
