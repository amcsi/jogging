<template>
    <div>
        <b-alert variant="success"
            dismissible
            :show="showDismissibleAlert && successMessage"
            @dismissed="showDismissibleAlert=false"
        >
            {{ successMessage }}
        </b-alert>

        <b-alert variant="danger"
            dismissible
            :show="showDismissibleAlert && errorMessage"
            @dismissed="showDismissibleAlert=false"
        >
            {{ errorMessage }}
        </b-alert>
    </div>
</template>

<script>
  export default {
    name: "global-messages",
  };
</script>

<style scoped>

</style>
