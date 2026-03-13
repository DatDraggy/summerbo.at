/// <reference types="svelte" />

declare module '*.svelte' {
  import type { ComponentType } from 'svelte';
  const component: ComponentType;
  export default component;
}

declare module '*.js';
declare module 'tinro/cmp';

declare const __PARTY_DATE__: string;
declare const __PARTY_YEAR__: string;
declare const __PARTY_SLOGAN__: string;
declare const __PARTY_ISO_DATE__: string;
declare const __REG_ISO_DATE__: string;
declare const __CRUISE_DATE__: string;
