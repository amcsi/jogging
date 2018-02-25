<template>
    <div>
        <h1>User list</h1>

        <pagination :paginationData="paginationData" :change="reloadList" v-if="paginationData" />

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
                <tr v-for="user in userList" v-if="! user.deleted">
                    <td>{{ user.id }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.role }}</td>
                    <td>
                        <i class="fa fa-pencil clickable" @click="$modal.show('editUser', {user})"></i>
                        <i class="fa fa-trash clickable" @click="deleteUser(user)"></i>
                    </td>
                </tr>
                </tbody>
            </table>

            <pagination :paginationData="paginationData" :change="reloadList" />
        </div>
    </div>
</template>

<script>
  /** @class UserList */
  export default {
    name: 'user-list',
    props: ['userData'],
    data() {
      return {
        userList: [],
        loading: true,
        paginationData: null,
      };
    },
    methods: {
      async reloadList(page = 1) {
        console.info(page);
        this.loading = true;
        try {
          const responseData = (await axios.get('/api/users', { params: { page } })).data;
          this.userList = responseData.data;
          this.paginationData = responseData.pagination;
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to load user list');
        }
        this.loading = false;
      }
    },
    mounted() {
      this.reloadList();
    },
  };
</script>

<style scoped>

</style>
