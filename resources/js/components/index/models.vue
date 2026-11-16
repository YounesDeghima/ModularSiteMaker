<script setup lang="ts">
import {ref, computed, watchEffect, useTemplateRef, onMounted} from 'vue'

let table = ['nigga1','nigga2','nigga3','nigga4']
const niggers = ref(table)
const selectedNiggers = ref(<string[]>([]))

function selectedNiggerConstructor(){
    return selectedNiggers.value.join(',')
}

const niggercheckboxes = useTemplateRef<HTMLDivElement[]>('niggersElementTable')

onMounted(()=>watchEffect(()=>{
    console.log(` the list is ${niggercheckboxes.value.map(el=>el.textContent).join('->')}`)
}))






</script>

<template>
    <div>
        <div >niggers you want to Buy : {{selectedNiggerConstructor()}}</div>
        <div v-for="nigger in niggers" :key="nigger" ref="niggersElementTable">
            <label>{{nigger}}</label>
            <input type="checkbox"  :value="nigger" v-model="selectedNiggers">
        </div>
    </div>
</template>

<style scoped>

</style>
