/**
 * Spanish localization dictionary for form labels, placeholders, helper text, and buttons.
 */
const form = {
  labels: {
    user: {
      user: 'Usuario',
      email: 'Correo electrónico',
      password: 'Contraseña',
      birthday: 'Fecha de nacimiento',
      dataProcessingPolicy:
        'Soy mayor de edad y acepto las <a href="{0}" class="color-text-first" target="_blank"> política de privacidad de uso</a> de UNGOLMAS.',
    },
    contact: {
      fullname: 'nombre completo',
      business: 'empresa / marca',
      phonewhatsappnumber: 'whatsapp / teléfono',
      interesttype: 'tipo de interés',
      country: 'país / territorio',
      message: 'mensaje',
    },
  },
  placeHolders: {
    user: {
      user: 'Ejm: juanperez (solo letras y números)',
    },
    contact: {
      fullName: 'Tu nombre',
      business: 'Nombre de tu empresa',
      email: 'correo@empresa.com',
      phoneWhatsappNumber: '999 999 999',
      Message:
        'Cuéntanos sobre tu marca, qué tipo de vínculo buscas y cualquier detalle relevante...',
    },
  },
  text: {
    dataProcessingPolicy:
      'Al registrarte confirmas que eres mayor de edad y aceptas nuestra <a href="{0}" target="_blank">Política de Privacidad. </a>',
    select: 'Selecciona una opción',
    isOfLegalAge:
      'Solo esta permitido el registro de personas mayores de edad. Para mas informacion visita nuestra  <a href="{0}"> política de privacidad </a>',
  },
  buttons: {
    register: 'Crear cuenta gratis',
    contact: 'Enviar mensaje',
    auth: {
      register: 'Registrarse con {0}',
      login: 'Iniciar sesión con {0}',
    },
  },
} as const;

export type Form = typeof form;
export { form };
