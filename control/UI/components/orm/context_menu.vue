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


    module.exports = {
        props: {
            actions: Object,
            position: Object,
            is_sub_menu: Boolean
        },
        data () {
            return {
                hovered_index: -1,
            }
        },
        created () {
            if (!window.context_menu) window.context_menu = this;
        },
        mounted () {
            // Detect outside clicks and close context menu
            if (!this.$props.is_sub_menu) {
                window.addEventListener('pointerdown', this.window_pointer_down);
            }
        },
        beforeDestroy() {
            if (!this.$props.is_sub_menu) {
                window.removeEventListener('pointerdown', this.window_pointer_down);
                window.context_menu = undefined;
            }
        },
        computed: {
            transformation: function() {
                return {
                    top: `${this.$props.position.y}px`,
                    left:  `${this.$props.position.x}px`,
                    'z-index':  graph_view.elements.length + 1
                }
            }
        },
        methods: {
            window_pointer_down(event) {
                if (!event.target.closest('#context_menu_primary')) {
                    graph_view.contextmenu.is_active = false;
                }
            }
        }
    }
</script>

<template>
    <v-list dense :id="is_sub_menu ? 'context_menu' : 'context_menu_primary'" :style="is_sub_menu ? {} : transformation">
        <v-list-item v-for="(item,index) in actions"
            :key="index"
            @pointerover.prevent="hovered_index = index" 
            @pointerleave="hovered_index = -1" 
            @click.prevent="item.starter()">
            <v-list-item-action>
                <v-icon>{{item.icon}}</v-icon>
            </v-list-item-action>
            <v-list-item-content>
                <v-list-item-title>{{item.name}}</v-list-item-title>
            </v-list-item-content>
            <component v-if="hovered_index == index"
                :is="graph_view.components.context_menu"
                :actions="item.actions"
                :is_sub_menu="true">
            </component> 
        </v-list-item>
    </v-list>
</template>

<style scoped>
/* Please style this crap, with style */

#context_menu, #context_menu_primary {
    position: absolute;
    background: rgba(0, 0, 0, 0.85);
    transform-origin: 0 0;
    left: 100%;
    top: 0%;
}
</style>