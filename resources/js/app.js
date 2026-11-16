import {createApp} from "vue";
import in_decrementor from "./components/index/in_decrementor.vue"
import colorChangingButton from "./components/index/colorChangingButton.vue"
import remove_addButton from "./components/index/remove_addButton.vue"
import for_vees from "./components/index/for_vees.vue"
import models from "./components/index/models.vue"
import templateRef from "./components/index/templateRef.vue"

const app = createApp({})

app.component('in_decrementor',in_decrementor)
app.component('colorChangingButton',colorChangingButton)
app.component('remove_addButton',remove_addButton)
app.component('for_vees',for_vees)
app.component('models',models)
app.component('templateRef',templateRef)

app.mount('#app')
