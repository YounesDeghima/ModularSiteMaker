<script setup lang="ts">
import {ref,nextTick} from 'vue'
import option_board from './option_board.vue';
import Cards from "./cards.vue";

const numberOfCards = ref(0)
const finishedTime = ref(0)
const lastCount = ref(0)

function cardGenerator(cardNumber){
    lastCount.value = cardNumber
    numberOfCards.value = cardNumber
}
function reset(){
    numberOfCards.value = 0
}
async function playAgain() {
    const n = lastCount.value
    finishedTime.value = null
    numberOfCards.value = 0       // the watcher in cards.vue only fires on a change,
    await nextTick()              // so go 0 first...
    numberOfCards.value = n       // ...then back to the same size
}

function formatTime(total: number) {
    const m = Math.floor(total / 60)
    const s = total % 60
    return `${m}:${String(s).padStart(2, '0')}`
}
function onFinished(seconds : number){
    finishedTime.value = seconds
}

</script>

<template>

    <div class="table">
        <header class="heading">
            <h1>Memory match</h1>
            <p>Study the cards, then find every pair.</p>
        </header>
        <option_board @beginTheGame="cardGenerator" @reset = 'reset'></option_board>
        <cards :numberOfCards="numberOfCards" @finishedTheGame="onFinished"></cards>

        <div v-if="finishedTime !== null && finishedTime!=0" class="overlay" @click.self="finishedTime = null">
            <div class="win" role="dialog" aria-modal="true" aria-labelledby="win-title">
                <h2 id="win-title">You won!</h2>
                <p>You found every pair in {{ formatTime(finishedTime) }}.</p>
                <div class="actions">
                    <button class="primary" @click="playAgain">Play again</button>
                    <button @click="finishedTime = null">Close</button>
                </div>
            </div>
        </div>

    </div>


</template>

<style scoped>
.overlay {
    position: fixed;
    inset: 0;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, 0.55);
    animation: fade-in 0.3s ease 0.6s both;   /* 0.6s delay so the last pair is visible first */
}

.win {
    width: min(90vw, 340px);
    padding: 28px 32px;
    text-align: center;
    color: #2c3e50;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

.win h2 {
    margin-bottom: 8px;
    font-size: 1.8rem;
}

.actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.actions button {
    height: 42px;
    padding: 0 20px;
    font: inherit;
    font-weight: 600;
    color: #2c3e50;
    background: transparent;
    border: 1px solid #b8c2cc;
    border-radius: 8px;
    cursor: pointer;
}

.actions .primary {
    color: #fff;
    background: #42b883;
    border-color: #42b883;
}

.actions button:focus-visible {
    outline: 3px solid #f5c542;
    outline-offset: 2px;
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .overlay { animation: none; }
}
</style>

