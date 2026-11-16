<script setup lang="ts">
import {ref, computed, useTemplateRef, onMounted, watch, watchEffect} from 'vue'
let table = ['nigga1','nigga2','nigga3','nigga4']
const names = ref(table)
const listtype = ref('ol')
const itemrefs = useTemplateRef('items')
watchEffect(()=>{
    console.log(`the list type is ${listtype.value} and the list is ${names.value.join('->')}`)
})

onMounted(()=>console.log(itemrefs.value))

function niggaFunc(niggaName){
    return "this niggas number is : "+ niggaName.split('').pop()
}
function shuffle<T>(array: T[]): T[] {
    const result = [...array]
    for (let i = result.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1))
        ;[result[i], result[j]] = [result[j], result[i]]
    }

    return result
}
function randomise(){
    names.value = shuffle(names.value)
}

function changeTableType(){

    listtype.value = listtype.value == 'ol' ? 'ul' : 'ol'
    return listtype
}

</script>

<template>
    <div>
        <button @click="changeTableType">change list type</button>
        <button @click="randomise">shuffle</button>
        <component :is="listtype">
            <li v-for="nigga in names" ref="items">
                {{niggaFunc(nigga)}}
            </li>
        </component>
    </div>




</template>

<style scoped>


</style>
