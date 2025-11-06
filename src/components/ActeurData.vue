
<template>
<div v-if="messageErreur == ''">
  <v-container v-if="bInGroupDocumentAucunAcces !== 1">
    <v-row v-if="acteurD.bactif == 0" no-gutters>
      <v-col cols="12" md="12" class="colinfoimportant">Acteur désactive <span v-if="acteurD.datedesactivation !== undefined && acteurD.datedesactivation !== null"> le {{ acteurD.datedesactivation }}</span></v-col>
    </v-row>
    <v-row no-gutters>
      <v-col cols="12" md="2" class="coltitre">Nom</v-col>
      <v-col cols="12" md="10">{{ acteurD.acteurnom }}</v-col>
    </v-row>
    <v-row no-gutters>
      <v-col cols="12" md="2" class="coltitre">Adresse</v-col>
      <v-col cols="12" md="10"><div v-html="acteurD.acteuradresse"></div></v-col> <!-- v-html pour garder les <br/> -->
    </v-row>
    <v-row v-if="acteurD.acteurcommentaire !== undefined && acteurD.acteurcommentaire !== null" no-gutters>
      <v-col cols="12" md="2" class="coltitre">Commentaire</v-col>
      <v-col cols="12" md="10"><div v-html="acteurD.acteurcommentaire"></div></v-col> <!-- v-html pour garder les <br/> -->
    </v-row>
    <v-row v-for="complement in acteurDCompl" no-gutters>
      <v-col cols="12" md="2" class="coltitre">{{ complement.acteurcomplementtype }}</v-col>
      <v-col cols="12" md="10"><div v-html="complement.acteurcomplement"></div></v-col> <!-- v-html pour garder les <br/> -->
    </v-row>
    <v-row no-gutters v-if="acteurDataAdresse.length > 1">
      <v-col cols="12" md="12">
        <v-expansion-panels>
          <v-expansion-panel>
            <v-expansion-panel-title class="coltitre">détail adresses ({{ acteurDataAdresse.length }})</v-expansion-panel-title>
            <v-expansion-panel-text>
              <div v-for="adresse in acteurDataAdresse">
                <div class="colinfo" v-if="adresse.acadcalification !== null">{{ adresse.acadcalification }}</div>
                <div v-if="adresse.actadruenumero !== null">{{ adresse.actadruenumero }}</div>
                <div v-if="adresse.actadcpville !== null">{{ adresse.actadcpville }}</div>
                <div v-if="adresse.actadpays !== 'Suisse'">{{ adresse.actadpays }}</div>
                <div v-if="adresse.actadcomplement !== null">{{ adresse.actadcomplement }}</div>
                <div>&nbsp;</div>
              </div>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
    </v-row>
    <v-row no-gutters v-if="acteurDataActeurLie.length > 0">
      <v-col cols="12" md="12">
        <v-expansion-panels>
          <v-expansion-panel>
            <v-expansion-panel-title class="coltitre">Acteurs liés ({{ acteurDataActeurLie.length }})</v-expansion-panel-title>
            <v-expansion-panel-text>
              <v-container>
                <v-row v-for="acteurlie in acteurDataActeurLie">
                  <v-col cols="12" md="2">{{ acteurlie.actaclrolecontact }}</v-col>
                  <v-col cols="12" md="8">{{ acteurlie.actaclnom }}</v-col>
                </v-row>
                </v-container>
            </v-expansion-panel-text>
          </v-expansion-panel>        
        </v-expansion-panels>
      </v-col>
    </v-row>
    <v-row no-gutters v-if="nbrRoles > 0">
      <v-col cols="12" md="12">
        <v-expansion-panels>
          <v-expansion-panel>
            <v-expansion-panel-title class="coltitre">Rôles ({{ nbrRoles }})</v-expansion-panel-title>
            <v-expansion-panel-text>
              <v-expansion-panels>
                <v-expansion-panel v-for="typerole in acteurDRole">
                  <v-expansion-panel-title>{{ typerole.acteurrolerole }} ({{ typerole.acteurrolenbrelements }})</v-expansion-panel-title>
                  <v-expansion-panel-text>
                    <v-container>
                      <v-row v-for="roleel in typerole.acteurroleelements" no-gutters>
                        <v-col cols="12" md="2">{{ roleel.aroleobjet }}</v-col>
                        <v-col cols="12" md="8"
                          v-if="roleel.aroleobjetnom !== null && roleel.aroleobjetdroitacces > 0"
                          :class="`bactif${roleel.aroleobjetbactif}`"
                          v-html="`<a class='ago' target='_blank' href='${roleel.aroleobjeturl}'><span  class='bactif${roleel.aroleobjetbactif}'>${roleel.aroleobjetnom}</span></a>`"
                        >
                        </v-col>
                        <v-col cols="12" md="8"
                          v-else-if="roleel.aroleobjetnom !== null && roleel.aroleobjetdroitacces == 0"
                          class="colmessage"
                        >
                          données confidentielles 
                        </v-col>
                        <v-col cols="12" md="8"
                          v-else
                          class="colmessage"
                        >
                          {{ roleel.aroleobjet }} {{ roleel.aroleidobjet }} n'existe plus. Prévenir le support goéland 
                        </v-col>
                        <v-col cols="12" md="2" class="goroledate">{{ roleel.aroledatecreation }}</v-col>  
                      </v-row>  
                    </v-container>  
                  </v-expansion-panel-text>
                </v-expansion-panel>  
              </v-expansion-panels>  
            </v-expansion-panel-text>
          </v-expansion-panel>        
        </v-expansion-panels>
      </v-col>
    </v-row>
    <v-row no-gutters>
      <v-col cols="12" md="12" class="colinfo">Création le {{ acteurD.datecreation }}. <span v-if="acteurD.datemodification !== null"> Dernière modification le {{ acteurD.datemodification }}.</span> id goéland: {{ acteurD.acteurid }}</v-col>
    </v-row>
  </v-container>
  <div v-else>
    <h3>Vous n'avez pas l'autorisation d'accèder aux données acteurs de goéland</h3>  
  </div>

