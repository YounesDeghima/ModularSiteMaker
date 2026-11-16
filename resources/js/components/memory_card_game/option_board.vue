<script setup lang="ts">
import {ref,watch} from 'vue'
const cardNumber = ref(4)
const gameStarted = ref(false)
const emit = defineEmits<{
    beginTheGame : [value : number]
    reset:[]
}>()






function start(){
    if (cardNumber.value%2 != 0){
        alert('change the card number to an even number ')
        return;
    }
    gameStarted.value = true
    emit('beginTheGame',cardNumber.value)
}
function reset(){
    gameStarted.value = false;
    emit('reset')
}

</script>

<template>
    <div>
        <input type="number" v-model="cardNumber" :disabled="gameStarted">
        <button @click="start" :disabled="gameStarted">start the game</button>
        <button @click="reset">reset</button>
    </div>
</template>

<style scoped>

</style>
