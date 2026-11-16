import {createApp} from "vue";
import game from "../components/memory_card_game/game.vue"

const memory_card_game = createApp({})

memory_card_game.component('game',game)

memory_card_game.mount("#memory_card_game")
