<template>
    <div>
        <vue-toast ref="toast" />

        <b-container>
            <login-registration :token="token" />

            <button v-if="token" class="btn btn-warning" @click="logout">Log out</button>
        </b-container>
    </div>
</template>

<script>
  import LoginRegistration from './components/LoginRegistration';
  import vueToast from 'vue-toast';
  import toastRegisterer from './toastRegisterer';
  import axios from 'axios';

  export default {
    name: "app",
    components: { LoginRegistration, vueToast },
    data() {
      return {
        token: localStorage.getItem('token') || '',
      };
    },
    methods: {
      logout() {
        this.setToken('');
      }
    },
    created() {
      this.setToken = token => {
        this.token = token;
        localStorage.setItem('token', token);
      };
    },
    mounted() {
      toastRegisterer(this.$refs.toast);

      this.$root.$on('loginSuccess', ({ token }) => {
        toast.displaySuccess('Login successful!');

        this.setToken(token);
        // Make sure all requests include the token from now on.
        axios.defaults.headers.common.Authorization = `Bearer ${token}`;
      });
    },
  };
</script>

<style scoped>

</style>
