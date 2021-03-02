module.exports = {
    root: true,
    env: {
        node: true,
    },
    extends: ["plugin:vue/essential", "eslint:recommended"],
    parserOptions: {
        parser: "babel-eslint",
    },
    rules: {
        "no-console": process.env.NODE_ENV === "production" ? "error" : "off",
        "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
        "no-undef": "warn",
        "no-unused-vars": "off",
        "no-extra-semi": "off",
        "no-unreachable": "off",
        "vue/no-use-v-if-with-v-for": "off",
        "vue/no-unused-vars": "off"
    },
    overrides: [{
        files: ["src/**/*.js"],
    }, ]
};