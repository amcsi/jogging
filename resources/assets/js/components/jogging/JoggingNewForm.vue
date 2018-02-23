<template>
    <div>
        <modal name="newJoggingTime" height="auto" scrollable>
            <form @submit.prevent="save" class="newJoggingTime" @keyup="clearError">
                <h2>Add new jogging entry</h2>

                <b-form-group horizontal
                    :label-cols="4"
                    label="Date"
                >
                    <b-form-input name="day" v-model.trim="day"></b-form-input>

                    <form-field-errors :errors="errors.day" />
                </b-form-group>

                <b-form-group horizontal
                    :label-cols="4"
                    label="Distance (meters)"
                >
                    <b-form-input name="distance_m" v-model.trim="distance_m" placeholder="500"></b-form-input>

                    <form-field-errors :errors="errors.distance_m" />
                </b-form-group>


                <b-form-group horizontal
                    :label-cols="4"
                    label="Minutes spent running"
                >
                    <b-form-input name="minutes" v-model.trim="minutes"></b-form-input>

                    <form-field-errors :errors="errors.minutes" />
                </b-form-group>

                <div v-if="! loading">
                    <b-btn variant="primary" type="submit">Save</b-btn>
                    <b-btn @click="$modal.hide('newJoggingTime')">Cancel</b-btn>
                </div>
                <div v-else>
                    <spinner />
                </div>
            </form>
        </modal>

        <b-btn @click="$modal.show('newJoggingTime')">Add new jogging entry</b-btn>
    </div>
</template>

<script>
  /** @class JoggingNewForm */
  export default {
    name: 'jogging-new-form',
    props: ['currentUser'],
    data() {
      const now = new Date();
      const todayUTC = new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));

      return {
        loading: false,
        errors: {},
        day: todayUTC.toISOString().slice(0, 10),
        minutes: '',
        distance_m: '',
      };
    },
    methods: {
      async save() {
        this.loading = true;
        this.errors = {};
        try {
          await axios.post('/api/jogging-times', {
            day: this.day,
            minutes: this.minutes,
            distance_m: this.distance_m,
          });
          toast.displaySuccess('Successfully added new jogging entry');
          this.$modal.hide('newJoggingTime');
          this.$root.$emit('newJoggingTimeAdded');
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to add new jogging entry');
          try {
            this.errors = error.response.data.errors;
          } catch (e) {
          }
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
    .newJoggingTime {
        margin: 10px;
    }
</style>
