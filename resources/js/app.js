import {createApp} from "vue";
import in_decrementor from "./components/index/in_decrementor.vue"

const app = createApp({})

app.component('in_decrementor',in_decrementor)

app.mount('#app')
