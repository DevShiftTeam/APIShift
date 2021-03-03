export default {
    namespaced: true,
    state: {
        component: null
    },

    mutations: {
        SET(state, component) {
            state.component = component;
        },
    },
    actions: {},
    getters: {
        NAVIGATOR(state) {
            return state.component;
        },
    },
};