<script setup lang="ts">
import {ref, computed, watch, onMounted, stop} from "vue";

let n =21

interface tableData{

    snakeTime :number,
    hasApple : boolean,
    isBorder : boolean,
    color : string,
    isHead : boolean

}
const SIZE = 21

const createCell = (row: number, col: number): tableData => ({
    snakeTime: 0,
    hasApple: false,
    isBorder: row === 0 || col === 0 || row === SIZE - 1 || col === SIZE - 1,
    isHead:false,
    color: 'red',
})

const table = ref<tableData[][]>(
    Array.from({ length: SIZE }, (_, row) =>
        Array.from({ length: SIZE }, (_, col) => createCell(row, col))
    )
)
function initialiseTable(){
    table.value.forEach((row)=>row.forEach((d)=>{d.isBorder = false;d.snakeTime = 0;d.hasApple = false}))
}

const latestApplePos = ref({i:0,j:0})


const direction = ref<"left"|"right"|"up"|"down">("left")
const length = ref(3)
const moves = {
    left : {i:0,j:-1},
    right : {i:0,j:1},
    up : {i:-1,j:0},
    down : {i:1,j:0}
}



let lastMoved =  "left"
const lastKey = ref<{key : string} | null >(null)

const keyDirections = {
    ArrowUp : "up",
    ArrowRight : "right",
    ArrowLeft : "left",
    ArrowDown : "down"
}
const opposite = {
    left: 'right',
    right: 'left',
    up: 'down',
    down: 'up',
}

const directionHeadStyle = computed(()=>{
    let string = null
    if(direction.value == "up"){
        return "6px 6px 0px 0px"
    }
    if(direction.value == "down"){
        return "0px 0px 6px 6px"
    }
    if(direction.value == "left"){
        return "6px 0px 0px 6px"
    }
    if(direction.value == "right"){
        return "0px 6px 6px 0px"
    }
})

const eyePosStyle = computed(()=>{
    const head = findHead()
    if(!head) return `radial-gradient(circle at 0% 0%, #000 30%, #fff 35%)`
    let ai = latestApplePos.value.i
    let aj = latestApplePos.value.j
    let hi = head.i
    let hj = head.j
    let eyePos = {v:'',h:''}
    eyePos.v = ai < hi ? "0%" : ai == hi ? "50%" : "100%"
    eyePos.h = aj < hj ? "0%" : aj == hj ? "50%" : "100%"

    return `radial-gradient(circle at ${eyePos.h} ${eyePos.v}, #000 50%, #fff 35%)`
})
/*const eyePosStyle = computed(() => {
    const head = findHead()
    if (!head) return `radial-gradient(circle 2.5px at 50% 50%, #000 98%, #fff 100%)`

    const dx = latestApplePos.value.j - head.j   // columns: + means apple is to the right
    const dy = latestApplePos.value.i - head.i   // rows: + means apple is below
    const dist = Math.hypot(dx, dy) || 1         // || 1 avoids dividing by 0

    const x = 50 + (dx / dist) * 18              // 18% = how far the pupil can move sideways
    const y = 50 + (dy / dist) * 25              // 25% = how far it can move up/down

    return `radial-gradient(circle 2.5px at ${x}% ${y}%, #000 98%, #fff 100%)`
})*/
function onKeydown(e: KeyboardEvent) {
    if (e.key.startsWith('Arrow')) e.preventDefault()
    // stops the page from scrolling
    lastKey.value = { key: e.key }
}

onMounted(() => window.addEventListener('keydown', onKeydown))

let stopKeyWatcher:(()=>void) | null = null

function startKeyWatcher(){
    stopKeyWatcher?.()
    stopKeyWatcher = watch(lastKey,(val)=>{

        if(!val)return
        console.log('triggered watcher')
        console.log(lastKey.value)
        const next = keyDirections[val.key]
        console.log(next)
        if(!next)return;
        if(next == opposite[lastMoved]) return;

        direction.value = next;

    })
}



