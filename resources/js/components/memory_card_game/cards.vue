<script setup lang="ts">
import {ref,computed,watch,onMounted,onUnmounted} from 'vue'
const props = defineProps<{ numberOfCards: number }>()
const emit = defineEmits<{
    finishedTheGame : [value : number] }>()
interface card {
    id : number,
    value : number,
    flipped : boolean,
    matched : boolean
}

let timer : ReturnType<typeof setTimeout> | undefined
let clock : ReturnType<typeof setTimeout> | undefined
const seconds = ref(0)

function startTimer(){
    stopTimer()
    clock = setInterval(()=>seconds.value ++,1000)
}
function stopTimer(){
    clearInterval(clock)
    clock = undefined

}

const time = computed(()=>{
    const m = Math.floor(seconds.value/60)
    const s = seconds.value % 60
    return `${m}:${String(s).padStart(2, '0')}`
})

let mismatchTimer: ReturnType<typeof setTimeout> | undefined

const previewing = ref(false)

const cardTable = ref(<card[]>([]))




function shuffle<T>(array: T[]): T[] {
    const result = [...array]
    for (let i = result.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1))
        ;[result[i], result[j]] = [result[j], result[i]]
    }

    return result
}

function flipAllCards(){
    cardTable.value.forEach(card=>(card.flipped = true))
}


function cardMaker(nb : number){
    seconds.value = 0
    clearTimeout(mismatchTimer)
    let ct = Array.from({length:nb/2},(_,i)=>i+1)
    ct = [...ct,...ct]
    ct = shuffle(ct)
    cardTable.value = ct.map((value,id)=>({id,value,flipped:false,matched:false}))

    previewing.value = true
    clearTimeout(timer)

    timer = setTimeout(()=>{
        flipAllCards()
        previewing.value = false
        startTimer()
    },5000)
}

function gameCompleted(){
    stopTimer()
    emit('finishedTheGame',seconds.value)
}

onUnmounted(() =>{ clearTimeout(timer),clearTimeout(mismatchTimer),stopTimer(),seconds.value=0})

const flippedcards = computed (()=>cardTable.value.filter((card)=>card.flipped == false && card.matched != true))
const matchedcards = computed(()=>cardTable.value.filter((card)=>card.matched == true))

watch(()=>flippedcards.value.length,(count)=>{
    if(count!=2){
        return
    }
    else{
        const [a,b] = flippedcards.value
        if(a.value == b.value){
            a.matched = true
            b.matched = true
        }
        else{
            clearTimeout(mismatchTimer)
            mismatchTimer= setTimeout(()=>{
                a.flipped = true
                b.flipped = true
                console.log('flipped')
            },800)

        }
    }
})

watch(()=>matchedcards.value.length!=cardTable.value.length,()=>gameCompleted())

watch(()=>props.numberOfCards,(nb)=>cardMaker(nb))


</script>

<template>
    <span class="count">{{ time }} · {{ matchedcards.length / 2 }} of {{ cardTable.length / 2 }} pairs</span>
    <div class="cards">
        <div v-for="(card,index) in cardTable" :key="card.id" >
            <button @click="card.flipped=false" :disabled="previewing || !card.flipped|| card.matched || flippedcards.length >=2">{{ !card.flipped || card.matched ? card.value : '?' }}</button>
        </div>
    </div>

</template>

<style scoped>
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, 90px);
    gap: 12px;
    justify-content: center;
    max-width: 520px;
    margin: 24px auto;
    padding: 16px;
}

.cards button {
    display: block;
    width: 90px;
    height: 90px;
    font-size: 1.75rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #42b883, #35495e);
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.cards button:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
}

.cards button:active {
    transform: translateY(0) scale(0.97);
}

.cards button:focus-visible {
    outline: 3px solid #f5c542;
    outline-offset: 3px;
}

.cards button:disabled {
    opacity: 0.6;
    cursor: default;
    transform: none;
    box-shadow: none;
}
</style>
