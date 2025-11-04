<template>
    <CallerIsInGroup :ssServer="ssServer" nomgroupe="OPCAnnonceTravauxImport"
        @calleringroup="receptionCallerInGroupGoelandManager"></CallerIsInGroup>
    <div v-if="messageErreur !== ''" id="divErreur">{{ messageErreur }}</div>

    <div v-else>
        <div v-if="jfFormsImportDataLoading" class="d-flex justify-center align-center" style="min-height: 200px;">
            <div class="text-center">
                <v-progress-circular indeterminate color="primary" :size="40"></v-progress-circular>
                <p class="mt-4">Chargement des données et contrôle goéland en cours...</p>
            </div>
        </div>
        <v-card v-else>
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="8">
                        <v-text-field v-model="nomAffaire" label="Nom" :maxlength="100" counter="100"
                            variant="outlined"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">{{ nomAffaireRemarqueGo }}</v-col>
                </v-row>
                <v-row>
                    <v-col cols="12" md="12">
                        <v-text-field v-model="descriptionAffaire" label="Description" :maxlength="100" counter="100"
                            variant="outlined"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12" md="12">{{ liensBatimentsParcelles }}</v-col>   
                </v-row>
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup lang="ts">
import type { ApiResponseIG } from './CallerIsInGroup.vue'
import type { ApiResponseIFD } from '@/axioscalls.ts'
import type { DataForms, Fichier } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { getImportFormsData } from '@/axioscalls.ts'
import { ref, onMounted } from 'vue'

interface Props {
    jsonDataForms: string
    ssServer?: string
    ssPage?: string
}

const props = withDefaults(defineProps<Props>(), {
    ssServer: '',
    ssPage: '/goeland/jaxforms/axios/jfprepareimport_annoncetravaux.php'
})

//Droits caller
const bOPCAnnonceTravauxImport = ref<boolean>(false)

const messageErreur = ref<string | undefined>('')
const jfFormsImportDataLoading = ref<boolean>(false);
const nomAffaire = ref<string>('');
const nomAffaireRemarqueGo = ref<string>('');
const descriptionAffaire = ref<string>('');
const liensBatimentsParcelles = ref<string>('');

onMounted(() => {
    loadDataImport()
})

const loadDataImport = async () => {
    jfFormsImportDataLoading.value = true
    console.log('props.jsonDataForms', props.jsonDataForms)
    const responseID: ApiResponseIFD = await getImportFormsData(props.ssServer, props.ssPage, props.jsonDataForms)
    if (responseID.data !== undefined) {
        const dataImportPropose: DataForms = responseID.data
        console.log(dataImportPropose)

        //Nom affaire selon adresse
        let rueAdresseNomAffaire: string = ''
        let localisationRue: string = ''
        let localisationNumero: string = ''
        let idRueGo: number = 0
        let idAdresseGo: number = 0
        if (dataImportPropose.rueAdresseNomAffaire !== undefined) {
            rueAdresseNomAffaire = dataImportPropose.rueAdresseNomAffaire
        }
        if (dataImportPropose.localisationRue !== undefined) {
            localisationRue = dataImportPropose.localisationRue
        }
        if (dataImportPropose.localisationNumero !== undefined) {
            localisationNumero = dataImportPropose.localisationNumero
        }
        if (dataImportPropose.idRueGo !== undefined) {
            idRueGo = dataImportPropose.idRueGo
        }
        if (dataImportPropose.idAdresseGo !== undefined) {
            idAdresseGo = dataImportPropose.idAdresseGo
        }
        if (rueAdresseNomAffaire !== '') {
            nomAffaire.value = rueAdresseNomAffaire
            if (localisationNumero !== '' && idAdresseGo === 0) {
                nomAffaireRemarqueGo.value = 'adresse introuvable dans goéland'    
            }    
        } else {
            if (localisationRue !== '') {
                nomAffaireRemarqueGo.value = 'rue introuvable dans goéland'
                nomAffaire.value = localisationRue
                if (localisationNumero !== '') {
                    nomAffaire.value += ` ${localisationNumero}`    
                }
            }
        }

        //Description
        if (dataImportPropose.descriptionTravaux !== undefined) {
            descriptionAffaire.value = dataImportPropose.descriptionTravaux
        }

        //Liens bâtiments, parcelles
        let idsBatimentGo: string = '', nbrBatiment: number = 0
        let idsParcelleGo: string = '', nbrParcelle: number = 0
        if (dataImportPropose.idsBatimentGo !== undefined) {
            idsBatimentGo = dataImportPropose.idsBatimentGo.toString()
        }
        if (dataImportPropose.idsParcelleGo !== undefined) {
            idsParcelleGo = dataImportPropose.idsParcelleGo.toString()
        }
        if (idsBatimentGo === '' && idsParcelleGo === '') {
            liensBatimentsParcelles.value = 'aucun bâtiment lié, aucune parcelle liée'
        } else {
            if (idsBatimentGo !== '') {
                const aIdBat: string[] = idsBatimentGo.split(",");
                nbrBatiment = aIdBat.length
            }
            if (idsParcelleGo !== '') {
                const aIdPar: string[] = idsParcelleGo.split(",");
                nbrParcelle = aIdPar.length
            }
            if (nbrBatiment === 0) {
                liensBatimentsParcelles.value = 'aucun bâtiment lié, '    
            } else if (nbrBatiment === 1) {
                liensBatimentsParcelles.value = '1 bâtiment lié, '    
            } else {
                liensBatimentsParcelles.value = `${nbrBatiment} bâtiments liés, `    
            }
            if (nbrParcelle === 0) {
                liensBatimentsParcelles.value = 'aucune parcelle liée, '    
            } else if (nbrParcelle === 1) {
                liensBatimentsParcelles.value += '1 parcelle liée, '    
            } else {
                liensBatimentsParcelles.value += `${nbrParcelle} parcelles liées, `    
            }
        }

    }



    jfFormsImportDataLoading.value = false
}

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