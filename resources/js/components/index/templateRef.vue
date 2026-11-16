<script setup lang="ts">
import {ref, computed, useTemplateRef, onMounted} from "vue";
const input = useTemplateRef('user_input')

onMounted(()=>{
    input.value.scrollIntoView()
    input.value.focus()
})
</script>

<template>
    <input ref="user_input">
</template>

<style scoped>

</style>
