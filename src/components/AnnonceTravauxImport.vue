<template>
    <CallerIsInGroup :ssServer="ssServer" nomgroupe="OPCAnnonceTravauxImport"
        @calleringroup="receptionCallerInGroupGoelandManager"></CallerIsInGroup>
    <div v-if="messageErreur !== ''" id="divErreur">{{ messageErreur }}</div>
    <div v-if="messageErreur === ''">
        salut de préparation import
    </div>
</template>

<script setup lang="ts">
import type { ApiResponseIG } from './CallerIsInGroup.vue'
import { ref, onMounted } from 'vue'

interface Props {
    jsonDataForms: string
    ssServer?: string
    ssPage?: string
}

const props = withDefaults(defineProps<Props>(), {
    ssServer: '',
    ssPage: '/goeland/jaxforms/axios/jfdata_annoncetravaux_prepare_import.php'
})

//Droits caller
const bOPCAnnonceTravauxImport = ref<boolean>(false)

const messageErreur = ref<string | undefined>('')

onMounted(() => {
    console.log(props.jsonDataForms)
})

const receptionCallerInGroupGoelandManager = (jsonData: string) => {
    const retCallerInGroup = ref<ApiResponseIG>(JSON.parse(jsonData))
    if (retCallerInGroup.value.success && retCallerInGroup.value.data !== undefined) {
        bOPCAnnonceTravauxImport.value = retCallerInGroup.value.data.isingroup
    }
    if (!bOPCAnnonceTravauxImport.value) {
        messageErreur.value = "Page réservée aux employés du groupe OPCAnnonceTravauxImport"
    }
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

#divMessage {
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