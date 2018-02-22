<template>
    <div>
        <vue-toast ref="toast" />

        <b-container @login.success="loginSuccess()">
            <login-registration />
        </b-container>
    </div>
</template>

<script>
  import LoginRegistration from './components/LoginRegistration';
  import vueToast from 'vue-toast';
  import toastRegisterer from './toastRegisterer';

  export default {
    name: "app",
    components: { LoginRegistration, vueToast },
    data() {
      return {
        token: '',
        messages: {},
      };
    },
    methods: {
      loginSuccess({ token }) {
        this.messages = { successMessage: "Login successful!" };
      },
      failure(message) {
        this.messages = { errorMessage: message };
      },
    },
    mounted() {
      toastRegisterer(this.$refs.toast);
    },
  };
</script>

<style scoped>

</style>
