<template>
    <div>
        <spinner :loading="loading" />

        <div v-if="!loading">
            <jogging-new-form :currentUser="currentUser" />

            <table class="table b-table">
                <thead>
                <tr>
                    <th aria-colindex="1">Date</th>
                    <th aria-colindex="2">Distance</th>
                    <th aria-colindex="3">Time (minutes)</th>
                    <th aria-colindex="4">Average speed</th>
                    <th aria-colindex="5">&nbsp</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="joggingTime in joggingTimes">
                    <td>{{ joggingTime.day }}</td>
                    <td>{{ formatFraction(joggingTime.distance_m / 1000) }} km</td>
                    <td>{{ formatFraction(joggingTime.minutes) }} minutes</td>
                    <td>{{ formatFraction((joggingTime.distance_m / 1000) / (joggingTime.minutes / 60)) }} km/h</td>
                    <td>&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
  import JoggingNewForm from './JoggingNewForm';

  export default {
    name: "jogging-list",
    props: ['currentUser'],
    components: { JoggingNewForm },
    data() {
      return {
        loading: true,
        joggingTimes: [],
        formatFraction: new Intl.NumberFormat([], { style: 'decimal', maximumFractionDigits: 2 }).format,
      };
    },
    async mounted() {
      try {
        const { data } = await axios.get('/api/jogging-times');
        this.joggingTimes = data.data;
        this.loading = false;
      } catch (error) {
        this.$emit('handleGenericAjaxError', error, 'Failed to fetch jogging times list');
        this.loading = false;
      }
    },
  };
</script>

<style scoped>

</style>
