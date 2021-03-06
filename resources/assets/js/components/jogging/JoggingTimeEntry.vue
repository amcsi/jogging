<template>
    <modal name="joggingTimeEntry" height="auto" scrollable @before-open="beforeOpen">
        <form @submit.prevent="save" class="joggingTimeEntry" @keyup="clearError">

            <h2>
                {{ id ? 'Edit jogging entry' : 'Add new jogging entry' }}
            </h2>

            <b-form-group horizontal
                :label-cols="4"
                label="Date"
            >
                <datepicker v-if="! id" name="day" v-model.trim="day"></datepicker>
                <div
                    v-if="id"
                    class="align-day"
                ><day :day="day" /></div>

                <form-field-errors :errors="errors.day" />
            </b-form-group>

            <b-form-group horizontal
                :label-cols="4"
                label="Distance (meters)"
            >
                <div class="container-with-input-and-right-label">
                    <b-form-input class="number-input-width" name="distance_m" v-model.trim="distance_m"></b-form-input>

                    <div class="right-label">meters</div>
                </div>

                <form-field-errors :errors="errors.distance_m" />
            </b-form-group>


            <b-form-group horizontal
                :label-cols="4"
                label="Minutes spent running"
            >
                <div class="container-with-input-and-right-label">
                    <b-form-input class="number-input-width" name="minutes" v-model.trim="minutes"></b-form-input>

                    <div class="right-label">minutes</div>
                </div>

                <form-field-errors :errors="errors.minutes" />
            </b-form-group>

            <div v-if="! loading">
                <b-btn variant="primary" type="submit">Save</b-btn>
                <b-btn @click="$modal.hide('joggingTimeEntry')">Cancel</b-btn>
            </div>
            <div v-else>
                <spinner />
            </div>
        </form>
    </modal>
</template>

<script>
  function getInititialState() {
    const now = new Date();
    const todayUTC = new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));

    return {
      loading: false,
      errors: {},
      id: '',
      day: todayUTC.toISOString().slice(0, 10),
      minutes: '',
      distance_m: '',
      joggingTime: null,
    };
  }
  /** @class JoggingNewForm */
  export default {
    name: 'jogging-new-form',
    props: ['user'],
    data: getInititialState,
    methods: {
      beforeOpen(event) {
        // Reset this modal to its initial state (for creating a new entry), then change it if
        // This is for editing an existing entry.
        let joggingTime = (event.params || {}).joggingTime || {};
        Object.assign(this, getInititialState(), joggingTime);
      },
      save() {
        if (this.id) {
          this.saveUpdate();
        } else {
          this.saveCreate();
        }
      },
      async saveCreate() {
        this.loading = true;
        this.errors = {};
        try {
          await axios.post(`/api/users/${this.user.id}/jogging-times`, {
            day: this.day,
            minutes: this.minutes,
            distance_m: this.distance_m,
          });
          toast.displaySuccess('Successfully added new jogging entry');
          this.$modal.hide('joggingTimeEntry');
          this.$root.$emit('joggingTimeChanged');
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to add new jogging entry', this);
        }
        this.loading = false;
      },
      async saveUpdate() {
        this.loading = true;
        this.errors = {};
        try {
          await axios.put('/api/jogging-times/' + this.id, {
            minutes: this.minutes,
            distance_m: this.distance_m,
          });
          toast.displaySuccess('Successfully updated jogging entry');
          this.$modal.hide('joggingTimeEntry');
          this.$root.$emit('joggingTimeChanged');
        } catch (error) {
          this.$root.$emit('handleGenericAjaxError', error, 'Failed to update jogging entry', this);
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

<style>
    .joggingTimeEntry {
        margin: 0 10px;
        padding: 10px 0;
    }

    .container-with-input-and-right-label {
        display: flex;
        align-items: center;
    }

    .number-input-width {
        width: 100px;
    }

    .right-label {
        margin-left: 10px;
    }

    .align-day {
        height: 100%;
        display: flex;
        align-items: center;
    }

    .v--modal-overlay .v--modal-box {
        /* This is to allow the datepicker to pop out */
        overflow: visible;
    }
</style>
