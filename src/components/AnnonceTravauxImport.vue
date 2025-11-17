<template>
    <CallerInfo :ssServer="ssServer" @callerinfo="receptionCallerInfo"></CallerInfo>
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
                <v-row dense>
                    <v-col cols="12" md="8">
                        <v-text-field v-model="nomAffaire" label="Nom" :maxlength="100" counter="100"
                            variant="outlined"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">{{ nomAffaireRemarqueGo }}</v-col>
                </v-row>
                <v-row dense>
                    <v-col cols="12" md="12">
                        <v-text-field v-model="descriptionAffaire" label="Description" :maxlength="100" counter="100"
                            variant="outlined"></v-text-field>
                    </v-col>
                </v-row>
                <v-row dense>
                    <v-col cols="12" md="6">
                        <v-select v-model="idEmpGestionnaire" :items="gestionnaireListeChoix" item-title="nom"
                            item-value="id" label="Sélectionner le gestionnaire" density="comfortable"></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select v-model="idEmpTechnicien" :items="technicienListeChoix" item-title="nom"
                            item-value="id" label="Sélectionner le technicien" density="comfortable"></v-select>
                    </v-col>
                </v-row>
                <v-row dense>
                    <v-col cols="12" md="12">
                        <!-- En-tête Client -->
                        <div class="d-flex align-center mb-3">
                            <span class="text-subtitle-2 text-grey-darken-1 mr-2">Client :</span>
                            <span class="font-weight-medium mr-4">{{ nomActeurClient }}</span>

                            <!-- Boutons d'action -->
                            <v-btn v-if="idActeurClient > 0" color="grey-lighten-1" icon="mdi-information"
                                variant="text" size="small" class="mr-2"
                                title="information détaillée pour cet acteur goéland"
                                @click="infoActeur = !infoActeur"></v-btn>

                            <v-btn v-if="idActeurClient > 0" color="grey-lighten-1" icon="mdi-delete" variant="text"
                                size="small" class="mr-4" title="supprimer ce choix de client"
                                @click="supprimeClient()"></v-btn>

                            <v-btn color="primary" variant="tonal" size="small" prepend-icon="mdi-account-search"
                                @click="choixActeur = !choixActeur">
                                Choisir un acteur
                            </v-btn>
                            &nbsp;&nbsp;
                            <a :href="`mailto:goeland@lausanne.ch?subject=${encodeURI('Création acteur')}&body=${encodeURI(mailtobody)}`"
                                class="text-decoration-none">
                                <v-icon icon="mdi-email" size="small" class="me-1"></v-icon>
                                demande de création d'un acteur
                            </a>
                        </div>

                        <!-- Card Info Acteur -->
                        <v-card v-if="infoActeur" variant="outlined" class="mb-3" style="position: relative;">
                            <v-btn icon="mdi-close" variant="text" size="small" @click="infoActeur = false"
                                style="position: absolute; top: 4px; right: 4px; z-index: 1;"></v-btn>
                            <v-card-text class="pt-8">
                                <Suspense>
                                    <ActeurData :acteurId="idActeurClient" :ssServer="ssServer"></ActeurData>
                                </Suspense>
                            </v-card-text>
                        </v-card>

                        <!-- Card Choix Acteur -->
                        <v-card v-if="choixActeur" variant="outlined" class="mb-3" style="position: relative;">
                            <v-btn icon="mdi-close" variant="text" size="small" @click="choixActeur = false"
                                style="position: absolute; top: 4px; right: 4px; z-index: 1;"></v-btn>
                            <v-card-text class="pt-8">
                                <Suspense>
                                    <ActeurChoix :ssServer="ssServer" modeChoix="unique" @choixActeur="receptionActeur">
                                    </ActeurChoix>
                                </Suspense>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
                <v-row dense>
                    <v-col cols="12" md="12">
                        <v-card v-if="nombreFichiers > 0">
                            <v-row dense>
                                <h3 class="text-subtitle-1 mb-3">
                                    <v-icon icon="mdi-paperclip" size="small" class="me-1"></v-icon>
                                    Fichiers joints
                                </h3>
                            </v-row>
                            <v-row dense v-for="(fichier, index) in fichiers" :key="fichier.idjf"
                                class="px-4 align-center" :class="{ 'border-b': index < fichiers.length - 1 }">
                                <v-col cols="12" md="6">{{ fichier.filename }}</v-col>
                                <v-col cols="12" md="5">
                                    <span
                                        v-if="fichier.idDocGo === 0 && fichier.infoDoublon === '' && fichier.size <= docSizeMax">
                                        <v-select v-model="fichier.idFamille" :items="docFamilleListe"
                                            item-title="label" item-value="id" label="Famille" density="compact"
                                            variant="outlined"></v-select>
                                    </span>
                                    <span v-if="fichier.idDocGo > 0">
                                        document goéland {{ fichier.idDocGo }}
                                    </span>
                                    <span v-if="fichier.idDocGo == 0 && fichier.infoDoublon !== ''">
                                        {{ fichier.infoDoublon }}
                                    </span>
                                    <span
                                        v-if="fichier.idDocGo == 0 && fichier.infoDoublon === '' && fichier.size > docSizeMax">
                                        fichier de {{ Math.round(fichier.size / 1024 / 1024) }} Mo, maximum accepté : {{
                                            Math.round(docSizeMax / 1024 / 1024) }} Mo
                                    </span>
                                </v-col>
                                <v-col cols="12" md="1">
                                    <v-btn icon="mdi-eye" variant="text" color="primary" size="small"
                                        @click="voirFichier(fichier.idjf)"></v-btn>
                                </v-col>
                            </v-row>
                        </v-card>

                    </v-col>
                </v-row>
                <v-row dense>
                    <v-col cols="12" md="12">{{ liensBatimentsParcelles }}</v-col>
                </v-row>
                <v-row dense>
                    <v-col cols="12" md="12" class="d-flex justify-center align-center">
                        <v-btn color="red-accent-3" variant="text" @click="importDemande()">
                            Importer cette demande dans une nouvelle affaire goéland
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup lang="ts">
import type { ApiResponseUI, UserInfo } from './CallerInfo.vue'
import type { ApiResponseIG } from './CallerIsInGroup.vue'
import type { ApiResponseIFD, ApiResponseEU, EmployeParUO, ApiResponseNumStr } from '@/axioscalls.ts'
import type { ApiResponseDIP } from '@/axioscalls.ts'
import type { DataForms, Fichier, EmployeParticipe, AffaireDataImport, FichierImport } from '@/jaxformsOpcAnnonceTravauxImport.ts'
import { getImportFormsData, getListeEmployeParUO, getDocImportParams, importAffaire } from '@/axioscalls.ts'
import { ref, onMounted, watch } from 'vue'
import { stringToPositiveInteger } from '@/jaxformsOpcAnnonceTravauxImport.ts'

