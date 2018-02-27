<template>
    <modal name="userEdit" height="auto" scrollable @before-open="beforeOpen">
        <form @submit.prevent="save" class="userEditForm" @keyup="clearError" style="margin: 10px;">

            <h2>Edit user</h2>

            <b-form-group horizontal
                :label-cols="4"
                label="E-mail"
            >
                <b-form-input name="email" v-model.trim="email"></b-form-input>

                <form-field-errors :errors="errors.email" />
            </b-form-group>

            <b-form-group horizontal
                :label-cols="4"
                label="Password"
            >
                <b-form-input name="password"
                    type="password"
                    v-model.trim="password"
                    autocomplete="false"></b-form-input>

                <form-field-errors :errors="errors.password" />
            </b-form-group>

            <b-form-group
                v-if="currentUser.role === ROLE_ADMIN && currentUser.id !== id"
                horizontal
                :label-cols="4"
                label="Role"
            >
                <b-form-select name="role" v-model.trim="role" :options="roleSelectOptions" />

                <form-field-errors :errors="errors.role" />
            </b-form-group>

            <div v-if="! loading">
                <b-btn variant="primary" type="submit">Save</b-btn>
                <b-btn @click="$modal.hide('userEdit')">Cancel</b-btn>
            </div>
            <div v-else>
                <spinner />
            </div>
        </form>
    </modal>
</template>

<script>
  import { ADMIN, selectOptions } from '../../constants/userRole';

  /** @class UserEdit */
  export default {
    name: 'user-edit',
    data() {
      return {
        roleSelectOptions: selectOptions,
        loading: false,
        errors: {},
        id: '',
        email: '',
        password: '',
        role: '',
        ROLE_ADMIN: ADMIN,
        currentUser: {},
      };
    },
    methods: {
      beforeOpen(event) {
        // Reset this modal to its initial state (for creating a new entry), then change it if
        // This is for editing an existing entry.
        const { id, email, role } = event.params.user;
        const { currentUser } = event.params;
        Object.assign(this.$data, this.$options.data.apply(this), { id, email, role, currentUser });
      },
      async save() {
        this.loading = true;
        this.errors = {};
        try {
          const putData = {
            email: this.email,
            role: this.role,
          };
          if (this.password) {
            // Only include password if something was entered into that field.
            putData.password = this.password;
          }
          await axios.put('/api/users/' + this.id, putData);
          toast.displaySuccess('Successfully updated user');
          this.$modal.hide('userEdit');
          this.$root.$emit('userChanged');
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to update user', this);
        }
        this.loading = false;
      },
      clearError($event) {
        if ($event.target.name) {
          Vue.delete(this.errors, $event.target.name);
        }
      },
    },
  };
</script>

<style scoped>
    .userEditForm {
        margin: 0 10px;
        padding: 10px 0;
    }
</style>
