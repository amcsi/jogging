<template>
    <span>{{ (new Date(day)).toLocaleDateString([], {timeZone: 'UTC'}) }}</span>
</template>

<script>
  /**
   * Simple component for formatting the "day"
   *
   * @class Date
   **/
  export default {
    name: 'day',
    props: ['day'],
  };
</script>

<style scoped>

</style>
