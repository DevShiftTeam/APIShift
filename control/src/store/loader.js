export default {
    namespaced: true,
    state: {
        visible: false,
        message: "",
        processes: 0,
    },

    mutations: {
        SET(state, component) {
            state.component = component;
        },
    },
    actions: {},
    getters: {
        LOADER(state) {
            return state.component;
        },
    },
};