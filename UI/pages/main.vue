<script>
    // This shit is made for scripting
    module.exports = {
        vuetify: new Vuetify({
            theme: { dark: false }
        }),
        data () {
            return {
                drawer: null
            }
        },
        created () {
            app.apishift.setSubtitle("Welcome!");
        }
    }
</script>

<template>
    <v-app class="main">
        <h1>Welcome! :)</h1>
        <hr />
        <p>
            Go to <a href="/control">Control Panel</a> to start working with the system
        </p>
    </v-app>
</template>

<style scoped>
/* Please style this crap, with style */
</style>