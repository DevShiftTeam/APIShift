/**
 * APIShift Engine v1.0.0
 *
 * Copyright 2020-present Sapir Shemer, DevShift (devshift.biz)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Sapir Shemer
 */
import Vue from "vue";
import App from "./App.vue";
import Vuetify from "vuetify/lib";
import VueRouter from "vue-router";
import i18n from "./locale/i18n";
import store from "./store/index";
import { APIShift as APIShiftClass } from "./scripts/APIShift.js";
import Vuebar from 'vuebar';
import Main from './pages/main'
Vue.use(Vuebar);
Vue.use(VueRouter);
Vue.use(Vuetify);

let vue = new Vue({ store });
window.app = new Vue({
    i18n,
    store: store,
    router: new VueRouter({
        routes: [],
    }),
    vuetify: new Vuetify({
        theme: {
            dark: true,
            light: {
                primary: "#003060",
            },
            themes: {
                dark: {
                    primary: "#a8daff",
                },
            },
        },
    }),
    props: {
        source: String,
    },
    data: {
        // System alerts
        alerts: [],
        // Determines if loader is visible
        loader: {
            visible: false,
            message: "",
            processes: 0,
        },
        apishift: null,
    },
    methods: {},
    render: (h) => h(App),
}).$mount("#app");

export default window.app;