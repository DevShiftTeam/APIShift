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
                item: 3,
                items: [
                    {
                        title: 'Manage Sessions',
                        subtitle: 'Create session structures and hierarchy as you desire easily'
                    },
                    {
                        title: 'Manage Database Structure & Objects',
                        subtitle: 'Create Item and Relations between them that define your application\'s data'
                    },
                    {
                        title: 'Manage Access To Controllers & Data',
                        subtitle: 'Define which user can access what function and data in your system'
                    }
                ]
            };
        },
        created() {
            APIShift.Loader.changeLoader("main");
        },
        methods: {

        }
    };
</script>

<template>
    <v-content>
        <v-container class="main_container" fluid>
            <v-card class="mx-auto" width="50%" color="light-blue lighten-1"><v-toolbar-title class="version_header">Version 1.0</v-toolbar-title></v-card>
            <v-card class="mx-auto" width="70%" tile>
                <v-list three-line>
                    <v-subheader>New Control Panel Features</v-subheader>
                    <v-list-item-group v-model="item" color="primary">
                        <v-list-item v-for="(item, i) in items" :key="i">
                            <v-list-item-content>
                                <v-list-item-title v-html="item.title"></v-list-item-title>
                                <v-list-item-subtitle v-html="item.subtitle"></v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-card>
        </v-container>
    </v-content>
</template>
<style scoped>
.version_header {
    text-align: center;
}

.main_container > * {
    margin-bottom: 20px;
}
</style>