<template>
  <v-card>
    <v-card-text>
      <v-row>
        <v-col cols="12" md="6">
          <v-list-item density="compact">
            <template v-slot:prepend>
              <v-icon icon="mdi-identifier" color="primary"></v-icon>
            </template>
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
            <template v-slot:prepend>
              <v-icon icon="mdi-file-multiple" color="primary"></v-icon>
            </template>
            <v-list-item-title class="text-body-2 text-grey">
              Nombre de fichiers
            </v-list-item-title>
            <v-list-item-subtitle class="text-body-1 font-weight-medium">
              {{ nombreFichiers }}
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
          <v-list-item
            v-for="(idfichier, index) in idsfichier"
            :key="idfichier"
            @click="voirFichier(idfichier)"
            class="mb-2"
            rounded
            border
          >
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
              <v-btn
                icon="mdi-eye"
                variant="text"
                color="primary"
                size="small"
              ></v-btn>
            </template>
          </v-list-item>
        </v-list>
      </div>

      <v-alert
        v-else
        type="info"
        variant="tonal"
        density="compact"
        icon="mdi-information"
        class="mt-2"
      >
        Aucun fichier joint à cette demande
      </v-alert>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import type { JFFormsData, ApiResponseJFFD, Group } from '@/axioscalls.js'
import { getJFFormsData, getDataContentByGroupAndVarId } from '@/axioscalls.js'
import { ref } from 'vue'

interface Props {
  id: string
  ssServer?: string
  ssPage?: string
}

const props = withDefaults(defineProps<Props>(), {
  ssServer: '',
  ssPage: '/goeland/jaxforms/axios/jfdata_annoncetravaux.php'
})

const affiche = ref<string>('')
const nombreFichiers = ref<number>(0)
const idsfichier = ref<string[]>([])
const jsonParamsD: string = `{"idformselement":"${props.id}"}`
        const responseD: ApiResponseJFFD = await getJFFormsData(props.ssServer, props.ssPage, jsonParamsD)
        const jfFormsData: JFFormsData | undefined = responseD.data
        if (jfFormsData !== undefined) {
            const gFichiers: Group | undefined = jfFormsData.data.group.find(g => g.id === "GRP_3")
            if (gFichiers !== undefined) {
                const vars = Array.isArray(gFichiers.var) ? gFichiers.var : [gFichiers.var]
                nombreFichiers.value = vars.length
                for (let i: number =0; i<nombreFichiers.value; i++) {
                    const idf: string | number | undefined = vars[i]?.content
                    if (idf !== undefined) {
                        idsfichier.value.push(idf.toString())    
                    }
                }   
            }
            
        }
        affiche.value = JSON.stringify(jfFormsData)

    const voirFichier = (idFichier: string) : void => {
        window.open(`${props.ssServer}/goeland/jaxforms/jffileattachmentview_annoncetravaux.php?idfileattachment=${idFichier}`)
    } 

</script>
