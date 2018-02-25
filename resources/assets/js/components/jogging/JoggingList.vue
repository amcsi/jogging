<template>
    <div>
        <h1>Jogging list</h1>

        <div>
            <jogging-time-entry :currentUser="currentUser" />

            <b-btn @click="$modal.show('joggingTimeEntry')">Add new jogging entry</b-btn>
        </div>

        <pagination :paginationData="paginationData" :change="reloadList" v-if="paginationData" />

        <spinner :loading="loading" />

        <div v-if="!loading">

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
                <tr v-for="joggingTime in joggingTimes" v-if="! joggingTime.deleted">
                    <td>{{ joggingTime.day }}</td>
                    <td>{{ formatFraction(joggingTime.distance_m / 1000) }} km</td>
                    <td>{{ formatFraction(joggingTime.minutes) }} minutes</td>
                    <td>{{ formatFraction((joggingTime.distance_m / 1000) / (joggingTime.minutes / 60)) }} km/h</td>
                    <td>
                        <i class="fa fa-pencil clickable" @click="$modal.show('joggingTimeEntry', {joggingTime})"></i>
                        <i class="fa fa-trash clickable" @click="deleteJoggingTime(joggingTime)"></i>
                    </td>
                </tr>
                </tbody>
            </table>

            <pagination :paginationData="paginationData" :change="reloadList" />
        </div>
    </div>
</template>

<script>
  import JoggingTimeEntry from './JoggingTimeEntry';

  export default {
    name: "jogging-list",
    props: ['currentUser'],
    components: { JoggingTimeEntry: JoggingTimeEntry },
    data() {
      return {
        loading: true,
        joggingTimes: [],
        paginationData: null,
        formatFraction: new Intl.NumberFormat([], { style: 'decimal', maximumFractionDigits: 2 }).format,
      };
    },
    methods: {
      async reloadList(page = 1) {
        try {
          this.loading = true;
          const { data } = await axios.get('/api/jogging-times', { params: { page } });
          this.joggingTimes = data.data.map(joggingTime => {
            // For reactivity.
            joggingTime.deleted = false;
            return joggingTime;
          });
          this.paginationData = data.pagination;
        } catch (error) {
          this.$emit('handleGenericAjaxError', error, 'Failed to fetch jogging times list');
        }
        this.loading = false;
      },
      async deleteJoggingTime(joggingTime) {
        if (!confirm('Are you sure you want to delete this entry?')) {
          return;
        }

        try {
            await axios.delete('/api/jogging-times/' + joggingTime.id);
            joggingTime.deleted = true;
            console.info('jogging time deleted');
        } catch (error) {
          try {
            this.$root.$emit('handleGenericAjaxError', error, 'Failed to delete jogging entry');
          } catch (e) {};
        }
      }
    },
    mounted() {
      this.reloadList();
      // Reload the list when a new entry is added.
      this.$root.$on('joggingTimeChanged', this.reloadList.bind(this));
    },
  };
</script>

<style scoped>
    .clickable {
        cursor: pointer;
    }
</style>
