<template>
    <form @submit.prevent="$root.$emit('doLogin', { email, password, vm: this })">
        <h2>Login form</h2>

        <b-form-group horizontal
            :label-cols="4"
            label="Email address"
        >
            <b-form-input v-model.trim="email"></b-form-input>
        </b-form-group>

        <b-form-group horizontal
            :label-cols="4"
            label="Password"
        >
            <b-form-input type="password" v-model.trim="password"></b-form-input>
        </b-form-group>

        <b-btn variant="primary" type="submit">Login / Register</b-btn>
    </form>
</template>

<script>
  export default {
    name: "login-registration",
    data() {
      return {
        email: '',
        password: '',
        passwordConfirm: '',
      };
    },
  };
</script>

<style scoped>

</style>
