<template>
  <div v-if="messageErreur === ''">
    <AnnonceTravauxListe v-if="idjf === ''" :ssServer="ssServer" :demandestatus="demandesStatus"></AnnonceTravauxListe>
    <AnnonceTravauxData v-else-if="bdata" :id="idjf" :uuid="uuidjf" status="" :ssServer="ssServer"
      @dataForms="receptionDataForms" />
    <div v-if="bimport">
      <v-card>
        <v-card-title class="d-flex align-center pe-2">
          <v-icon icon="mdi-file-document" class="me-2"></v-icon>
          Préparation import de la demande {{ uuidjf }} dans une affaire OPC - Annonce travaux&nbsp;<small>(version {{ version }})</small>

          <v-spacer></v-spacer>

          <v-btn icon="mdi-close" variant="text" @click="idjf = ''"></v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text class="pa-4">
          <AnnonceTravauxImport v-if="bimport" :jsonDataForms="jsonDataForms" :ssServer="ssServer"
            @affaireimport="receptionAffaireImport" />
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" variant="text" @click="idjf = ''">
            Fermer sans importer
          </v-btn>
        </v-card-actions>
      </v-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { DataForms } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { stringToPositiveInteger } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { ref } from 'vue'
import packageJson from '../package.json'
const ssServer = ref<string>('')
if (import.meta.env.DEV) {
  ssServer.value = 'https://mygolux.lausanne.ch'
}

const version = ref<string>(packageJson.version)
const jsonDataForms = ref<string>('')
const bdata = ref<boolean>(true)
const bimport = ref<boolean>(false)
const messageErreur = ref<string>('')

const urlParams = new URLSearchParams(window.location.search)

//Possibilité de passer le id et uuid du formulaire en paramètre
const idjf = ref<string>('')
const prmIdJF = urlParams.get('id')
if (prmIdJF !== null) {
  idjf.value = prmIdJF
}
const uuidjf = ref<string>('')
const prmUuIdJF = urlParams.get('uuid')
if (prmUuIdJF !== null) {
  if (prmUuIdJF !== '') {
    const iUuIdJF: number | null = stringToPositiveInteger(prmUuIdJF)
    if (iUuIdJF !== null) {
      uuidjf.value = prmUuIdJF
    } else {
      messageErreur.value = 'Paramètre uuid (numéro de formulaire) invalide. Doit être vide ou numérique'
    }
  }
}

//Possibilité de passer le status en paramètre
const demandesStatus = ref<number>(40) //status traité
const prmDemandesStatus = urlParams.get('status')
if (prmDemandesStatus !== null) {
  const iDemandesStatus: number | null = stringToPositiveInteger(prmDemandesStatus)
  if (iDemandesStatus !== null) {
    demandesStatus.value = iDemandesStatus
  }
}

const receptionDataForms = (receptedJsonDataForms: string) => {
  jsonDataForms.value = receptedJsonDataForms
  if (uuidjf.value === '') {
    const dataForms: DataForms = JSON.parse(jsonDataForms.value)
    uuidjf.value = dataForms.numeroDemande
  }
  bdata.value = false
  bimport.value = true
}

const receptionAffaireImport = (sjson: string) => {
  const oRecep: { "idjaxformsdemande": string, "idaffaire": string } = JSON.parse(sjson)
  const idAffaire = oRecep.idaffaire
  document.location.href = `/goeland/affaire2/affaire_data.php?idaffaire=${idAffaire}`
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