</div>
<div else v-html="messageErreur"></div>
</template>

<script setup lang="ts">
import type { Acteur,
  ActeurComplement,
  ApiResponseDL,
  ApiResponseDc,
  ApiResponseDa,
  ApiResponseDal,
  ApiResponseDar,
  ActeurAdresse,
  ActeurActLie,
  ActeurRole
} from '@/axioscallsacteur.ts'
import { ref } from 'vue'
import { getActeurData } from '@/axioscallsacteur.js'

interface SsPages {
  ssPageData?: string
  ssPageDataAdresse?: string
  ssPageDataComplement?: string
  ssPageDataActeurLie?: string
  ssPageDataActeurRole?: string
}
interface Props {
  acteurId: number
  ssServer?: string
  ssPages?: SsPages
}

interface ActeurDCompl {
  acteurcomplementtype: string
  acteurcomplement: string  
}

interface ActeurDRole {
  acteurrolerole: string  
  acteurrolenbrelements: number
  acteurroleelements: ActeurRole[] 
}

const props = withDefaults(defineProps<Props>(), {
  ssServer: '',
  ssPages: () => ({
    ssPageData: '/goeland/acteur/ajax/acteur_data.php',
    ssPageDataAdresse: '/goeland/acteur/ajax/acteur_dataadresse.php',
    ssPageDataComplement: '/goeland/acteur/ajax/acteur_datacomplement.php',
    ssPageDataActeurLie: '/goeland/acteur/ajax/acteur_dataacteurlie.php',
    ssPageDataActeurRole: '/goeland/acteur/ajax/acteur_datarole.php'
  } as SsPages)
})

