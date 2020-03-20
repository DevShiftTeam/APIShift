<script>
    /**
     * APIShift Engine v1.0.0
     * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
     * Released under the MIT License with the additions present in the LICENSE.md
     * file in the root folder of the APIShift Engine original release source-code
     * @author Sapir Shemer
     */
</script>

<template>
    <v-content>
        <v-container fluid fill-height>
            <v-toolbar class="mx-auto" max-width="90%">
                <v-toolbar-title>Manage Sessions</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon>
                    <v-icon>mdi-plus-circle</v-icon>
                </v-btn>
            </v-toolbar>
            <v-card class="mx-auto session-editor" width="90%" min-height="75%" outlined elevation="2">
                <v-layout class="mx-auto" align-start justify-center row wrap>
                    <v-hover v-slot:default="{ hover }">
                        <v-card outlined class="px-0 session-card" :elevation="hover ? 16 : 2">
                            <v-card-title>
                                <v-icon left>
                                    fas fa-user
                                </v-icon>
                                ADMIN_STATE
                                <v-spacer></v-spacer>
                                <v-btn icon>
                                    <v-icon>mdi-pencil-box</v-icon>
                                </v-btn>
                                <v-btn icon>
                                    <v-icon>mdi-minus-circle</v-icon>
                                </v-btn>
                                <v-btn icon>
                                    <v-icon small>fas fa-lock</v-icon>
                                </v-btn>
                            </v-card-title>

                            <v-card-text>
                                <v-form>
                                    <v-text-field class="session_field" type="text" lable="name" id="name" value="ADMIN_STATE" box disabled></v-text-field>
                                    <v-text-field class="session_field" type="number" lable="active timeout" box disabled value="0"></v-text-field>
                                    <v-text-field class="session_field" type="number" lable="inactive timeout" box disabled value="0"></v-text-field>
                                </v-form>
                            </v-card-text>

                            <v-card-actions>
                                <v-btn text color="purple accent-4" width="100%">
                                    View Children
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-hover>
                </v-layout>
            </v-card>
        </v-container>
    </v-content>
</template>
<style scoped>

.session-editor {
    padding: 10px;
    margin-top: 5px;
}

.session-card {
    margin: 5px;
    min-width: 300px;
}

.session_field {
    padding-top: 0;
}

</style>