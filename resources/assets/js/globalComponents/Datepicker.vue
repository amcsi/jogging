<template>
    <v-date-picker
        v-if="show"
        :name="name"
        mode='single'
        :value="date"
        @input="childInput"
        :input-props="{ class: classObject, placeholder: placeholder || 'Enter date' }"
    >
    </v-date-picker>
</template>

<script>
  /**
   * Datepicker wrapper to set an implementation boundary.
   *
   * @class DatePicker
   **/
  export default {
    name: 'datepicker',
    props: ['value', 'input', 'name', 'classObject', 'placeholder'],
    data() {
      return {
        show: true,
      };
    },
    computed: {
      date() {
        const valueDate = new Date(this.value);
        return new Date(valueDate.getUTCFullYear(), valueDate.getUTCMonth(), valueDate.getUTCDate());
      },
    },
    methods: {
      childInput(value) {
        if (value instanceof Date) {
          const month = ('0' + (value.getMonth() + 1)).slice(-2);
          const day = ('0' + (value.getDate())).slice(-2);
          value = `${value.getFullYear()}-${month}-${day}`;
          this.$emit('input', value);
        } else {
          // Value didn't change, but we need to force an update, otherwise the v-calendar doesn't close.
          this.rerender();
        }
      },
    },
    mounted() {
      /**
       * https://github.com/vuejs/Discussion/issues/356#issuecomment-312529480
       */
      this.rerender = () => {
        this.show = false;
        this.$nextTick(() => {
          this.show = true;
        });
      };
    },
  };
</script>

<style scoped>

</style>