const acteurId = ref<number>(props.acteurId)
const ssServer = ref<string>(props.ssServer)
const ssPageData = ref<string>(props.ssPages?.ssPageData ?? '/goeland/acteur/ajax/acteur_data.php');
const ssPageDataAdresse = ref<string>(props.ssPages?.ssPageDataAdresse ?? '/goeland/acteur/ajax/acteur_dataadresse.php');
const ssPageDataComplement = ref<string>(props.ssPages?.ssPageDataComplement ?? '/goeland/acteur/ajax/acteur_datacomplement.php');
const ssPageDataActeurLie = ref<string>(props.ssPages?.ssPageDataActeurLie ?? '/goeland/acteur/ajax/acteur_dataacteurlie.php');
const ssPageDataActeurRole = ref<string>(props.ssPages?.ssPageDataActeurRole ?? '/goeland/acteur/ajax/acteur_datarole.php');

const messageErreur = ref<string>('')
const bInGroupDocumentAucunAcces = ref<number>(0) //Provisoire...

const responseDL: ApiResponseDL = await getActeurData(ssServer.value, ssPageData.value, acteurId.value)
if (responseDL.message != 'ok') {
  messageErreur.value += responseDL.message 
}
const acteurData: Acteur[] = responseDL.success && responseDL.data ? responseDL.data : []
const acteurD = ref<Acteur>(acteurData[0] ?? {} as Acteur)

const responseDc: ApiResponseDc = await getActeurData(ssServer.value, ssPageDataComplement.value, acteurId.value)
if (responseDc.message != 'ok') {
  messageErreur.value += responseDc.message 
}
const acteurDataComplement: ActeurComplement[] = responseDc.success && responseDc.data ? responseDc.data : []

const responseDa: ApiResponseDa = await getActeurData(ssServer.value, ssPageDataAdresse.value, acteurId.value)
if (responseDa.message != 'ok') {
  messageErreur.value += responseDa.message 
}
const acteurDataAdresse = ref<ActeurAdresse[]>(responseDa.success && responseDa.data ? responseDa.data : [])

const responseDal: ApiResponseDal = await getActeurData(ssServer.value, ssPageDataActeurLie.value, acteurId.value)
if (responseDa.message != 'ok') {
  messageErreur.value += responseDa.message 
}
const acteurDataActeurLie = ref<ActeurActLie[]>(responseDal.success && responseDal.data ? responseDal.data : [])

const responseDar: ApiResponseDar = await getActeurData(ssServer.value, ssPageDataActeurRole.value, acteurId.value)
if (responseDar.message != 'ok') {
  messageErreur.value += responseDar.message 
}
const acteurDataRole: ActeurRole[] = responseDar.success && responseDar.data ? responseDar.data : []

