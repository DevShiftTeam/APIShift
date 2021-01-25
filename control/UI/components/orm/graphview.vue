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
     * @author Sapir Shemer
     */

    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: null,
                item_comp: APIShift.API.getComponent('orm/item', true),
                items: [
                    { is_relation: false, name: "wait", component_id: 0 },
                    { is_relation: false, name: "haha", component_id: 1 }
                ],
                relative: {
                    x: 0,
                    y: 0
                },
                scale: 1,
                /* Drag & Drop functional data */
                event_list: [],
                event_target: Element || Object,
            }
        },
        created () {
            for(var x in [...Array(100).keys()]) {
                this.items.push({
                    is_relation: false, name: "w" + x
                })
            }
        },
        mounted () {
            this.$el.type = "graphview";
            this.$refs['graphview'] = this;
        },
        updated () {
            /* The $refs Array returns each item as Array (because of v-for), so we normalize it to use item[0] directly on key reference */
            function normalizeArray (arr) {
                console.log(arr);
                return arr.map((item) => Array.isArray(item) ? item[0] : item)
            }
            function objectMap(object, mapFn) {
                return Object.keys(object).reduce(function(result, key) {
                    result[key] = mapFn(object[key])
                    return result
                }, {})
            }

            this.$refs = objectMap(this.$refs, (item) => Array.isArray(item) ? item[0] : item);
            console.log(this.$refs);
        },
        methods: {
            pointer_down(event) {
                // Add event to event cache, determine interactive target 
                this.event_list.push(event);
                
                // Mobile Multi-Touch Zoom-In / Zoom-Out gesture 
                if (this.event_list.length === 2) {
                    this.event_target = 'graphview';
                }
                // viewport panning / element movement 
                if (this.event_list.length === 1) {
                    this.event_target = event.target.closest('.graph_line')
                    || event.target.closest('.graph_element') 
                    || event.target.closest('#graphview');

                    if (event.ctrlKey) {
                        this.remove_event(event);
                    }   
                    else if (this.event_target.type === 'graph_line') {
                        // Line click functionality
                        this.event_target.type = 'graph_line';

                    } else if (this.event_target.type === 'graph_element') {
                            // Element click functionality
                            console.log(this.$refs[this.event_target.ref]);
                            this.$refs[this.event_target.ref].drag_start();
                    } else if (this.event_target.type === 'graphview') {
                        // Graph (drawer) click functionality - maybe someday beneficial
                        console.log('graphview!!');
                    }
                    console.log(this.event_list);
                    // this.event_target = this.$refs[this.event_target.ref];
                    console.log(this.event_target);
                }
            },
            pointer_move(event) {
                // Calculate mouse position on screen for relative scaling
                let graph_rect     = this.$el.getBoundingClientRect();
                this.relative['x'] = event.clientX - graph_rect.left;
                this.relative['y'] = event.clientY - graph_rect.top;

                // Discard irrelevent mouse movenents on desktops
                if (this.event_list.length === 0 || !this.event_target) {
                    return;
                }
                // Element click functionality - basiaclly thats it .. Maybe 3d movement on future version ????
                if (this.event_target.type === 'graph_line') {
                    // Line click functionality
                    /* Fill, coming soon  */
                }
                // Element click functionality - basiaclly thats it .. Maybe 3d movement on future version ????
                if (this.event_target.type === 'graph_element') {
                    if (this.event_list.length === 2) {
                        
                    }
                    // console.log(event.clientX - this.event_list[0].clientX);
                    const vmove = [ event.clientX - this.event_list[0].clientX,
                                    event.clientY - this.event_list[0].clientY ];
                    this.$refs[this.event_target.ref] ? this.$refs[this.event_target.ref].drag(vmove) : null;
                }
                if (this.event_target.type === 'graphview') {
                    if (this.event_list.length === 1) {
                        // Graph (drawer) viewport panning - move all element by a calculated vector 
                        const vmove = [ event.clientX - this.event_list[0].clientX,
                                        event.clientY - this.event_list[0].clientY ];
                                        console.log('asdas');
                        for (const item of this.items) {
                            console.log(this.$refs);
                            this.$refs[item.name] ? this.$refs[item.name].drag(vmove) : null;
                        }
                    }
                    if (this.event_list.length === 2) {
                        // Scaling Logic, cooming soon //
                        
                    }
                }
                this.update_event(event);
                // console.log(this.event_list.length , this.event_target);
            },
            pointer_up(event) {
                // Remove event from event cache
                this.remove_event(event);
                this.event_target = this.event_list.length ? this.event_target : null;

                if (this.event_list.length < 2) {
                    this.event_list = [];
                }
                console.log(this.event_target);
            },
            // Every pointer having his own pointer ID, we can use it for multi-pointer events manipulations
            remove_event(event) {
                    for (var i = 0; i < this.event_list.length; i++) {
                        if (event.pointerId == this.event_list[i].pointerId) {
                            this.event_list.splice(i, 1);
                            break;
                        }
                    }
            },
            update_event(event) {
                    for (var i = 0; i < this.event_list.length; i++) {
                        if (event.pointerId == this.event_list[i].pointerId) {
                            this.event_list[i] = event;
                            break;
                        }
                    }
            },
            wheel (event) {
                let self = this;

                    var delta = event.deltaY;
                    if (event.deltaMode > 0) delta *= 100;

                    var sign = Math.sign(delta), speed = 1;
                    var deltaAdjustedSpeed = Math.min(0.25, Math.abs(speed * delta / 128));

                    console.log('1 scale by ' + (1 - sign * deltaAdjustedSpeed));
                    self.scale *= (1 - sign * deltaAdjustedSpeed);
            },
            yes() {
                return 'yes';
            }
        }
    }
</script>

<template ref="graphview">
    <div id="graphview"
        @pointerdown.prevent="pointer_down"
        @pointermove.prevent="pointer_move"
        @pointerup.prevent="pointer_up"
        @wheel.prevent="wheel">
        <component
            v-for="item in items"
            :is="item_comp"
            :key="item.name"
            :ref="item.name"
            :relative="relative"
            :is_relation="item.is_relation"
            :scale="scale"
            :name="item.name">
            
            </component>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
#graphview {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>