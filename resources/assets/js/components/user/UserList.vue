<template>
    <div>
        <h1>User list</h1>

        <userEdit />

        <b-pagination
            size="md"
            :total-rows="paginationData.total"
            :per-page="paginationData.per_page"
            @change="reloadList"
            v-model="page"
            v-if="paginationData"
        ></b-pagination>

        <spinner :loading="loading" />

        <div v-if="!loading">

            <table class="table b-table">
                <thead>
                <tr>
                    <th aria-colindex="1">ID</th>
                    <th aria-colindex="2">Email</th>
                    <th aria-colindex="3">Role</th>
                    <th aria-colindex="5">&nbsp</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="user in userList">
                    <transition name="fade">
                        <tr v-if="! user.deleted">
                            <td>{{ user.id }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.role }}</td>
                            <td>
                                <router-link v-if="currentUser.role === ROLE_ADMIN"
                                    :to="`users/${user.id}/jogging-times`"
                                    title="View jogging times">🏃
                                </router-link>
                                <i class="fa fa-pencil clickable"
                                    @click="$modal.show('userEdit', { user, currentUser })"></i>
                                <i class="fa fa-trash clickable" @click="deleteUser(user)"></i>
                            </td>
                        </tr>
                    </transition>
                </template>
                </tbody>
            </table>

            <b-pagination
                size="md"
                :total-rows="paginationData.total"
                :per-page="paginationData.per_page"
                @change="reloadList"
                v-model="page"
                v-if="paginationData"
            ></b-pagination>
        </div>
    </div>
</template>

<script>
  import { ADMIN } from '../../constants/userRole';
  import UserEdit from './UserEdit';

  /** @class UserList */
  export default {
    name: 'user-list',
    components: { UserEdit },
    props: ['currentUser'],
    data() {
      return {
        ROLE_ADMIN: ADMIN,
        userList: [],
        loading: true,
        paginationData: null,
        page: 1,
      };
    },
    methods: {
      async reloadList(page = 1) {
        this.page = page;
        this.loading = true;
        try {
          const responseData = (await axios.get('/api/users', { params: { page } })).data;
          this.userList = responseData.data.map(item => {
            // For reactivity.
            item.deleted = false;
            return item;
          });
          this.paginationData = responseData.pagination;
          this.page = page;
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to load user list');
        }
        this.loading = false;
      },
      async deleteUser(user) {
        if (!confirm(`Are you sure you want to delete user with an email of ${user.email}?`)) {
          return;
        }

        try {
          await axios.delete('/api/users/' + user.id);
          user.deleted = true;
          toast.displaySuccess('Successfully deleted the user.');
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to delete user');
        }
      },
    },
    mounted() {
      this.reloadList(this.page);
      // Reload the list when a new entry is added.
      this.$root.$on('userChanged', this.reloadList.bind(this.page));
    },
  };
</script>

<style scoped>
    .clickable {
        cursor: pointer;
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }

    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
    {
        opacity: 0;
    }
</style>
