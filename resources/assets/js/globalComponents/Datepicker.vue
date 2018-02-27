<template>
    <v-date-picker
        :name="name"
        mode='single'
        :value="date"
        @input="childInput"
        :class="className"
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
    props: ['value', 'input', 'name', 'class'],
    computed: {
      date() {
        const valueDate = new Date(this.value);
        return new Date(valueDate.getUTCFullYear(), valueDate.getUTCMonth(), valueDate.getUTCDate());
      },
      className() {
        return this['class'];
      },
    },
    methods: {
      childInput(value) {
        if (value instanceof Date) {
          const month = ('0' + (value.getMonth() + 1)).slice(-2);
          const day = ('0' + (value.getDate())).slice(-2);
          value = `${value.getFullYear()}-${month}-${day}`;
        }
        this.$emit('input', value);
      },
    },
  };
</script>

<style scoped>

</style>
