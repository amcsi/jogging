<template>
    <form @submit.prevent="login">
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
  import axios from 'axios';

  export default {
    name: "login-registration",
    data() {
      return {
        email: '',
        password: '',
        passwordConfirm: '',
      };
    },
    methods: {
      login() {
        axios.post('/api/login', {
          username: this.email,
          password: this.password,
        }).then(({ data }) => {
          this.$root.$emit('newTokenReceived', { token: data.access_token });
        }).catch(error => {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to log in');
        });
      },
    },
  };
</script>

<style scoped>

</style>
