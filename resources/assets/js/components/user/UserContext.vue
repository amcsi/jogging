<template>
    <div>
        <spinner v-if="loading" />
        <router-view v-else :user="user" />
    </div>
</template>

<script>
  /**
   * This component is for wrapping views that can be applied to not only the auth user, but a given target user too.
   *
   * @class UserContext
   **/
  export default {
    name: 'user-context',
    data() {
      return {
        loading: true,
        user: null,
      };
    },
    async mounted() {
      this.loading = true;
      const { data } = await axios.get(`/api/users/${this.$route.params.userId}`);
      this.user = data.data;
      this.loading = false;
    },
  };
</script>

<style scoped>

</style>
