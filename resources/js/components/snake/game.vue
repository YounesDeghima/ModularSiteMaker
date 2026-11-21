<script setup lang="ts">
import {ref, computed, watch, onMounted, stop} from "vue";

let n =21

interface coordinates{
    i:number,
    j:number
}
interface tableData{

    snakeTime :number,
    hasApple : boolean,
    isBorder : boolean,
    color : string,
    isHead : boolean,
    clicked : boolean,
    isPortal : boolean,
    portalOrientation :string,
    portalNumber : number
    cor:coordinates


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
    portalOrientation : "none",
    portalNumber:0,
    cor:{i:col,j:row},

})

const table = ref<tableData[][]>(
    Array.from({ length: SIZE }, (_, row) =>
        Array.from({ length: SIZE }, (_, col) => createCell(row, col))
    )
)
interface PortalCell{
    i:number
    j:number
}


interface Portal{
    cells : PortalCell[]
    direction : string
}


const portal1 = ref<Portal>({cells:[],direction:'up'})
const portal2 = ref<Portal>({cells:[],direction:'up'})

function initialiseTable(){
    table.value.forEach((row)=>row.forEach((d)=>{d.isBorder ==true? console.log(".") :d.isBorder = false;d.isPortal=false;d.snakeTime = 0;d.hasApple = false}))
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

const createdPortal1 = ref<boolean>(false)
function onKeydown(e: KeyboardEvent) {
    if (e.key.startsWith('Arrow')) e.preventDefault()
    // stops the page from scrolling
    lastKey.value = { key: e.key }
    e.preventDefault()
    if(e.key == 'k'||e.key=='l'){
        let orientation = direction.value =='up' || direction.value == 'down' ? 'hor' : 'ver'
        if(!createdPortal1.value){
            createdPortal1.value = true
            createPortal(orientation,findHead(),e.key)
        }
        else{
            console.log('yo')
            deletePortal(e.key)
            createPortal(orientation,findHead(),e.key)
        }




    }
    if(e.key=='p'){
        pauseGameTick()
    }
    if(e.key=='u'){
        unpauseGameTick()
    }
}

onMounted(() => window.addEventListener('keydown', onKeydown))

let stopKeyWatcher:(()=>void) | null = null

function startKeyWatcher(){
    stopKeyWatcher?.()
    stopKeyWatcher = watch(lastKey,(val)=>{

        if(!val)return

        console.log(lastKey.value)
        const next = keyDirections[val.key]

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
    table.value.forEach((c)=>c.forEach((x)=>x.snakeTime > 0 ? t.push(x) : console.log('.')))
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



function randomCell(n: number, m: number) {
    let obj={
        i: Math.floor(Math.random() * n),
        j: Math.floor(Math.random() * m),
    }

    return obj
}
function randomAppleGenerator(){
    let bruh = {i:0,j:0}
    do{
        bruh = randomCell(19,19)

    }while(table.value[bruh.i][bruh.j].snakeTime>1||table.value[bruh.i][bruh.j].isBorder ==true)
    table.value[bruh.i][bruh.j].hasApple = true
    latestApplePos.value.i = bruh.i
    latestApplePos.value.j = bruh.j

}

let dirpos = {
    'ver':[{i:-1,j:0},{i:0,j:0},{i:1,j:0}],

    'hor':[{i:0,j:-1},{i:0,j:0},{i:0,j:1}]
}


function findFirstBorder(){
    let head = findHead()
    let dir = direction.value
    let ki = dir == 'up' ? -1 : dir == 'down' ? 1 : 0
    let kj = dir == 'left' ? -1 : dir == 'right' ? 1 : 0
    let inki = ki
    let inkj = kj

    while(table.value[head.i+inki][head.j+inkj].isBorder == false){

        inki = inki + ki
        inkj = inkj + kj
    }

    return {i:head.i+inki,j:head.j+inkj}
}

let borderOnTopOfBorder = {
    'right' : {i:0,j:-1},
    'left' : {i:0,j:1},
    'up' : {i:1,j:0},
    'down' : {i:-1,j:0},

}
let snakePortal = {
    'right' : {i:0,j:1},
    'left' : {i:0,j:-1},
    'up' : {i:-1,j:0},
    'down' : {i:+1,j:0},
}

function canCreatePortal(po:string,cor){

    let portalTable = []
    let pa = dirpos[po]

    let fb = findFirstBorder()
    let x = borderOnTopOfBorder[direction.value]

    for(let i=0;i<pa.length;i++){

        if(!table.value[fb.i+pa[i].i][fb.j+pa[i].j]||table.value[fb.i+pa[i].i+x.i][fb.j+pa[i].j+x.j].isBorder||table.value[fb.i+pa[i].i][fb.j+pa[i].j].isPortal==true){
            return false
        }
    }

    return true

}

function createPortal(po:string,cor,key){
    let pa = []

    if(canCreatePortal(po,cor)){
        let pd = direction.value == 'up' ? 'down' :direction.value=='down' ? 'up' : direction.value == 'left' ?'right' : 'left'
        pa = dirpos[po]
        let fb = findFirstBorder()
        for(let i=0;i<pa.length;i++){
            table.value[fb.i+pa[i].i][fb.j+pa[i].j].isPortal = true
            table.value[fb.i+pa[i].i][fb.j+pa[i].j].isBorder = false


            if(key == 'k'){
                portal1.value.cells.push({i:fb.i+pa[i].i,j:fb.j+pa[i].j})
                table.value[fb.i+pa[i].i][fb.j+pa[i].j].portalNumber = 1
            }
            if(key == 'l'){
                portal2.value.cells.push({i:fb.i+pa[i].i,j:fb.j+pa[i].j})
                table.value[fb.i+pa[i].i][fb.j+pa[i].j].portalNumber = 2
            }

        }
        if(key == 'k'){
            portal1.value.direction = pd
        }
        if(key == 'l'){
            portal2.value.direction = pd
        }

    }
}

function deletePortal(key:string){

    let portal:Portal;
    let orientation = direction.value =='up' || direction.value == 'down' ? 'hor' : 'ver'
    if(key=='k'){
        portal = portal1.value
    }
    else{
        if(key=='l'){
            portal = portal2.value
        }

    }
    console.log(canCreatePortal(orientation,findFirstBorder()))
    if(canCreatePortal(orientation,findFirstBorder())){


            if(portal.cells.length>0){
                for(let i=0;i<portal.cells.length;i++){

                    table.value[portal.cells[i].i][portal.cells[i].j].isPortal=false
                    table.value[portal.cells[i].i][portal.cells[i].j].portalNumber=0
                    table.value[portal.cells[i].i][portal.cells[i].j].isBorder = true


                }
            }

    }
    if(key=='k'){
        portal1.value.cells = []
    }
    else{
        if(key=='l'){
            portal2.value.cells = []
        }

    }


}


function snakeInPortal(){
    let head = findHead()
    let ap ;
    for(let i =0;i<portal1.value.cells.length;i++){
        if(table.value[portal1.value.cells[i].i][portal1.value.cells[i].j].snakeTime == length.value){
            console.log('snake entered Portal 1')
            ap = snakePortal[portal2.value.direction]
            table.value[portal1.value.cells[i].i][portal1.value.cells[i].j].snakeTime = 0
            table.value[portal2.value.cells[i].i+ap.i][portal2.value.cells[i].j+ap.j].snakeTime = length.value
            direction.value = portal2.value.direction



            break;
        }
    }
    for(let i =0;i<portal2.value.cells.length;i++){
        if(table.value[portal2.value.cells[i].i][portal2.value.cells[i].j].snakeTime==length.value){
            ap = snakePortal[portal1.value.direction]
            table.value[portal2.value.cells[i].i][portal2.value.cells[i].j].snakeTime = 0
            table.value[portal1.value.cells[i].i+ap.i][portal1.value.cells[i].j+ap.j].snakeTime = length.value
            direction.value = portal1.value.direction
            break;
        }
    }
}

/*function hasPortal(rc,dr){
    let k;


        for(k=0;k<dirpos[dr].length;k++){
            if(table.value[rc.i+dirpos[dr][k].i][rc.j+dirpos[dr][k].j].isPortal){
                return true
            }
        }

    return false

}
function randomPortalSliceGenerator(){

    let rp = []
    let rc;
    do{
        rc = randomCell(20,20)
        if(rc){
            if(rc.i<19 && rc.i>2){
                if(!hasPortal(rc,'up')&&!hasPortal(rc,'down')){
                    rp.push("down"); rp.push("up")
                }

            }
            else{
                if(rc.i<2){
                    if(!hasPortal(rc,'down')){
                        rp.push("down")
                    }

                }
                else{
                    if(!hasPortal(rc,'up')){
                        rp.push("up")
                    }

                }

            }
            if(rc.j<19 && rc.j>2){
                if(!hasPortal(rc,'left')&&!hasPortal(rc,'right')){
                    rp.push("left"); rp.push("right")
                }

            }
            else{
                if(rc.j<2){
                    if(!hasPortal(rc,'right')){
                        rp.push("right")
                    }

                }
                else{
                    if(!hasPortal(rc,'left')){
                        rp.push("left");
                    }

                }

            }
        }

    }while(rp.length<0)

    return [rp,rc]

}




function randomPortalSliceGenerator2(){
    let [rp,rc] = randomPortalSliceGenerator()
    let i = Math.floor(Math.random()*rp.length)
    let directionRp = rp[i]
    let pd;let md;
    pd = directionRp=='left'||directionRp=='right'?'up':'left'
    md = pd == 'up'?'down':'right'
    const rpf :Portal = {cells:[],direction:{plusDirection:pd,minusDirection:md}}
    for(let k=0;k<3;k++){
        if(directionRp == 'up'){
            table.value[rc.i-k][rc.j].isPortal = true
            rpf.cells.push({i:rc.i-k,j:rc.j})
        }
        if(directionRp == 'down'){
            table.value[rc.i+k][rc.j].isPortal = true
            rpf.cells.push({i:rc.i+k,j:rc.j})
        }
        if(directionRp == 'right'){
            table.value[rc.i][rc.j+k].isPortal = true
            rpf.cells.push({i:rc.i,j:rc.j+k})
        }
        if(directionRp == 'left'){
            table.value[rc.i][rc.j-k].isPortal = true
            rpf.cells.push({i:rc.i,j:rc.j-k})
        }
    }
    return rpf

}


function randomPortalGenerator(){

    portal1.value = randomPortalSliceGenerator2()
    portal2.value = randomPortalSliceGenerator2()
    console.log(portal1,portal2)

}*/
/*function snakeInPortal(){
    for(let i =0;i<2;i++){


            if(table.value[portal1.value.cells[i].i][portal1.value.cells[i].j].snakeTime>0){

            }



            if(table.value[portal2.value.cells[i].i][portal2.value.cells[i].j].snakeTime>0){


            }


    }
}

const enteringPortal1 = ref<boolean>(false)
const enteringPortal2 = ref<boolean>(false)
function portalHasSnake(){
    for(let i =0;i<2;i++){
        if(!enteringPortal2.value){
            enteringPortal1.value=true
            if(table.value[portal1.value.cells[i].i][portal1.value.cells[i].j].snakeTime>0){
                table.value[portal2.value.cells[i].i][portal2.value.cells[i].j].snakeTime = table.value[portal1.value.cells[i].i][portal1.value.cells[i].j].snakeTime
                direction.value = portal2.value.direction.plusDirection

                console.log("snake in portal1")
            }
        }
       if(!enteringPortal1.value){
           enteringPortal2.value=true
           if(table.value[portal2.value.cells[i].i][portal2.value.cells[i].j].snakeTime>0){
               table.value[portal1.value.cells[i].i][portal1.value.cells[i].j].snakeTime = table.value[portal2.value.cells[i].i][portal2.value.cells[i].j].snakeTime
               direction.value = portal1.value.direction.plusDirection
               console.log("snake in portal1")
           }
       }

    }


}*/


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


function pauseGameTick(){
        clearInterval(gameTick)
}


function  unpauseGameTick(){
    initialiseGameTick()
}

function initialiseGameTick(){
    gameTick = setInterval(()=>{
        moveSnake();
        snakeInPortal()
    },1000-clockSpeed.value*90)
}


const lvlBuilderStarted = ref<boolean>(false)
function lvlBuilder(){

    lvlBuilderStarted.value = true

}
function gotClicked(cell: tableData){

    lvlBuilderStarted.value ? cell.isBorder== false ? cell.isBorder = true :cell.isBorder = false : console.log(".")
}

const deleteStarted = ref<boolean>(false)
const drawingStarted = ref<boolean>(false)

onMounted(()=>{
    initialiseTable()
    borderMaker();
    }
)



</script>

<template>
    <div class="board" :class="{ building: lvlBuilderStarted, deleting: deleteStarted }" @keydown.>
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
                head:cell.snakeTime == length,
                portalN1 : cell.portalNumber == 1,
                portalN2 : cell.portalNumber == 2
            }"
                      @click="lvlBuilderStarted ? gotClicked(cell) : console.log('.')"
                      @mousedown.prevent="drawingStarted = true"
                      @mouseenter="drawingStarted?gotClicked(cell):console.log(`.`)"
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
.portalN1 { background: blue}
.portalN2 {background: orange}

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
