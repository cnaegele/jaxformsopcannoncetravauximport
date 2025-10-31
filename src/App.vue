<template>
  <v-app>
    <v-main>
      <AnnonceTravauxListe :ssServer="ssServer"></AnnonceTravauxListe>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'
  const ssServer = ref<string>('')
if (import.meta.env.DEV) {
  ssServer.value = 'https://mygolux.lausanne.ch'
}
</script>
