<template>
    <b-pagination
        size="md"
        :total-rows="paginationData.total"
        v-model="paginationData.current_page"
        :per-page="paginationData.per_page"
        @change="change"
    ></b-pagination>
</template>

<script>
  /** @class Pagination */
  export default {
    name: 'pagination',
    props: ['paginationData', 'change'],
  };
</script>
