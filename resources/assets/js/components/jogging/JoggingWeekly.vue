<template>
    <div>
        <h1>Weekly jogging</h1>

        <spinner :loading="loading" />

        <div v-if="!loading">

            <table class="table b-table">
                <thead>
                <tr>
                    <th aria-colindex="1">Dates</th>
                    <th aria-colindex="2">Distance</th>
                    <th aria-colindex="3">Time (minutes)</th>
                    <th aria-colindex="4">Average speed</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="joggingTime in joggingTimes" v-if="! joggingTime.deleted">
                    <td>{{ joggingTime.first_day }} - {{ joggingTime.last_day }}</td>
                    <td>{{ formatFraction(joggingTime.distance_m / 1000) }} km</td>
                    <td>{{ formatFraction(joggingTime.minutes) }} minutes</td>
                    <td>{{ formatFraction((joggingTime.distance_m / 1000) / (joggingTime.minutes / 60)) }} km/h</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
  /** @class JoggingWeekly */
  export default {
    name: 'jogging-weekly',
    props: ['currentUser'],
    data() {
      return {
        loading: true,
        joggingTimes: [],
        formatFraction: new Intl.NumberFormat([], { style: 'decimal', maximumFractionDigits: 2 }).format,
      };
    },
    methods: {
      async reloadList() {
        try {
          this.loading = true;
          const { data } = await axios.get(`/api/users/${this.currentUser.id}/jogging-times/by-week`);
          this.joggingTimes = data.data.map(joggingTime => {
            // For reactivity.
            joggingTime.deleted = false;
            return joggingTime;
          });
        } catch (error) {
          this.$emit('handleGenericAjaxError', error, 'Failed to fetch jogging times list');
        }
        this.loading = false;
      },
    },
    mounted() {
      this.reloadList();
    },
  };
</script>

<style scoped>

</style>
