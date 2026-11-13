<script setup>

import {ref, computed} from 'vue'


const colors = ['red', 'blue', 'green', 'yellow', 'purple', 'orange']

function getRandomColor() {
    const index = Math.floor(Math.random() * colors.length)
    return colors[index]
}

const btnColor = ref('black')
const btnTxtColor = ref('white')

function changeColor(){
    let rc = getRandomColor();
    btnColor.value = (rc === btnColor.value) ? colors[(colors.indexOf(rc) + 1) % colors.length] : rc;
    let rtc = getRandomColor();
    btnTxtColor.value = (rtc ===  btnColor.value) ? colors[(colors.indexOf(rtc) + 1) % colors.length] : rtc;

}


</script>

<template>
    <div>
        <button :style="{backgroundColor:btnColor ,color:btnTxtColor}" @click="changeColor">
            change my color
        </button>
    </div>
</template>

<style scoped>

</style>
