import svelte from 'rollup-plugin-svelte';
import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import livereload from 'rollup-plugin-livereload';
import typescript from '@rollup/plugin-typescript';
import css from 'rollup-plugin-css-only';
import replace from '@rollup/plugin-replace';
import { spawn } from 'child_process';
import { readFileSync, writeFileSync } from 'fs';

const production = !process.env.ROLLUP_WATCH;

const partyIsoDate = '2026-08-18';
const regIsoDate = '2026-02-15';
const dateObj = new Date(partyIsoDate);
const day = dateObj.getDate();
const month = dateObj.toLocaleString('en-US', { month: 'long' });
const year = dateObj.getFullYear();

const getOrdinal = (n) => {
	const s = ['th', 'st', 'nd', 'rd'];
	const v = n % 100;
	return n + (s[(v - 20) % 10] || s[v] || s[0]);
};

const partyInfo = {
	__PARTY_DATE__: `${getOrdinal(day)} ${month}`,
	__PARTY_YEAR__: year.toString(),
	__PARTY_SLOGAN__: 'Pawchella',
	__PARTY_ISO_DATE__: partyIsoDate,
	__REG_ISO_DATE__: regIsoDate
};

function serve() {
	let server;

	function toExit() {
		if (server) server.kill(0);
	}

	return {
		writeBundle() {
			if (server) return;
			server = spawn('npm', ['run', 'start', '--', '--dev'], {
				stdio: ['ignore', 'inherit', 'inherit'],
				shell: true
			});

			process.on('SIGTERM', toExit);
			process.on('exit', toExit);
		}
	};
}

export default {
	input: 'src/main.ts',
	output: {
		sourcemap: true,
		format: 'iife',
		name: 'app',
		file: 'public/build/bundle.js'
	},
	plugins: [
		replace({
			values: partyInfo,
			preventAssignment: true,
			delimiters: ['', '']
		}),
		{
			name: 'html-template',
			buildStart() {
				this.addWatchFile('src/index.html');
				this.addWatchFile('src/manifest.json');
			},
			writeBundle() {
				const templates = ['src/index.html', 'src/manifest.json'];
				for (const template of templates) {
					let content = readFileSync(template, 'utf8');
					for (const [key, value] of Object.entries(partyInfo)) {
						content = content.replace(new RegExp(key, 'g'), value);
					}
					writeFileSync(template.replace('src/', 'public/'), content);
				}
			}
		},
		svelte({
			compilerOptions: {
				// enable run-time checks when not in production
				dev: !production,
			}
		}),
		// we'll extract any component CSS out into
		// a separate file - better for performance
		css({ output: 'bundle.css' }),

		// If you have external dependencies installed from
		// npm, you'll most likely need these plugins. In
		// some cases you'll need additional configuration -
		// consult the documentation for details:
		// https://github.com/rollup/plugins/tree/master/packages/commonjs
		resolve({
			browser: true,
			dedupe: ['svelte']
		}),
		commonjs(),
		typescript({
			sourceMap: true,
			inlineSources: !production,
			tsconfig: './tsconfig.json'
		}),

		// In dev mode, call `npm run start` once
		// the bundle has been generated
		!production && serve(),

		// Watch the `public` directory and refresh the
		// browser on changes when not in production
		!production && livereload('public'),
	],
	watch: {
		clearScreen: false
	}
};