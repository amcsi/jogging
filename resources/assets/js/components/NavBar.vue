<template>
    <b-navbar toggleable="md" type="dark" variant="info">

        <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>

        <b-navbar-brand href="#/">Jogging List</b-navbar-brand>

        <b-collapse is-nav id="nav_collapse">

            <b-navbar-nav>
                <b-nav-item v-for="link in links" :key="link.uri + link.label" :href="'#/' + link.uri">{{ link.label
                    }}
                </b-nav-item>
            </b-navbar-nav>

            <b-navbar-nav class="ml-auto">
                <b-nav-item target="_blank" href="https://github.com/amcsi/jogging"><i class="fa fa-github"></i> Github</b-nav-item>

                <b-nav-item-dropdown v-if="userData">
                    <template slot="button-content">
                        <em>{{ userData.email }}</em>
                    </template>
                    <b-dropdown-item @click="$root.$emit('doLogout')">Signout</b-dropdown-item>
                </b-nav-item-dropdown>
            </b-navbar-nav>

        </b-collapse>
    </b-navbar>
</template>

<script>
  import { isManager } from '../constants/userRole';

  /** @class NavBar */
  export default {
    name: 'nav-bar',
    props: ['userData'],
    computed: {
      links() {
        const links = [];
        if (this.userData) {
          links.push({ uri: 'jogging-weekly', label: 'Weekly jogging' });
          const role = this.userData.role;
          if (isManager(role)) {
            links.push({ uri: 'users', label: 'User list' });
          }
        }
        return links;
      }
    },
  };
</script>

<style scoped>

</style>
