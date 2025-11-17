<template>
  <AnnonceTravauxListe :ssServer="ssServer" :demandestatus="demandesStatus"></AnnonceTravauxListe>
</template>

<script setup lang="ts">
import { stringToPositiveInteger } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { ref } from 'vue'
const ssServer = ref<string>('')
if (import.meta.env.DEV) {
  ssServer.value = 'https://mygolux.lausanne.ch'
}

//Possibilité de passer le status en paramètre
const demandesStatus = ref<number>(40) //status traité
const urlParams = new URLSearchParams(window.location.search)
const prmDemandesStatus = urlParams.get('status')
if (prmDemandesStatus !== null) {
  const iDemandesStatus: number | null = stringToPositiveInteger(prmDemandesStatus) 
  if (iDemandesStatus !== null) {
     demandesStatus.value = iDemandesStatus
  }
}
</script>
