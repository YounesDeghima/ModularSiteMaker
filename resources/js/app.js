import {createApp} from "vue";
import in_decrementor from "./components/index/in_decrementor.vue"
import colorChangingButton from "./components/index/colorChangingButton.vue"
import remove_addButton from "./components/index/remove_addButton.vue"

const app = createApp({})

app.component('in_decrementor',in_decrementor)
app.component('colorChangingButton',colorChangingButton)
app.component('remove_addButton',remove_addButton)

app.mount('#app')
