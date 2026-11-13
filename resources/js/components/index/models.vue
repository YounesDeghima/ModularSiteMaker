<script setup lang="ts">
import {ref,computed} from 'vue'

let table = ['nigga1','nigga2','nigga3','nigga4']
const niggers = ref(table)
const selectedNiggers = ref(<string[]>([]))





</script>

<template>
    <div>
        <div >niggers you want to Buy : {{selectedNiggers}}</div>
        <div v-for="nigger in niggers" :key="nigger.value">
            <label>{{nigger}}</label>
            <input type="checkbox"  :value="nigger" v-model="selectedNiggers">
        </div>
    </div>
</template>

<style scoped>

</style>
