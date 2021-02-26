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
            camera: Object,
            height: Number,
            width: Number,
            scale: Number
        },
        data () {
            return {
                aspect_ratio: 1,
                view_camera_ratio: 1
            }
        },
        created () {
            this.aspect_ratio = this.height / this.width;
            console.log(this.aspect_ratio);
        },
        mounted () {
            this.graph_rect = graph_view.$el.getBoundingClientRect();
            console.log(this.height);
            console.log(this.width);
            // let rect = this.$el.getBoundingClientRect();

            // // Define accesors for covinient use of camera.x/y, relative to map itself
            // Object.defineProperty(this.camera, "x", {
            //     set: function (x) {
            //         this.x_pos = rect.x + x;
            //     },
            //     get: function () {
            //         return this.x_pos - rect.x;
            //     }
            // });
            // Object.defineProperty(this.camera, "y", {
            //     set: function (y) {
            //         this.y_pos = rect.y + y;
            //     },
            //     get: function () {
            //         return this.y_pos - rect.y;
            //     }
            // });


            
            // this.camera.width = window.init_rect.width / (rect.width )
            
            // let self = this;
            // Object.defineProperty(graph_view.camera, "x", {
            //     set: function (x) {
            //         console.log(self.camera);
            //         self.camera.x = x;
            //     },
            // });
            // Object.defineProperty(graph_view.camera, "y", {
            //     set: function (y) {
            //         self.camera.y = y;
            //     },
            // });

        },
        methods: {
            pointer_down() {
            }
        },
        watch: {

        },
        computed: {
            // Just for testing 

        }
    }
</script>

<template>
    <div id="map_view" :style="{ 'height': `${200 * aspect_ratio}px` , 'width': `${200 }px` }">
        <div id="camera" 
                        :style="{ 
                                height: `${100 * camera.height}%`,
                                width: `${100 * camera.width}%`,
                                transform: `translate(${this.$props.camera.x * 200 }px,${this.$props.camera.y * aspect_ratio * 200}px)`
                            }"></div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */    
    #map_view {
        position: absolute;
        width: 150px;
        height: 150px;
        background: beige;
        right: 0;
        top: 0;
        z-index: 200;
    }

    #map_view #camera {
        position: absolute;
        width: 50px;
        height: 50px;
        background: black;

        z-index: 5;
    }
    
</style>