
window.app = new Vue({
    el: '#app',
    data: {
        // System alerts
        alerts: [],
        // Determines if loader is visible
        loader_visible: false,
        // Loader current message
        loader_message: "",
        current_page: {},
        app_loader: {},
        app_navigator: {},
        app_footer: {},
        app_notifications: {},
        apishift: {}
    },
    created() {
        let apishift = new APIShift();
    }
});