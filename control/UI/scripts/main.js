/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

window.app = new Vue({
    el: '#app',
    router: new VueRouter({
        routes: []
    }),
    vuetify: new Vuetify({
        theme: { dark: true }
    }),
    props: {
        source: String
    },
    data: {
        // System alerts
        alerts: [],
        // Determines if loader is visible
        loader: {
            visible: false,
            message: "",
            processes: 0
        },
        app_loader: Vue.extend({ template: "<div></div>" }),
        app_navigator: Vue.extend({ template: "<div></div>" }),
        app_footer: Vue.extend({ template: "<div></div>" }),
        app_notifications: Vue.extend({ template: "<div></div>" }),
        apishift: null
    },
    created() {
        // Initialize APIShift Engine
        this.apishift = new APIShift(this.loader);
        
        // Link components to apishift
        APIShift.Loader.load(function () {
            app.app_notifications = APIShift.components["notifications"];
            if(!APIShift.load_components) return; //  Don't load other components if system isn't installed
            app.app_loader = APIShift.components["loader"];
            app.app_navigator = APIShift.components["navigator"];
            app.app_footer = APIShift.components["footer"];
        }).then(() => {
            // Add loaded routes & pages
            app.$router.addRoutes(APIShift.admin_routes);

            // Handle first load of page
            if(!APIShift.installed) app.$router.push("/installer");
            else if(!APIShift.logged_in) app.$router.push("/login");

            // Navigation gaurd for control panel
            app.$router.beforeEach((to, from, next) => {
                // Move to installation if not installed
                if(to.path != "/installer" && !APIShift.installed) next("/installer");
                // Move to login if not authenticated
                else if(to.path != "/login" && !APIShift.logged_in & APIShift.installed) next("/login");
                // Move to main if authenticated
                else if((to.path == "/login" || to.path == "/") && APIShift.logged_in) next("/main");
                else next();
            })
        });
    }
});