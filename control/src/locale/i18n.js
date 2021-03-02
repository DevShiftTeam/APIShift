import Vue from 'vue'
import VueI18n from 'vue-i18n'
import enMessage from './en.json'

Vue.use(VueI18n)

const messages = {
    en: enMessage
}

const i18n = new VueI18n({
    locale: 'en',
    messages,
    fallbackLocale: 'vn',
    silentFallbackWarn: true
})

export default i18n