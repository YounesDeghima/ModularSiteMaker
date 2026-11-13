<script setup lang="ts">
import {ref, computed} from 'vue'

const btnStatus = ref(true);

function switchMode(){
    btnStatus.value = (btnStatus.value == true) ? false : true;

}


</script>

<template>
    <div>
        <button @click="switchMode">
            switch between on and off
        </button>
        <button v-if="btnStatus">
            i am on
        </button>
        <button v-else :disabled="!btnStatus">
            i am off
        </button>
    </div>
</template>

<style scoped>

</style>
