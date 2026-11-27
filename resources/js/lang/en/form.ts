/**
 * English localization dictionary for form labels, placeholders, helper text, and buttons.
 */
const form = {
  labels: {
    user: {
      user: 'Username',
      email: 'Email address',
      password: 'Password',
      birthday: 'Date of birth',
      dataProcessingPolicy:
        'I am of legal age and accept the <a href="{0}" class="color-text-first" target="_blank"> privacy policy of use</a> of UNGOLMAS.',
    },
    contact: {
      fullname: 'full name',
      business: 'company / brand',
      phonewhatsappnumber: 'whatsapp / phone',
      interesttype: 'type of interest',
      country: 'country / territory',
      message: 'message',
    },
  },
  placeHolders: {
    user: {
      user: 'E.g.: juanperez (letters and numbers only)',
    },
    contact: {
      fullName: 'Your name',
      business: 'Your company name',
      email: 'email@company.com',
      phoneWhatsappNumber: '999 999 999',
      Message:
        'Tell us about your brand, what kind of connection you are looking for, and any relevant details...',
    },
  },
  text: {
    dataProcessingPolicy:
      'By registering you confirm that you are of legal age and accept our <a href="{0}" target="_blank">Privacy Policy. </a>',
    select: 'Select an option',
    isOfLegalAge:
      'Only registration of people of legal age is allowed. For more information visit our <a href="{0}"> privacy policy </a>',
  },
  buttons: {
    register: 'Create free account',
    contact: 'Send message',
    auth: {
      register: 'Register with {0}',
      login: 'Log in with {0}',
    },
  },
} as const;

export type Form = typeof form;
export { form };
