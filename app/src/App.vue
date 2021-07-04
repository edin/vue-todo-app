<template>
    <v-app>
        <v-app-bar app color="primary" dark>
            <v-toolbar-title>Todo Application</v-toolbar-title>
            <v-spacer></v-spacer>
        </v-app-bar>

        <v-main>
            <router-view />
        </v-main>
    </v-app>
</template>

<script lang="ts">
import Vue from "vue";

export default Vue.extend({
    name: "App",

    data: () => ({
        //
    }),
});
</script>
