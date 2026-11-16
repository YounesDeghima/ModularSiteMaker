import {createApp} from "vue";
import game from "../components/snake/game.vue"

const snakeGame = createApp({})
snakeGame.component("game",game)

snakeGame.mount("#snakeGame")
