<template>
    <div>
        <vue-toast ref="toast" />

        <spinner v-if="loading" />

        <div v-else>
            <nav-bar :userData="userData" />

            <b-container>
                <login-registration v-if="!userData" />

                <div v-if="userData">
                    <router-view :currentUser="userData" />
                </div>
            </b-container>
        </div>
    </div>
</template>

<script>
  import axios from 'axios';
  import vueToast from 'vue-toast';
  import LoginRegistration from './components/LoginRegistration';
  import NavBar from './components/NavBar';
  import toastRegisterer from './toastRegisterer';

  export default {
    name: 'app',
    components: { LoginRegistration, vueToast, NavBar },
    data() {
      return {
        token: localStorage.getItem('token') || '',
        userData: JSON.parse(localStorage.getItem('userData')) || '',
        loading: false,
      };
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

      this.$root.$on('doLogin', async ({ email, password, vm }) => {
        this.loading = true;
        try {
          const { data } = await axios.post('/api/login', {
            password,
            username: email,
          });
          const { access_token } = data;
          this.setToken(access_token);
          const { data: userData } = await axios.get('/api/users/me');
          this.setUserData(userData.data);
          toast.displaySuccess('Login successful!');
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to log in', vm);
        }
        this.loading = false;
      }).$on('handleGenericAjaxError', (error, message = 'An error has occurred', vm = null) => {
        try {
          if (vm) {
            // Set validation errors on the component the event was fired from.
            try {
              vm.errors = error.response.data.errors;
            } catch (e) {
            }
          }

          if (error.response.data.message) {
            toast.displayError(`${message}: ${error.response.data.message}`);
            return;
          }
        } catch (e) {
        }
        toast.displayError(message);
      }).$on('doLogout', () => {
        this.setToken('');
      });
    },
  };
</script>

<style scoped>

</style>
