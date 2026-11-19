<script setup lang="ts">
import {ref, computed, watch, onMounted, stop} from "vue";

let n =21

interface tableData{

    snakeTime :number,
    hasApple : boolean,
    isBorder : boolean,
    color : string,
    isHead : boolean,
    clicked : boolean,
    isPortal : boolean,
    portalOrientation :string

}

const clockSpeed = ref<number>(5)
const SIZE = 21

const createCell = (row: number, col: number): tableData => ({
    snakeTime: 0,
    hasApple: false,
    isBorder: row === 0 || col === 0 || row === SIZE - 1 || col === SIZE - 1,
    isHead:false,
    color: 'red',
    clicked : false,
    isPortal : false,
    portalOrientation : "none"
})

const table = ref<tableData[][]>(
    Array.from({ length: SIZE }, (_, row) =>
        Array.from({ length: SIZE }, (_, col) => createCell(row, col))
    )
)
function initialiseTable(){
    table.value.forEach((row)=>row.forEach((d)=>{d.isBorder ? console.log("i did nothing") :d.isBorder = false;d.snakeTime = 0;d.hasApple = false}))
}

let normalMode = "normalMode"
let twinMode = "twinMode"

const gameMode = ref<"normalMode"|"twinMode">("normalMode")

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

function findCell(time: number) {
    for (let i = 0; i < table.value.length; i++) {
        for (let j = 0; j < table.value[i].length; j++) {
            if (table.value[i][j].snakeTime === time) return { i, j }
        }
    }
    return null
}
function reverseSnake(){
    let t = []
    let ph = null //placeholder
    table.value.forEach((c)=>c.forEach((x)=>x.snakeTime > 0 ? t.push(x) : console.log('niggers')))
    t.sort((a,b)=>a.snakeTime - b.snakeTime)

    let tail = findCell(1)
    let beforeTail = findCell(2)
    let di = tail.i - beforeTail.i
    let dj = tail.j - beforeTail.j




    for(let i=0;i<t.length/2;i++){
        ph = t[i].snakeTime
        t[i].snakeTime = t[t.length - i - 1].snakeTime
        t[t.length - i - 1].snakeTime = ph
    }
    direction.value = di == 1 ? "down" : di == -1 ? "up" : dj == 1 ? "right" : "left"
    lastMoved = direction.value

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
    let ateAnApple = false
    if(next.hasApple){
        next.hasApple = false
        length.value ++
        ateAnApple = true
        randomAppleGenerator()
    }
    else{
        table.value.flat().forEach((c) => {
            if (c.snakeTime > 0) c.snakeTime--
        })
    }
    next.snakeTime = length.value
    if(gameMode.value == "twinMode" && ateAnApple == true){
        reverseSnake()
    }
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

function hasPortal(rc){
    let si = rc.i-1 > 0 ? rc.i-1 : si
    let ei = rc.i+1
    let
}
function randomPortalSliceGenerator(rc){
    let rp = []
    if(rc.i<19 && rc.i>2){
        rp.push("down"); rc.push("up")
    }
    else{
        if(rc.i<2){
            rp.push("down")
        }
        else{
            rp.push("up")
        }

    }
    if(rc.j<19 && rc.j>2){
        rp.push("left"); rc.push("right")
    }
    else{
        if(rc.j<2){
            rp.push("right")
        }
        else{
            rp.push("left")
        }

    }
    return rp

}

let rpDirections = {

}


function randomPortalSliceGenerator2(rc,rp){
    let i = Math.floor(Math.random()*rp.length())
    let directionRp = rp[i]
    let rpf = []
    for(let k=0;k<2;k++){
        if(directionRp == 'up'){
            table.value[rc.i-k][rc.j].isPortal = true
        }
        if(directionRp == 'down'){
            table.value[rc.i+k][rc.j].isPortal = true
        }
        if(directionRp == 'right'){
            table.value[rc.i][rc.j+k].isPortal = true
        }
        if(directionRp == 'left'){
            table.value[rc.i][rc.j-k].isPortal = true
        }
    }

}


function randomPortalGenerator(){
    let rc = randomCell(20,20)
    let rpA = randomPortalSliceGenerator(rc)




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
    lvlBuilderStarted.value = false
    gameStarted.value = true
    length.value =3
    direction.value = "left"
    lastMoved = "left"
    initialiseTable()

    initialiseSnake()
    randomAppleGenerator()
    initialiseGameTick()
    startKeyWatcher()
    console.log(table.value[15][15].snakeTime)
}


function stopGame(){
    gameStarted.value = false
    initialiseTable()
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
    },1000-clockSpeed.value*90)
}


const lvlBuilderStarted = ref<boolean>(false)
function lvlBuilder(){

    lvlBuilderStarted.value = true

}
function gotClicked(cell: tableData){

    lvlBuilderStarted.value ? cell.isBorder== false ? cell.isBorder = true :cell.isBorder = false : console.log("niggers")
}

const deleteStarted = ref<boolean>(false)
const drawingStarted = ref<boolean>(false)

onMounted(()=>{
    initialiseTable()
    borderMaker();
    }
)
watch(drawingStarted,(val)=>{
    console.log("drawingStarted value changed ")
})


</script>

<template>
    <div class="board" :class="{ building: lvlBuilderStarted, deleting: deleteStarted }">
        <button @click="lvlBuilder" :disabled="gameStarted"  class="tool"
                :class="{ on: lvlBuilderStarted }">lvl builder: {{ lvlBuilderStarted ? 'ON' : 'OFF' }}</button>

        <select  v-model="clockSpeed" :disabled="gameStarted">
            <option v-for="i in 10">
                {{i}}
            </option>
        </select>
        <button @click="startGame" :disabled="gameStarted">start game</button>
        <button @click="resetGame">new game</button>
        <select v-model="gameMode" :disabled="gameStarted">

            <option :value="normalMode">normal mode</option>
            <option :value="twinMode">twin mode</option>
        </select>
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
                      @click="lvlBuilderStarted ? gotClicked(cell) : console.log('nothing will happen')"
                      @mousedown.prevent="drawingStarted = true"
                      @mouseenter="drawingStarted?gotClicked(cell):console.log(`btuh`)"
                      @mouseup="drawingStarted = false"

                      :style="{
    borderRadius: cell.snakeTime === length ? directionHeadStyle : '0px',
    '--eye-bg': eyePosStyle,
}">

                </div>
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


.tool {
    padding: 6px 12px;
    border: 2px solid #888;
    border-radius: 6px;
    background: #f3f3f3;
    color: #333;
    cursor: pointer;
    transition: background-color 120ms, border-color 120ms, color 120ms;
}
.tool:hover:not(:disabled) { border-color: #444; }
.tool:disabled { opacity: 0.5; cursor: not-allowed; }

/* ON state: builder is green, delete is red */
.tool.on {
    background: #2e9e4f;
    border-color: #1b6b34;
    color: white;
    box-shadow: 0 0 6px rgba(46, 158, 79, 0.6);
}
.tool.delete.on {
    background: #d33;
    border-color: #911;
    box-shadow: 0 0 6px rgba(211, 51, 51, 0.6);
}

/* board feedback while building */
.board.building .cell { cursor: pointer; }
.board.building .cell:hover { outline: 2px solid #2e9e4f; outline-offset: -2px; }
.board.deleting .cell:hover { outline-color: #d33; }
</style>
