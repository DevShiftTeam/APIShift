<script>
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
     * @author Ilan Dazanashvili
     */

    //TODO: Design better z-index system.
    module.exports = {
        props: {
            position: Object,
            menu: Object,
            data: Object,
            parent: Object
        },
        data () {
            return {
            }
        },
        created () {
        },
        mounted () {
            
        },
        methods: {

        }, 
    }
</script>

<template>
        <v-list class="single_menu" :style="{ top: `${this.$props.position.y}px`, left: `${this.$props.position.x}px`}">
            <v-list-item v-for="(item, index) in menu" :key="index" @click="item.handler(data)">
                {{ item.name }}
                <v-spacer></v-spacer>
                <v-icon>chevron-right</v-icon>
            </v-list-item>  
        </v-list>
</template>

<style scoped>
/* Please style this crap, with style */

.single_menu {
    position: absolute;
    left: 50px;
    top: 50px;
    width: 50px;
    height: 50px;
    transform-origin: 0 0;
    background: red;
}
</style>