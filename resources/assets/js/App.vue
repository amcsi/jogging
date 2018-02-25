<template>
    <div>
        <vue-toast ref="toast" />

        <b-container>
            <login-registration v-if="!userData" />

            <div v-if="userData">
                <div>
                    Welcome, <strong>{{ userData.email }}!</strong>
                    <button v-if="userData" class="btn btn-warning" @click="logout" :userData="userData">Log out</button>
                </div>

                <router-view :currentUser="userData" />
            </div>
        </b-container>
    </div>
</template>

<script>
  import axios from 'axios';
  import vueToast from 'vue-toast';
  import LoginRegistration from './components/LoginRegistration';
  import toastRegisterer from './toastRegisterer';

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

      // On creation: set the token to make sure the header is added to the axios defaults.
      this.setToken(localStorage.getItem('token') || '');
    },
    mounted() {
      toastRegisterer(this.$refs.toast);

      this.$root.$on('newTokenReceived', ({ token }) => {

        this.setToken(token);

        axios.get('/api/users/me').then(({ data }) => {
          this.setUserData(data.data);
          toast.displaySuccess('Login successful!');
        }).catch(error => {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to fetch user data');
        });
      }).$on('handleGenericAjaxError', (error, message = 'An error has occurred') => {
        try {
          if (error.response.data.message) {
            toast.displayError(`${message}: ${error.response.data.message}`);
            return;
          }
        } catch (e) {
        }
        toast.displayError(message);
      });
    },
  };
</script>

<style scoped>

</style>
