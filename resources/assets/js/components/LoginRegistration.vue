<template>
    <div v-if="! token">
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

        <b-btn variant="primary" @click="login">Login / Register</b-btn>
    </div>
</template>

<script>
  import axios from 'axios';

  export default {
    name: "login-registration",
    props: ['token'],
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
          console.info('successHere', data);
          this.$root.$emit('loginSuccess', { token: data.access_token });
        }).catch(error => {
          console.error('error', error);
          try {
            if (error.response.data.message) {
              toast.displayError(`Login failure: ${error.response.data.message}`);
              return;
            }
          } catch (e) {}
          toast.displayError('Login failure');
        });
      },
    },
  };
</script>

<style scoped>

</style>
