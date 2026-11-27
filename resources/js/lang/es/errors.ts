/**
 * Spanish localization dictionary for validation and authentication error messages.
 */
const errors = {
  validation: {
    requiredDate: 'Selecciona hora y fecha.',
    requiredSelect: 'Selecciona una opción.',
    requiredInput: 'Campo vacío, ingresa un valor.',
    requiredBirthday: 'La fecha de nacimiento es requerida.',
    requiredInputEmail: 'Ingrese correo electrónico.',
    requiredCheckTyC: 'Aceptar términos de uso.',
    requiredCheckPdU: 'Aceptar las políticas de privacidad de uso.',
    requiredInputNumber: 'Ingrese números.',
    requiredInputPhones: 'Ingrese número celulares.',
    requiredInputMin: 'Ingresar mínimo ${1} digitos.',
    requiredInputMax: 'Ingresar máximo ${0} digitos.',
  },
  auth: {
    email: 'Ingrese correo electrónico.',
    password: 'Ingrese contraseña.',
  },
} as const;

export type Errors = typeof errors;
export { errors };
