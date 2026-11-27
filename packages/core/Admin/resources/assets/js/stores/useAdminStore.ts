/**
 * Global Pinia store for the CMSNET Admin module state.
 */

import { ref, computed } from 'vue';
import { defineStore } from 'pinia';

export const useAdminStore = defineStore('admin', () => {
  /* STATE */
  const isSubmitting = ref<boolean>(false);

  /* COMPUTED */
  const isBusy = computed<boolean>(() => isSubmitting.value);

  /* UI HANDLERS */

  /**
   * Updates the global submission loading state for the admin module.
   *
   * @param {boolean} status - Whether an operation is currently submitting.
   * @returns {void}
   */
  const setSubmitting = (status: boolean): void => {
    isSubmitting.value = status;
  };

  return {
    isSubmitting,
    isBusy,
    setSubmitting,
  };
});
