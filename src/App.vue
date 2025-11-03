<template>
      <AnnonceTravauxListe :ssServer="ssServer"></AnnonceTravauxListe>
</template>

<script setup lang="ts">
import { ref } from 'vue'
  const ssServer = ref<string>('')
if (import.meta.env.DEV) {
  ssServer.value = 'https://mygolux.lausanne.ch'
}
</script>