interface Props {
    jsonDataForms: string
    ssServer?: string
    ssPage?: string
    ssPageEmployeListe?: string
    ssPageDocImportParams?: string
    ssPageAffaireImport?: string
}

const props = withDefaults(defineProps<Props>(), {
    ssServer: '',
    ssPage: '/goeland/jaxforms/axios/jfprepareimport_annoncetravaux.php',
    ssPageEmployeListe: '/goeland/employe/axios/employe_liste_parunite.php',
    ssPageDocImportParams: '/goeland/jaxforms/axios/jflistefamilledocument_annoncetravaux.php',
    ssPageAffaireImport: '/goeland/jaxforms/axios/jfimportaffaire_annoncetravaux.php'
})

//Data caller
const callerInformation = ref<UserInfo | null | undefined>(null)
//Droits caller
const bOPCAnnonceTravauxImport = ref<boolean>(false)

const messageErreur = ref<string | undefined>('')
const jfFormsImportDataLoading = ref<boolean>(false);
const idJaxformsDemande = ref<string>('')
const numeroJaxformsDemande = ref<string>('')
const nomAffaire = ref<string>('');
const nomAffaireRemarqueGo = ref<string>('');
const descriptionAffaire = ref<string>('');
const aIdsBatimentGo = ref<number[]>([])
const aIdsParcelleGo = ref<number[]>([])
const liensBatimentsParcelles = ref<string>('');
const gestionnaireListeChoix = ref<EmployeParticipe[]>([])
const idEmpGestionnaire = ref<number>(0)
const technicienListeChoix = ref<EmployeParticipe[]>([])
const idEmpTechnicien = ref<number>(0)
const idActeurClient = ref<number>(0)
const nomActeurClient = ref<string>(' - ')
const choixActeur = ref<boolean>(false)
const infoActeur = ref<boolean>(false)
const nombreFichiers = ref<number>(0)
const fichiers = ref<Fichier[]>([])
const docFamilleListe = ref<[{ id: number, label: string }]>([{ id: 0, label: 'fichier pas importé' }])
const docSizeMax = ref<number>(5000000)
const mailtobody = ref<string>('')

