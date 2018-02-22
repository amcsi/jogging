<template>
    <div>
        <spinner :loading="loading" />

        <div v-if="!loading">
            <table class="table b-table">
                <thead>
                    <th aria-colindex="1">Date</th>
                    <th aria-colindex="2">Distance</th>
                    <th aria-colindex="3">Time (minutes)</th>
                    <th aria-colindex="4">Average speed</th>
                    <th aria-colindex="5">&nbsp</th>
                </thead>
                <tbody>
                    <tr v-for="joggingTime in joggingTimes">
                        <td>{{ joggingTime.day }}</td>
                        <td>{{ formatFraction(joggingTime.distance / 1000) }}
                            km</td>
                        <td>{{ formatFraction(joggingTime.seconds / 60) }} minutes</td>
                        <td>{{ formatFraction((joggingTime.distance / 1000) / (joggingTime.seconds / 3600)) }} km/h</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
  export default {
    name: "jogging-list",
    props: ['currentUser'],
    data() {
      return {
        loading: true,
        joggingTimes: [],
        formatFraction: new Intl.NumberFormat([], {style: 'decimal', maximumFractionDigits: 2}).format,
      };
    },
    mounted() {
      axios.get('/api/jogging-times')
        .then(({ data }) => {
          this.joggingTimes = data.data;
          this.loading = false;
        })
        .catch(error => {
          this.$emit('handleGenericAjaxError', error, 'Failed to fetch jogging times list');
          this.loading = false;
        });
    },
  };
</script>

<style scoped>

</style>