function findHead() {
    for (let i = 0; i < table.value.length; i++) {
        for (let j = 0; j < table.value[i].length; j++) {

            if (table.value[i][j].snakeTime === length.value) {

                return { i, j }
            }
        }
    }
    return null
}
function borderMaker() {
    for (let i = 0; i < table.value.length; i++) {
        for (let j = 0; j < table.value[i].length; j++) {
            if(i==0|| j==0 || i == table.value.length-1 ||j == table.value[i].length-1){
                table.value[i][j].isBorder = true
            }

        }
    }
    return null
}

function moveSnake(){
    let head = findHead()
    let ni = head.i + moves[direction.value].i
    let nj = head.j + moves[direction.value].j
    const next = table.value[ni][nj]

    if(next.snakeTime>0 || next.isBorder){
        stopGame()
        return;
    }

    if(next.hasApple){
        next.hasApple = false
        length.value ++
        randomAppleGenerator()
    }
    else{
        table.value.flat().forEach((c) => {
            if (c.snakeTime > 0) c.snakeTime--
        })
    }
    next.snakeTime = length.value   // new head, after the countdown
    lastMoved = direction.value



}
let gameTick : ReturnType<typeof setInterval>

function reportTable(){
    console.log(table.value)
}

function randomCell(n: number, m: number) {
    return {
        i: Math.floor(Math.random() * n),
        j: Math.floor(Math.random() * m),
    }
}
function randomAppleGenerator(){
    let bruh = {i:0,j:0}
    do{
        bruh = randomCell(20,20)

    }while(table.value[bruh.i][bruh.j].snakeTime>1||table.value[bruh.i][bruh.j].isBorder ==true)
    table.value[bruh.i][bruh.j].hasApple = true
    latestApplePos.value.i = bruh.i
    latestApplePos.value.j = bruh.j

}
function initialiseSnake(){
    table.value[15][15].snakeTime = 3
    table.value[15][16].snakeTime = 2
    table.value[15][17].snakeTime = 1
}


function eyePosition(){

}


const gameStarted = ref <boolean>(false)

function startGame(){
    gameStarted.value = true
    length.value =3
    direction.value = "left"
    lastMoved = "left"
    initialiseTable()
    borderMaker()
    initialiseSnake()
    randomAppleGenerator()
    initialiseGameTick()
    startKeyWatcher()
    console.log(table.value[15][15].snakeTime)
}


function stopGame(){
    stopKeyWatcher?.()


    clearInterval(gameTick)
}
function resetGame(){
    stopGame()

    startGame()
}


function initialiseGameTick(){
    gameTick = setInterval(()=>{
        moveSnake();
    },100)
}



onMounted(()=>{
    borderMaker();
    }
)


</script>

<template>
    <button @click="startGame" :disabled="gameStarted">start game</button>
    <button @click="resetGame">new game</button>
    <div class="board">
        <div class="row" v-for="(row,r) in table" :key="r">
            <div  v-for="(cell,c) in row" :key="c"
                  class="cell"
                  :class="{
                snake : cell.snakeTime>0,
                apple : cell.hasApple,
                border : cell.isBorder,
                head:cell.snakeTime == length
            }"

                  :style="{
    borderRadius: cell.snakeTime === length ? directionHeadStyle : '0px',
    '--eye-bg': eyePosStyle,
}">

            </div>
        </div>
    </div>
</template>

<style scoped>
.row { display: flex; }
.cell { width: 20px; height: 20px; background: #eee; }
.border { background: #333; }
.snake { background: green; }
.apple { background: red; }

.head {
    background: #1b6b34;

    box-shadow: 0 0 6px rgba(27, 107, 52, 0.8);
    position: relative;
}

/* eyes */
.head::before,
.head::after {
    content: "";
    position: absolute;
    top: 2px;
    width: 8px;
    height: 10px;
    border-radius: 50%;
    background: var(--eye-bg);
}
.head::before { left: 1px; }
.head::after  { right: 1px; }

</style>
