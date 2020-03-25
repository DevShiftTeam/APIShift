<script>
    /**
     * APIShift Engine v1.0.0
     * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
     * Released under the MIT License with the additions present in the LICENSE.md
     * file in the root folder of the APIShift Engine original release source-code
     * @author Sapir Shemer
     */

    module.exports = {
        data() {
            return {
                is_main_page: true
            }
        },
        created() {
            this.is_main_page = app.$router.currentRoute.path === '/access';
        },
        beforeRouteUpdate (to, from, next) {
            if(to.path == '/access' || to.path == '/access/') this.is_main_page = true;
            else this.is_main_page = false;
            next();
        }
    };
</script>

<template>
    <v-content>
        <v-container fluid fill-height v-if="is_main_page">
            <v-layout class="mx-auto" align-start justify-center row wrap>
                    <v-card to="/access/session" class="page-card" elevation-4>
                        <v-container>
                            <v-row justify="space-between">
                                <v-col>
                                    <v-list-item-title>Session Access</v-list-item-title>
                                    <v-list-item-subtitle>Manage session access and authorization processes</v-list-item-subtitle>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card>
                    <v-card to="/access/controllers" class="page-card" elevation-4>
                        <v-container>
                            <v-row justify="space-between">
                                <v-col>
                                    <v-list-item-title>Controllers Access</v-list-item-title>
                                    <v-list-item-subtitle>Manage controllers access and authorization processes</v-list-item-subtitle>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card>
            </v-layout>
        </v-container>
        <router-view v-else></router-view>
    </v-content>
</template>
<style scoped>
.page-card {
    margin: 5px;
    min-width: 300px;
}

.page-card:hover {
    cursor: pointer;
}
</style>