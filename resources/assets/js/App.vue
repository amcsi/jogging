<template>
    <b-container @login.success="loginSuccess()">
        <global-messages
            :successMessage="messages.successMessage"
            :errorMessage="messages.errorMessage"
        />
        <login-registration />
    </b-container>
</template>

<script>
  import LoginRegistration from './components/LoginRegistration';
  import GlobalMessages from './components/GlobalMessages';

  export default {
    name: "app",
    components: { LoginRegistration, GlobalMessages },
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
  };
</script>

<style scoped>

</style>