//Compléments
//tout le binz ci-dessous pour afficher plus joli les cas avec plusieurs type de complément identique
//et les cas spéciaux avec un url 
const transformActeurDCompl = (acteurDataComplement: ActeurComplement[]): ActeurDCompl[] => {
  const nbrComplements: number = acteurDataComplement.length
  const aActeurDCompl: ActeurDCompl[] = []
  
  if (nbrComplements === 0) return aActeurDCompl
  
  const firstItem = acteurDataComplement[0]
  if (!firstItem) return aActeurDCompl
  
  let idTypeComplement: number = firstItem.acteurcomplementtypeid
  let idTypeComplementPrec: number = idTypeComplement
  let typeComplement: string = firstItem.acteurcomplementtype
  let urlRegCom: string = ''
  let complement: string = '', complementplus: string = ''
  
  if (idTypeComplement == 22) {
    urlRegCom = firstItem.acteurcomplement
  } else if (idTypeComplement == 8) {
    complement = `<a class="ago" href="${firstItem.acteurcomplement}" target="_blank">${firstItem.acteurcomplement}</a>`
  } else if (idTypeComplement == 24) {
    complement = `<a class="ago" href="https://debiteur.lausanne.ch/debiteur-ui/details-debiteur/${firstItem.acteurcomplement}" target="_blank">${firstItem.acteurcomplement}</a>`
  } else {
    complement = firstItem.acteurcomplement
  }
  
  let oActeurDCompl: ActeurDCompl
  
  for (let i: number = 1; i < acteurDataComplement.length; i++) {
    const currentItem = acteurDataComplement[i]
    if (!currentItem) continue // Skip si undefined
    
    idTypeComplement = currentItem.acteurcomplementtypeid
    
    if (idTypeComplement == 22) {
      urlRegCom = currentItem.acteurcomplement
      idTypeComplementPrec = 22 
    } else {
      if (idTypeComplement != idTypeComplementPrec) {
        if (idTypeComplementPrec != 22) {
          oActeurDCompl = {
            "acteurcomplementtype": typeComplement, 
            "acteurcomplement": complement, 
          }
          aActeurDCompl.push(oActeurDCompl)
        }
        idTypeComplementPrec = idTypeComplement
        typeComplement = currentItem.acteurcomplementtype
        
        if (idTypeComplement == 21) {
          complement = `<a class="ago" href="${urlRegCom}" target="_blank">${currentItem.acteurcomplement}</a>`
        } else if (idTypeComplement == 8) {
          complement = `<a class="ago" href="${currentItem.acteurcomplement}" target="_blank">${currentItem.acteurcomplement}</a>`
        } else if (idTypeComplement == 24) {
          complement = `<a class="ago" href="https://debiteur.lausanne.ch/debiteur-ui/details-debiteur/${currentItem.acteurcomplement}" target="_blank">${currentItem.acteurcomplement}</a>`
        } else {
          complement = currentItem.acteurcomplement
        }
      } else {
        if (idTypeComplement == 8) {
          complementplus = `<a class="ago" href="${currentItem.acteurcomplement}" target="_blank">${currentItem.acteurcomplement}</a>`
        } else if (idTypeComplement == 24) {
          complement = `<a class="ago" href="https://debiteur.lausanne.ch/debiteur-ui/details-debiteur/${currentItem.acteurcomplement}" target="_blank">${currentItem.acteurcomplement}</a>`
        } else {
          complementplus = currentItem.acteurcomplement
        }
        complement = `${complement}<br>${complementplus}`  
      }
    }
  }
  
  typeComplement = typeComplement.replace("ABACUS", "")
  oActeurDCompl = {
    "acteurcomplementtype": typeComplement, 
    "acteurcomplement": complement, 
  }
  aActeurDCompl.push(oActeurDCompl) 
  
  return aActeurDCompl
}
const acteurDCompl = ref<ActeurDCompl[]>(transformActeurDCompl(acteurDataComplement))

//Rôles
const nbrRoles = ref<number>(acteurDataRole.length)
//Tout ce binz pour regrouper par rôle
//const transformActeurDCompl = (acteurDataComplement: ActeurComplement[]): ActeurDCompl[] => {
const transformActeurDRole = (acteurDataRole: ActeurRole[]): ActeurDRole[] => {
  const aActeurDRole: ActeurDRole[] = []
  if (nbrRoles.value > 0) {
    //Liste distincte des rôles
    const aacRoleRole: string[] = [...new Set(acteurDataRole.map(item => item.acrolerole))]
    const nbracRoleRole: number = aacRoleRole.length
    //Regroupement par rôle
    for (let i: number=0; i<nbracRoleRole; i++) {
      const role: string = aacRoleRole[i] ?? ''
      const roleEls: ActeurRole[] = acteurDataRole.filter(item => item.acrolerole === role);
      const nbrEls: number = roleEls.length
      const oActeurDRole: ActeurDRole = {
        "acteurrolerole" : role,
        "acteurrolenbrelements" : nbrEls,
        "acteurroleelements" : roleEls
      }
      aActeurDRole.push(oActeurDRole)
    }
  }
  return aActeurDRole
}
const acteurDRole = ref<ActeurDRole[]>(transformActeurDRole(acteurDataRole))

</script>

<style>
  .coltitre {
    font-weight: bold;
  }
  .colinfoimportant {
    color: red;
  }
  .colmessage {
    font-style: italic;
    font-weight: lighter;
  }
  .colinfo {
    font-size: small;
  }
  a.ago {
    text-decoration: none;
    color: inherit;
  }
  .bactif0 {
    font-style: italic;
    color: rgb(252, 182, 182)
  }
  .bactif1 {
    font-style: normal;
  }
  .goroledate {
    text-align: right;
  }
</style>
