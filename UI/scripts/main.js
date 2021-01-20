
window.app = new Vue({
    el: '#app',
    router: new VueRouter({
        routes: []
    }),
    data: {
        // System alerts
        alerts: [],
        // Determines if loader is visible
        loader_visible: false,
        // Loader current message
        loader_message: "",
        app_loader: {},
        app_navigator: {},
        app_footer: {},
        app_notifications: {},
        pages: [],
        apishift: null
    },
    created() {
        this.apishift = new APIShift();

        // Link components & pages to apishift
        APIShift.Loader.load((resolve, reject) => {
            app.app_notifications = APIShift.API.getComponent("notifications");
            app.pages.push({
                path: '/main',
                component: APIShift.API.getPage("main", true)
            });
            resolve(0);
        });


        APIShift.Loader.load((resolve, reject) => {
            app.$router.addRoutes(app.pages);
            // Handle first load of page
            if(app.$route.path == "/") app.$router.push("/main");

            resolve(0);
        });
    }
});