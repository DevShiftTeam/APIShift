export default {
    namespaced: true,
    state: {
        isAuthenticated: false
    },

    mutations: {
        SET(state, isAuthenticated) {
            state.isAuthenticated = isAuthenticated;
        },
    },
    actions: {},
    getters: {
        isAuthenticated(state) {
            return state.isAuthenticated;
        },
    },
};