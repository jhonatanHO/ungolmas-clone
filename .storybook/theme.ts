import { themes, type ThemeVars } from 'storybook/internal/theming';

const branding = {
  brandTitle: 'UNGOLMAS',
  brandUrl: './',
  brandImage: 'https://ungolmas.com/assets/img/ugm-logo-pc.svg',
};

export const storybookThemes = {
  light: { ...themes.light, ...branding, appContentBg: '#ffffff' },
  dark: { ...themes.dark, ...branding, appContentBg: '#09090b' },
} satisfies Record<'light' | 'dark', ThemeVars>;
