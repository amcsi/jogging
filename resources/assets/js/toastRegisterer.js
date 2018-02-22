/**
 * Registers a global toast handling API
 *
 * @param toast
 */
function toastRegisterer(toast) {
  window.toast = {
    displaySuccess(message) {
      toast.showToast(message, {
        theme: 'success',
      });
    },
    displayError(message) {
      toast.showToast(message, {
        theme: 'error',
      });
    },
  };
}

export default toastRegisterer;
