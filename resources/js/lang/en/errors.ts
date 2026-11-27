/**
 * English localization dictionary for validation and authentication error messages.
 */
const errors = {
  validation: {
    requiredDate: 'Select time and date.',
    requiredSelect: 'Select an option.',
    requiredInput: 'Empty field, enter a value.',
    requiredBirthday: 'Date of birth is required.',
    requiredInputEmail: 'Enter email address.',
    requiredCheckTyC: 'Accept terms of use.',
    requiredCheckPdU: 'Accept privacy policies of use.',
    requiredInputNumber: 'Enter numbers.',
    requiredInputPhones: 'Enter mobile numbers.',
    requiredInputMin: 'Enter at least ${1} digits.',
    requiredInputMax: 'Enter at most ${0} digits.',
  },
  auth: {
    email: 'Enter email address.',
    password: 'Enter password.',
  },
} as const;

export type Errors = typeof errors;
export { errors };
