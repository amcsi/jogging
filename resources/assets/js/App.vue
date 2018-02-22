<template>
    <div>
        <vue-toast ref="toast" />

        <b-container>
            <login-registration v-if="!userData" />

            <div v-if="userData">
                Welcome, <strong>{{ userData.email }}!</strong>
                <button v-if="userData" class="btn btn-warning" @click="logout" :userData="userData">Log out</button>
            </div>
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
        userData: JSON.parse(localStorage.getItem('userData')) || '',
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

        if (token) {
          // Make sure all requests include the token from now on.
          axios.defaults.headers.common.Authorization = `Bearer ${token}`;
        } else {
          // Remove auth header from defaults.
          delete axios.defaults.headers.common.Authorization;
          // Clear user data.
          this.setUserData(null);
        }
      };
      this.setUserData = userData => {
        this.userData = userData;
        localStorage.setItem('userData', userData ? JSON.stringify(userData) : null);
      };
    },
    mounted() {
      toastRegisterer(this.$refs.toast);

      this.$root.$on('newTokenReceived', ({ token }) => {

        this.setToken(token);

        axios.get('/api/users/me').then(({ data }) => {
          this.setUserData(data.data);
          toast.displaySuccess('Login successful!');
        }).catch(error => {
          try {
            if (error.response.data.message) {
              toast.displayError(`Login failure: ${error.response.data.message}`);
              return;
            }
          } catch (e) {}
          toast.displayError('Login failure');
        });
      });
    },
  };
</script>

<style scoped>

</style>
