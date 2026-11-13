<template>
    <div>
        <button v-on:click="increment_counter">increment</button>
        <button v-on:click="decrement_counter">decrement</button>
        <h2>value : {{counter}}</h2>
        <h2>parity : {{parity}}</h2>
    </div>
</template>

<script setup>
    import { ref, computed } from 'vue'

    const counter = ref(0)

    function increment_counter(){
        counter.value++
    }
    function decrement_counter(){
        counter.value--
    }

    const parity = computed(()=>{return counter.value%2==0 ? 'even' : 'odd'});


</script>
