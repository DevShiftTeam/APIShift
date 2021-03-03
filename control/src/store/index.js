import Vue from "vue";
import auth from "./auth";
import Vuex from "vuex";
import createPersistedState from "vuex-persistedstate";

Vue.use(Vuex);
Vue.config.productionTip = false;

export default new Vuex.Store({
    modules: {
        auth: auth,
    },
    plugins: [createPersistedState()],
    state: {
        navigator: null,
        footer: null,
        loader: null,
    },
    mutations: {
        SET_NAVIGATOR(state, comp) {
            state.navigator = comp;
        },
        SET_FOOTER(state, comp) {
            state.footer = comp;
        },
        SET_LOADER(state, comp) {
            state.loader = comp;
        },
    },
    getters: {
        NAVIGATOR(state) {
            return state.navigator;
        },
        FOOTER(state) {
            return state.footer;
        },
        LOADER(state) {
            return state.loader;
        },
    },
});