import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';
import type { StorybookConfig } from '@storybook/vue3-vite';

const rootRequire = createRequire(join(process.cwd(), 'package.json'));
const requireFromEssentials = createRequire(
  rootRequire.resolve('@storybook/addon-essentials/package.json')
);
const addonDocsDir = dirname(requireFromEssentials.resolve('@storybook/addon-docs/package.json'));
const requireFromDocs = createRequire(join(addonDocsDir, 'package.json'));
const blocksDir = dirname(requireFromDocs.resolve('@storybook/blocks/package.json'));

/**
 * Configuración principal de Storybook para Vue 3 + Vite.
 */
const config: StorybookConfig = {
  stories: [
    '../packages/**/resources/**/*.mdx',
    '../packages/**/resources/**/*.stories.@(js|jsx|mjs|ts|tsx)',
  ],
  addons: [
    { name: '@storybook/addon-essentials', options: { backgrounds: false } },
    '@storybook/addon-interactions',
    'storybook-dark-mode',
  ],
  framework: {
    name: '@storybook/vue3-vite',
    options: {},
  },
  viteFinal(viteConfig) {
    // NOTE: Excluir laravel-vite-plugin en Storybook para evitar que sobreescriba public/hot o build.outDir.
    viteConfig.plugins = (viteConfig.plugins ?? []).filter((plugin) => {
      if (!plugin || typeof plugin !== 'object' || !('name' in plugin)) {
        return true;
      }

      return !String(plugin.name).startsWith('laravel');
    });

    // NOTE: Resolver los builds ESM (.mjs) de @storybook/addon-docs/blocks y @storybook/blocks bajo pnpm,
    // preservando todos los alias previos de Storybook (externalGlobalsPlugin) y de vite.config.ts.
    const docsAliases = [
      {
        find: '@storybook/addon-docs/blocks',
        replacement: join(addonDocsDir, 'dist/blocks.mjs'),
      },
      {
        find: '@storybook/blocks',
        replacement: join(blocksDir, 'dist/index.mjs'),
      },
    ];

    viteConfig.resolve ??= {};
    const existingAlias = viteConfig.resolve.alias;

    if (Array.isArray(existingAlias)) {
      viteConfig.resolve.alias = [...existingAlias, ...docsAliases];
    } else if (existingAlias && typeof existingAlias === 'object') {
      viteConfig.resolve.alias = [
        ...Object.entries(existingAlias).map(([find, replacement]) => ({ find, replacement })),
        ...docsAliases,
      ];
    } else {
      viteConfig.resolve.alias = docsAliases;
    }

    return viteConfig;
  },
};

export default config;