const emit = defineEmits<{
    (e: 'affaireimport', idaffaire: string): void
}>()

onMounted(() => {
    loadDataImport()
})

watch(() => infoActeur.value, (newValue) => {
    if (newValue) {
        choixActeur.value = false
    }
})
watch(() => choixActeur.value, (newValue) => {
    if (newValue) {
        infoActeur.value = false
    }
    console.log("choixActeur", choixActeur.value, "infoActeur", infoActeur.value)
})

const loadDataImport = async () => {
    jfFormsImportDataLoading.value = true
    console.log('props.jsonDataForms', props.jsonDataForms)

    let gestionnaire: EmployeParticipe = { id: 0, nom: '-' }
    gestionnaireListeChoix.value.push(gestionnaire)
    const responseGestionnaire: ApiResponseEU = await getListeEmployeParUO(props.ssServer, props.ssPageEmployeListe, 106)
    if (responseGestionnaire.data !== undefined) {
        const employesOPCAdm: EmployeParUO[] = responseGestionnaire.data
        employesOPCAdm.forEach((empl) => {
            gestionnaire = {
                id: empl.idemploye,
                nom: `${empl.nom} ${empl.prenom}`
            }
            gestionnaireListeChoix.value.push(gestionnaire)
        })
    }
    if (gestionnaireListeChoix.value.some(item => item.id === callerInformation.value?.id)) {
        if (callerInformation.value?.id !== undefined && callerInformation.value?.id !== null) {
            idEmpGestionnaire.value = callerInformation.value?.id
        }
    }

    let technicien: EmployeParticipe = { id: 0, nom: '-' }
    technicienListeChoix.value.push(technicien)
    const responseTechnicien: ApiResponseEU = await getListeEmployeParUO(props.ssServer, props.ssPageEmployeListe, 105)
    if (responseTechnicien.data !== undefined) {
        const employesOPCTech: EmployeParUO[] = responseTechnicien.data
        employesOPCTech.forEach((empl) => {
            technicien = {
                id: empl.idemploye,
                nom: `${empl.nom} ${empl.prenom}`
            }
            technicienListeChoix.value.push(technicien)
        })
    }
    if (technicienListeChoix.value.some(item => item.id === callerInformation.value?.id)) {
        if (callerInformation.value?.id !== undefined && callerInformation.value?.id !== null) {
            idEmpTechnicien.value = callerInformation.value?.id
        }
    }

    const responseID: ApiResponseIFD = await getImportFormsData(props.ssServer, props.ssPage, props.jsonDataForms)
    if (responseID.data !== undefined) {
        const dataImportPropose: DataForms = responseID.data
        //console.log("dataImportPropose", dataImportPropose)

        idJaxformsDemande.value = dataImportPropose.idDemande
        numeroJaxformsDemande.value = dataImportPropose.numeroDemande

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

        //Client (acteur)
        if (dataImportPropose.demandeur.idacteurGo !== undefined && dataImportPropose.demandeur.nomacteurGo !== undefined) {
            if (dataImportPropose.demandeur.idacteurGo > 0) {
                idActeurClient.value = dataImportPropose.demandeur.idacteurGo
                nomActeurClient.value = dataImportPropose.demandeur.nomacteurGo
            }
        }
        //mailtobody pour demande de création d'acteur
        const dataForms: DataForms = JSON.parse(props.jsonDataForms)
        const societe: string = dataForms.demandeur.societe ?? ''
        const nom: string = dataForms.demandeur.nom ?? ''
        const prenom: string = dataForms.demandeur.prenom ?? ''
        const rue: string = dataForms.demandeur.rue ?? ''
        const numero: string = dataForms.demandeur.numero ?? ''
        const npa: string = dataForms.demandeur.npa ?? ''
        const localite: string = dataForms.demandeur.localite ?? ''
        const email: string = dataForms.demandeur.email ?? ''
        const telephone: string = dataForms.demandeur.telephone ?? ''
        mailtobody.value = `Societé : ${societe}\nNom : ${nom}\nPrénom : ${prenom}\nRue numéro : ${rue} ${numero}\nNpa Localité : ${npa} ${localite}\nemail : ${email}\ntéléphone : ${telephone}`

        //Fichiers
        if (dataImportPropose.fichiers !== undefined) {
            const docImportParams: ApiResponseDIP = await getDocImportParams(props.ssServer, props.ssPageDocImportParams)
            if (docImportParams.data !== undefined) {
                docSizeMax.value = docImportParams.data.sizemax
                docImportParams.data.familles.forEach((famille) => {
                    docFamilleListe.value.push({ "id": famille.id, "label": famille.label })
                })
                //console.log("docFamilleListe", docFamilleListe.value)
            }
            //console.log("docImportParams", docImportParams)
            fichiers.value = dataImportPropose.fichiers
            nombreFichiers.value = fichiers.value.length
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
                aIdBat.forEach((sid) => {
                    const idbat = stringToPositiveInteger(sid)
                    if (idbat !== null) {
                        aIdsBatimentGo.value.push(idbat)
                    }
                })
            }
            if (idsParcelleGo !== '') {
                const aIdPar: string[] = idsParcelleGo.split(",");
                nbrParcelle = aIdPar.length
                aIdPar.forEach((sid) => {
                    const idpar = stringToPositiveInteger(sid)
                    if (idpar !== null) {
                        aIdsParcelleGo.value.push(idpar)
                    }
                })
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

const supprimeClient = () => {
    idActeurClient.value = 0
    nomActeurClient.value = ' - '
}

const receptionActeur = (id: number, jsonData: string) => {
    const { acteurid, acteurnom } = JSON.parse(jsonData) as { acteurid: number, acteurnom: string }
    idActeurClient.value = acteurid
    nomActeurClient.value = acteurnom
    choixActeur.value = false
}

const voirFichier = (idFichier: string): void => {
    window.open(`${props.ssServer}/goeland/jaxforms/jffileattachmentview_annoncetravaux.php?idfileattachment=${idFichier}`)
}
const importDemande = async () => {
    console.log(fichiers.value)
    let fichierImport: FichierImport[] = []
    fichiers.value.forEach(fic => {
        const idfamille: number = fic.idFamille
        if (idfamille > 0) {
            const famille = docFamilleListe.value.find(item => item.id === idfamille)?.label ?? "famille inconnue"
            const idJaxforms: string = fic.idjf
            const filenameJaxforms: string = fic.filename
            const fimp: FichierImport = {
                "idJaxforms": idJaxforms,
                "idFamille": idfamille,
                "filename": `${famille} - ${filenameJaxforms}`
            }
            fichierImport.push(fimp)
        }
    });

    const affaireDataImport: AffaireDataImport = {
        "idJaxformsDemande": idJaxformsDemande.value,
        "numeroJaxformsDemande": numeroJaxformsDemande.value,
        "nomAffaire": nomAffaire.value.trim(),
        "descriptionAffaire": descriptionAffaire.value.trim(),
        "idEmployeGestionnaire": idEmpGestionnaire.value,
        "idEmployeTechnicien": idEmpTechnicien.value,
        "idActeurClient": idActeurClient.value,
        "idBatimentLie": aIdsBatimentGo.value,
        "idParcelleLie": aIdsParcelleGo.value,
        "fichiers": fichierImport
    }
    console.log("affaireDataImport", JSON.stringify(affaireDataImport))
    if (nomAffaire.value.trim().length >= 5) {
        const responseIdAffaire: ApiResponseNumStr = await importAffaire(props.ssServer, props.ssPageAffaireImport, JSON.stringify(affaireDataImport))
        console.log(responseIdAffaire)
        let sjson: string
        if (responseIdAffaire.data !== undefined) {
            const sidAffaire: string = responseIdAffaire.data.toString()
            if (/^\d+$/.test(sidAffaire)) {
                sjson = `{"idjaxformsdemande":"${idJaxformsDemande.value}", "idaffaire":"${sidAffaire}"}`
                emit('affaireimport', sjson)
            } else {
                messageErreur.value = `Echec lors de l'importation.\n${sidAffaire}`
            }
        } else {
            messageErreur.value = `Echec lors de l'importation.`
        }
    } else {
        messageErreur.value = `Le nom de l'affaire est obligatoire et doit contenir au minimum 5 caractères`
    }
}

const receptionCallerInfo = (jsonData: string) => {
    const retCallerInformation = ref<ApiResponseUI>(JSON.parse(jsonData))
    if (retCallerInformation.value.success) {
        callerInformation.value = retCallerInformation.value.data
    }
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

.border-b {
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

/* Pour le mode sombre */
.v-theme--dark .border-b {
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
</style>