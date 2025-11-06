<template>
  <v-container>
    <v-row no-gutters>
      <v-col cols="8" md="8">
        <v-radio-group
          label="critère selon"
          v-model="typeCritere"
          inline
          density="compact">
          <v-radio label="nom" :value="'nom'"></v-radio>&nbsp;&nbsp;
          <v-radio label="nom début" :value="'nomdebut'"></v-radio>&nbsp;&nbsp;
          <v-radio label="id goéland" :value="'idgo'"></v-radio>&nbsp;&nbsp;
          <v-radio label="id CHE" :value="'idche'"></v-radio>&nbsp;&nbsp;
        </v-radio-group>  
      </v-col>
    </v-row>
    <v-row no-gutters>
      <v-col cols="8" md="2">
        <v-checkbox      
          v-model="bActeurMoral" 
          label="personnes morales"
          @click="onInputCritere">
        </v-checkbox>  
      </v-col>
      <v-col cols="8" md="2">
        <v-checkbox
          v-model="bActeurPhysique" 
          label="personnes physiques"
          @click="onInputCritere">
        </v-checkbox>  
      </v-col>
      <v-col cols="8" md="2">
         <v-checkbox
          v-model="bActeurDesactive" 
          label="y.c. acteurs désactivés"
          @click="onInputCritere">
        </v-checkbox>  
      </v-col>
    </v-row>
    <v-row  no-gutters>
      <v-col cols="8" md="4">
        <v-text-field 
          clearable 
          v-model="txtCritere"
          ref="inpTxtCritere"
          autofocus 
          :label="labelTextField"
          :rules="critereRules"
          @input="onInputCritere"
        ></v-text-field>  
      </v-col>
    </v-row>
    <v-row v-if="modeChoix=='multiple' && acteursListeChoisi.length > 0" no-gutters>
      <v-col cols="8" md="8">
        <v-list max-height="400">
          <v-list-subheader>
            Acteurs choisis ({{ acteursListeChoisi.length }})
            &nbsp;&nbsp;&nbsp;&nbsp;
            <v-btn
              rounded="lg"
              @click="choixTermine()"
            >Choix terminé</v-btn>
          </v-list-subheader>
          <v-list-item
            v-for="acteur in acteursListeChoisi"
              :key="acteur.acteurid"
              :value="acteur.acteurid"
              :title="acteur.acteurnom"
              :class="`bactif${acteur.bactif}`"
          >
          <template v-slot:append>
              <v-btn
                color="grey-lighten-1"
                icon="mdi-delete"
                variant="text"
                @click="supprimeChoix(acteur.acteurid)"
              ></v-btn>
            </template>
        </v-list-item>
        </v-list>
      </v-col>
    </v-row>
    <v-row v-if="modeChoix=='multiple' && acteursListeChoisi.length > 0" no-gutters>
      <v-col cols="8" md="8">
        &nbsp;
      </v-col>
    </v-row>
    <v-row no-gutters>
      <v-col cols="8" md="8">
        <v-list max-height="400">
          <v-list-subheader>{{ libelleListe }}</v-list-subheader>
          <v-list-item
            v-for="acteur in acteursListeSelect"
            :key="acteur.acteurid"
            :value="acteur.acteurid"
            :title="acteur.acteurnom"
            :class="`bactif${acteur.bactif}`"
            @click="choixActeur(acteur)"
          >
            <template v-slot:append>
              <v-btn
                color="grey-lighten-1"
                icon="mdi-information"
                variant="text"
                @mouseenter="infoMouseEnter()"
                @mouseleave="infoMouseLeave()"
                @click="infoActeur(acteur.acteurid)"
              ></v-btn>
            </template>
          </v-list-item>
        </v-list>
      </v-col>  
    </v-row>
  </v-container>

  <v-dialog v-model="dialogInfo" max-width="1280">
    <v-card title="Informations Acteur">
      <v-card-text>
          <div v-if="acteurIdInfo != 0">
            <Suspense>
              <ActeurData 
                :acteurId="acteurIdInfo"
                :ssServer="ssServer"
              ></ActeurData>
            </Suspense>  
          </div>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          text="Fermer"
          @click="closeInfoActeur()"
        ></v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>  
</template>

<script setup lang="ts">
import type { VTextField } from 'vuetify/components'
import type { Acteur, ApiResponseDL } from '@/axioscallsacteur.ts'
import { ref, watch, computed, onMounted, nextTick } from 'vue'
import { getActeursListe } from '@/axioscallsacteur.ts'
import ActeurData from '@/components/ActeurData.vue'

interface Props {
  modeChoix?: string
  typeCritere?: string
  nombreMaximumRetour?: number
  ssServer?: string
  ssPage?: string
}

interface CritereValidationContext {
  typeCritere: { value: string };
}

interface CritereRecherche {
  critere: string;
  crtype: string;
  bacteurmoral: number;
  bacteurphysique: number;
  bacteurdesactive: number;
  nombremaximumretour: number;
}

const props = withDefaults(defineProps<Props>(), {
  modeChoix: 'unique',
  typeCritere: 'nom',
  nombreMaximumRetour: 100,
  ssServer: '',
  ssPage: '/goeland/acteur/ajax/acteur_liste.php'
})

const messageErreur = ref<string>('')
const libelleListe = ref<string>('choix acteurs (0)')
const modeChoix = ref<string>(props.modeChoix)
watch(() => props.modeChoix, (newValue) => {
  modeChoix.value = newValue
})
const typeCritere = ref<string>(props.typeCritere)
const nombreMaximumRetour = ref<number>(props.nombreMaximumRetour)
const txtCritere = ref<string>('')
const bActeurMoral = ref<boolean>(true)
const bActeurPhysique = ref<boolean>(true)
const bActeurDesactive = ref<boolean>(false)
const labelTextField = ref<string>('nom')
const inpTxtCritere = ref<VTextField | null>(null)
const ssServer = ref<string>(props.ssServer)
const ssPage = ref<string>(props.ssPage)
const acteursListeSelect = ref<Acteur[]>([])
const acteursListeChoisi = ref<Acteur[]>([])
const acteurIdInfo = ref<number>(0)
const dialogInfo = ref<boolean>(false)

