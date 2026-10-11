// @ts-ignore
import { test as base, expect, type Page } from '@playwright/test';
// @ts-ignore
import { runCLI } from '@wp-playground/cli';
// @ts-ignore
import { execSync } from 'node:child_process';
// @ts-ignore
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
// @ts-ignore
import path from 'node:path';

// @ts-ignore
const pluginRoot = path.resolve( __dirname, '../..' );
const pluginSlug = 'download-list-block-with-icons';
const autoloadPath = path.join( pluginRoot, 'vendor', 'autoload.php' );
const lockPath = path.join( pluginRoot, 'composer.lock' );

/**
 * Return whether composer install must run (no autoloader or an older one than composer.lock).
 */
function needsComposerInstall(): boolean {
	if ( ! existsSync( autoloadPath ) ) {
		return true;
	}
	if ( ! existsSync( lockPath ) ) {
		return false;
	}
	return statSync( lockPath ).mtimeMs > statSync( autoloadPath ).mtimeMs;
}

/**
 * Install the composer dependencies, if necessary.
 */
function ensureComposerDeps(): void {
	if ( ! needsComposerInstall() ) {
		console.log( 'vendor/autoload.php is up to date – do not run composer install.' );
		return;
	}
	console.log( 'Running composer install …' );
	execSync( 'composer install --optimize-autoloader', {
		cwd: pluginRoot,
		stdio: 'inherit',
	} );
}

/**
 * Return the time of the last change of the files in the given directory, including subdirectories.
 *
 * @param directory The directory.
 */
function getNewestChange( directory: string ): number {
	let newest = 0;
	for ( const entry of readdirSync( directory, { withFileTypes: true } ) ) {
		const entryPath = path.join( directory, entry.name );
		newest = Math.max( newest, entry.isDirectory() ? getNewestChange( entryPath ) : statSync( entryPath ).mtimeMs );
	}
	return newest;
}

/**
 * Make sure the block has been built from the actual sources, as the editor and the frontend need it.
 */
function ensureBlockBuild(): void {
	const buildPath = path.join( pluginRoot, 'block', 'index.js' );
	if ( ! existsSync( buildPath ) ) {
		throw new Error( 'The block is not built. Run "npm run build" before the Playwright tests.' );
	}
	if ( getNewestChange( path.join( pluginRoot, 'src' ) ) > statSync( buildPath ).mtimeMs ) {
		throw new Error( 'The block is older than its sources in "src". Run "npm run build" before the Playwright tests.' );
	}
}

type CliServer = Awaited<ReturnType<typeof runCLI>>;

export const test = base.extend<{}, { cli: CliServer }>( {
	// worker-scoped: runs once per test worker, not per test.
	cli: [
		// @ts-ignore
		async ( {}, use ) => {
			ensureComposerDeps();
			ensureBlockBuild();

			const cli = await runCLI( {
				command: 'server',
				workers: 6,
				quiet: true,
				mount: [
					{
						hostPath: pluginRoot,
						vfsPath: `/wordpress/wp-content/plugins/${ pluginSlug }`,
					},
				],
				blueprint: {
					// the WordPress version could be set via environment, e.g. to test a release candidate or a local zip.
					// @ts-ignore
					preferredVersions: { php: '8.3', wp: process.env.WP_PLAYGROUND_WP || 'latest' },
					login: true,
					steps: [
						{
							step: 'activatePlugin',
							pluginPath: `${ pluginSlug }/${ pluginSlug }.php`,
						},
						// a folder taxonomy for the media library, like folder plugins (e.g. Folderly) register it.
						{
							step: 'writeFile',
							path: '/wordpress/wp-content/mu-plugins/playwright-folders.php',
							data: readFileSync( path.join( __dirname, 'setup', 'folders.php' ), 'utf8' ),
						},
						// files, a download list, folders and posts with download lists.
						{
							step: 'runPHP',
							code: `<?php require '/wordpress/wp-content/plugins/${ pluginSlug }/tests/Playwright/setup/content.php';`,
						},
					],
				},
			} );

			await use( cli );

			await cli?.server?.close();
		},
		{ scope: 'worker' },
	],
} );

/**
 * Open the block editor for the given URL and wait until it is ready.
 *
 * @param page The page.
 * @param url  The URL of the editor.
 */
export async function openEditor( page: Page, url: string ): Promise<void> {
	await page.goto( url );
	await page.waitForFunction( () => {
		// @ts-ignore
		return !! window.wp?.data?.select( 'core/editor' )?.getCurrentPostId?.();
	} );

	// close the welcome guide, which would cover the editor.
	await page.evaluate( () => {
		// @ts-ignore
		window.wp.data.dispatch( 'core/preferences' ).set( 'core/edit-post', 'welcomeGuide', false );
	} );
}

/**
 * Return the ID of the published post with the given slug.
 *
 * @param page The page (in WP admin, where the REST API is available).
 * @param slug The slug of the post.
 */
export async function getPostId( page: Page, slug: string ): Promise<number> {
	return page.evaluate( async ( postSlug ) => {
		// @ts-ignore
		const posts = await window.wp.apiFetch( { path: `/wp/v2/posts?slug=${ postSlug }&_fields=id` } );
		return posts[ 0 ].id;
	}, slug );
}

export { expect };
