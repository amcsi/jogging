<template>
    <form @submit.prevent="login" class="card">
        <div class="card-header">
            <h2>Login form</h2>
        </div>

        <div class="card-body">
            <b-form-group horizontal
                :label-cols="4"
                label="Email address"
            >
                <b-form-input v-model.trim="email" required placeholder="E-mail address"></b-form-input>

                <form-field-errors :errors="errors.email" />
            </b-form-group>

            <b-form-group horizontal
                :label-cols="4"
                label="Password"
            >
                <b-form-input type="password" v-model.trim="password" required placeholder="Password"></b-form-input>

                <form-field-errors :errors="errors.password" />
            </b-form-group>

            <div v-if="! loading">
                <div v-if="!offerRegistration">
                    <b-btn variant="primary" type="submit">Login / Register</b-btn>
                </div>

                <div v-else>
                    <b-btn variant="primary" type="submit">Login</b-btn>

                    <b-form-group horizontal
                        :label-cols="4"
                        label="Password confirmation"
                    >
                        <b-form-input type="password"
                            v-model.trim="password2"
                            @keydown.native.prevent.enter="register"
                            placeholder="Password confirmation"></b-form-input>

                        <form-field-errors :errors="errors.password2" />
                    </b-form-group>

                    <b-btn variant="primary" type="submit" @click.prevent="register">Register</b-btn>
                </div>
            </div>
            <div v-else>
                <spinner />
            </div>
        </div>
    </form>
</template>

<script>
  export default {
    name: "login-registration",
    data() {
      return {
        email: '',
        password: '',
        password2: '',
        passwordConfirm: '',
        errors: {},
        loading: false,
        offerRegistration: false,
      };
    },
    methods: {
      async register() {
        this.loading = true;
        if (this.password !== this.password2) {
          this.errors = { password2: ['Passwords do not match.'] };
          return;
        }

        try {
          await axios.post('/api/users', { email: this.email, password: this.password });
          try {
            return this.login();
          } catch (e) {}
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'You were not able to register.', this);

          return;
        }
        this.loading = false;
      },
      async login() {
        this.loading = true;
        try {
          const { data } = await axios.post('/api/login', { password: this.password, email: this.email });
          const { access_token } = data;
          this.$root.$emit('tokenReceived', access_token);
          this.offerRegistration = false;
        } catch (error) {
          if (error.response.data.error === 'email_not_found') {
            // Offer the user to log in.
            this.offerRegistration = true;
          } else {
            this.$root.$emit('handleGenericAjaxError', error, 'Failed to log in', this);
          }
        }
        this.loading = false;
      },
    },
  };
</script>

<style scoped>

</style>
