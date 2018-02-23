<template>
    <div v-if="errors">
        <div v-for="error in errors" class="text-danger">{{ error }}</div>
    </div>
</template>

<script>
  /** @class FormFieldErrors */
  export default {
    name: 'form-field-errors',
    props: ['errors'],
  };
</script>
