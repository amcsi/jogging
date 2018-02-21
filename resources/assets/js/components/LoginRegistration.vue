<template>
    <div>
        <h2>Login form</h2>

        <b-form-group horizontal
            :label-cols="4"
            description="Let us know your name."
            label="Email address"
        >
            <b-form-input v-model.trim="email"></b-form-input>
        </b-form-group>

        <b-form-group horizontal
            :label-cols="4"
            description="Password"
            label="Password"
        >
            <b-form-input type="password" v-model.trim="password"></b-form-input>
        </b-form-group>

        <b-btn variant="primary" @click="login">Login / Register</b-btn>
    </div>
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
        console.info('login');
        axios.post('/api/login', {
          username: this.email,
          password: this.password,
        }).then(({ data }) => {
          this.$emit('login.success', { token: data.data.access_token });
        }).catch(() => {
          this.$emit('error', 'Login failure');
        });
      },
    },
  };
</script>

<style scoped>

</style>
