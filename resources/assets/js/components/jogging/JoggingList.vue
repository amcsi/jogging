<template>
    <div>
        <h1 v-if="forCurrentUser">Jogging list</h1>
        <h1 v-else>Jogging list ({{ targetUser.email }})</h1>

        <div>
            <jogging-time-entry :user="targetUser" />

            <b-btn @click="$modal.show('joggingTimeEntry')">Add new jogging entry</b-btn>
        </div>

        <div class="input-group">
            <div class="input-group-prepend">
                <button v-if="dateFrom || dateTo" class="btn btn-warning" @click="clearDates">Clear dates</button>
                <span v-else class="input-group-text">Filter by dates</span>
            </div>
            <datepicker style="flex: 1;" :classObject="['form-control']" placeholder="From" v-model="dateFrom" />
            <datepicker style="flex: 1;" :classObject="['form-control']" placeholder="To" v-model="dateTo" />
        </div>

        <div v-if="joggingTimes.length">
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
                        <th aria-colindex="1">Date</th>
                        <th aria-colindex="2">Distance</th>
                        <th aria-colindex="3">Time (minutes)</th>
                        <th aria-colindex="4">Average speed</th>
                        <th aria-colindex="5">&nbsp</th>
                    </tr>
                    </thead>
                    <tbody>

                    <template v-for="joggingTime in joggingTimes">
                        <transition name="fade">
                            <tr v-if="! joggingTime.deleted">
                                <td>
                                    <day :day="joggingTime.day" />
                                </td>
                                <td>{{ formatFraction(joggingTime.distance_m / 1000) }} km</td>
                                <td>{{ formatFraction(joggingTime.minutes) }} minutes</td>
                                <td>{{ formatFraction((joggingTime.distance_m / 1000) / (joggingTime.minutes / 60) || 0)
                                    }}
                                    km/h
                                </td>
                                <td>
                                    <i class="fa fa-pencil clickable"
                                        @click="$modal.show('joggingTimeEntry', {joggingTime})"></i>
                                    <i class="fa fa-trash clickable" @click="deleteJoggingTime(joggingTime)"></i>
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
        <div v-else>
            <p>There are no jogging time entries.</p>
        </div>
    </div>
</template>

<script>
  import JoggingTimeEntry from './JoggingTimeEntry';

  export default {
    name: "jogging-list",
    props: ['currentUser', 'user'],
    components: { JoggingTimeEntry: JoggingTimeEntry },
    data() {
      return {
        loading: true,
        joggingTimes: [],
        paginationData: null,
        page: 1,
        formatFraction: new Intl.NumberFormat([], { style: 'decimal', maximumFractionDigits: 2 }).format,
        dateFrom: '',
        dateTo: '',
      };
    },
    computed: {
      targetUser() {
        return this.user || this.currentUser;
      },
      forCurrentUser() {
        return !this.user;
      },
    },
    watch: {
      dateFrom() {
        // TODO: Probably would be better to separate jogging list as a child component under these filters.
        this.reloadList();
      },
      dateTo() {
        this.reloadList();
      },
    },
    methods: {
      async reloadList(page = 1) {
        this.page = page;
        try {
          this.loading = true;
          const { dateFrom, dateTo } = this;
          const params = { page };
          if (dateFrom) {
            params.dateFrom = dateFrom;
          }
          if (dateTo) {
            params.dateTo = dateTo;
          }
          const { data } = await axios.get(`/api/users/${this.targetUser.id}/jogging-times`, { params });
          this.joggingTimes = data.data.map(joggingTime => {
            // For reactivity.
            joggingTime.deleted = false;
            return joggingTime;
          });
          this.paginationData = data.pagination;
          this.page = this.paginationData.current_page;
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to fetch jogging times list');
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
          toast.displaySuccess('Successfully deleted jogging entry.');
        } catch (error) {
          try {
            this.$root.$emit('handleGenericAjaxError', error, 'Failed to delete jogging entry');
          } catch (e) {
          }
        }
      },
      clearDates() {
        this.dateFrom = '';
        this.dateTo = '';
      },
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

    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }

    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
    {
        opacity: 0;
    }
</style>