type ValidationRule = (value: string | null | undefined) => boolean | string
let bcritereRules: boolean
let demandeInfoActeur: boolean = false


const createCritereRules = (context: CritereValidationContext): ValidationRule[] => [
  (value: string | null | undefined): boolean | string => {
    if (context.typeCritere.value === 'idgo') {
      if (!value) {
        bcritereRules = true
        return true
      }
      
      if (/^\+?(0|[1-9]\d*)$/.test(value)) {
        bcritereRules = true
        return true
      }
      
      bcritereRules = false
      return 'id goéland invalide, numérique'
    } else {
      bcritereRules = true
      return true
    }
  }
]

const critereRules = computed(() => 
  createCritereRules({ typeCritere })
)

let typingTimer: ReturnType<typeof setTimeout> | null = null
const typingInterval: number = 700
const onInputCritere = (value: string | null | undefined): void => {
  // console.log('oninput')
  
  // Nettoyer le timer précédent s'il existe
  if (typingTimer) {
    clearTimeout(typingTimer)
  }
  
  // Créer un nouveau timer
  typingTimer = setTimeout(() => {
    prepareRechercheActeurs()
  }, typingInterval)
  
  // Focus sur l'input avec vérification de sécurité
  const inputElement = inpTxtCritere.value?.$el?.querySelector('input') as HTMLInputElement | null
  inputElement?.focus()
}

onMounted((): void => {
  nextTick((): void => {
    inpTxtCritere.value?.focus()
  })
})

const prepareRechercheActeurs = (): void => {
  const critere: string = txtCritere.value
  const crType:string = typeCritere.value
    if (critere.length >= 1 && crType == 'nomdebut') {
      rechercheActeurs(critere, crType, nombreMaximumRetour.value)
    } else if (critere.length >= 3 && crType == 'nom') {
      rechercheActeurs(critere, crType, nombreMaximumRetour.value)
    } else if (critere.length >= 1 && crType == 'idgo') {
      rechercheActeurs(critere, crType, nombreMaximumRetour.value)
    } else if (critere.length >= 15 && crType == 'idche') {
      rechercheActeurs(critere, crType, nombreMaximumRetour.value)
    }
}

const rechercheActeurs = async (critere: string, crType: string, nombreMaximumRetour: number): Promise<void> => {
  let ibActeurMoral: number, ibActeurPhysique: number, ibActeurDesactive: number
  if (bActeurMoral.value == true) {
    ibActeurMoral = 1  
  } else {
    ibActeurMoral = 0 
  }
  if (bActeurPhysique.value == true) {
    ibActeurPhysique = 1 
  } else {
    ibActeurPhysique = 0  
  }
  if (bActeurDesactive.value == true) {
    ibActeurDesactive = 1  
  } else {
    ibActeurDesactive = 0  
  }


  const oCritere: CritereRecherche = {
    critere: critere,
    crtype: crType,
    bacteurmoral: ibActeurMoral,
    bacteurphysique: ibActeurPhysique,
    bacteurdesactive: ibActeurDesactive,
    nombremaximumretour: nombreMaximumRetour
  }

  const response: ApiResponseDL = await getActeursListe(ssServer.value, ssPage.value, JSON.stringify(oCritere))
  const acteursListe: Acteur[] = response.success && response.data ? response.data : []
  if (acteursListe.length < nombreMaximumRetour) {
    libelleListe.value = `Choix acteurs (${acteursListe.length})`
  } else {
    libelleListe.value = `Choix acteurs (${acteursListe.length}). Attention, plus de ${nombreMaximumRetour} acteurs correspondent aux critères`
  }
  acteursListeSelect.value = acteursListe
 

  // Votre logique asynchrone ici
  // Par exemple: await fetchData()
  console.log(acteursListe)
}

const emit = defineEmits<{
  (e: 'choixActeur', id: number, choix: string): void
}>()

const choixActeur = (acteur: Acteur): void => {
  if (!demandeInfoActeur) {
    if (modeChoix.value == 'unique') {
      emit('choixActeur', acteur.acteurid, JSON.stringify(acteur))
    } else if (modeChoix.value == 'multiple') {
      if (acteursListeChoisi.value.some(objet => objet.acteurid === acteur.acteurid) === false) {
        acteursListeChoisi.value.push(acteur)
      }
    }
  } else {
    dialogInfo.value = true  
  }
}

const supprimeChoix = (acteurid: number) => {
  acteursListeChoisi.value = acteursListeChoisi.value.filter(objet => objet.acteurid !== acteurid)  
}

const choixTermine = (): void => {
  emit('choixActeur', 0, JSON.stringify(acteursListeChoisi.value))
  acteursListeChoisi.value = [] 
}

const infoMouseEnter = (): void => {
  demandeInfoActeur = true
}

const infoMouseLeave = (): void => {
  demandeInfoActeur = false
}

const infoActeur = (idActeur: number): void => {
  acteurIdInfo.value = idActeur
}
const closeInfoActeur = (): void => {
  dialogInfo.value = false
  acteurIdInfo.value = 0
}
</script>

<style scoped>
  .bactif0 {
    font-style: italic;
    color: rgb(252, 182, 182)
  }
  .bactif1 {
    font-style: normal;
  }
</style>