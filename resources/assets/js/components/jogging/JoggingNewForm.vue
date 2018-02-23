<template>
    <div>
        <modal name="newJoggingTime">
            <form @submit.prevent="save" class="newJoggingTime">
                <h2>Add new jogging entry</h2>

                <b-form-group horizontal
                    :label-cols="4"
                    label="Date"
                >
                    <b-form-input v-model.trim="day"></b-form-input>
                </b-form-group>

                <b-form-group horizontal
                    :label-cols="4"
                    label="Distance (meters)"
                >
                    <b-form-input v-model.trim="distance_m" placeholder="500"></b-form-input>
                </b-form-group>


                <b-form-group horizontal
                    :label-cols="4"
                    label="Minutes spent running"
                >
                    <b-form-input v-model.trim="minutes"></b-form-input>
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
    props: 'currentUser',
    data() {
      return {
        loading: false,
      };
    },
    methods: {
      save() {
        this.loading = true;
      }
    },
  };
</script>

<style scoped>
    .newJoggingTime {
        margin: 10px;
    }
</style>
