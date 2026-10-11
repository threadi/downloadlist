import { test, expect } from './fixtures';

/**
 * Tests for the output of download lists in the frontend.
 */
test.describe( 'Frontend', () => {
	// @ts-ignore
	test( 'Plugin is active', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/wp-admin/plugins.php` );
		await expect( page.locator( 'tr[data-slug="download-list-block-with-icons"] .deactivate a' ) ).toBeVisible();
	} );

	// @ts-ignore
	test( 'List shows the chosen files with link, icon and file size', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/?name=playwright-files` );

		const list = page.locator( 'ul.wp-block-downloadlist-list' );
		await expect( list ).toBeVisible();
		await expect( list ).toHaveClass( /iconset-/ );

		// both files in the chosen order.
		const items = list.locator( 'li' );
		await expect( items ).toHaveCount( 2 );
		await expect( items.nth( 0 ) ).toContainText( 'Playwright Image' );
		await expect( items.nth( 1 ) ).toContainText( 'Playwright Archive' );

		// the classes for the icons of the file types.
		await expect( items.nth( 0 ) ).toHaveClass( /file_png/ );
		await expect( items.nth( 1 ) ).toHaveClass( /file_zip/ );

		// the link to the file and the file size.
		await expect( items.nth( 0 ).locator( 'a' ).first() ).toHaveAttribute( 'href', /\/wp-content\/uploads\/.*playwright-image.*\.png$/ );
		await expect( items.nth( 0 ) ).toContainText( /\(\d+([.,]\d+)?\s?[KMG]?B\)/ );

		// the styles of the iconset are loaded.
		const before = await items.nth( 0 ).evaluate( ( element ) => window.getComputedStyle( element, '::before' ).content );
		expect( before ).not.toBe( 'none' );
	} );

	// @ts-ignore
	test( 'List shows the files of a download list', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/?name=playwright-download-list` );

		const items = page.locator( 'ul.wp-block-downloadlist-list li' );
		await expect( items ).toHaveCount( 1 );
		await expect( items.first() ).toContainText( 'Playwright Archive' );
	} );

	// @ts-ignore
	test( 'List shows the files of a folder, optionally with its subfolders', async ( { page, cli } ) => {
		// only the folder itself.
		await page.goto( `${ cli.serverUrl }/?name=playwright-folder` );
		let items = page.locator( 'ul.wp-block-downloadlist-list li' );
		await expect( items ).toHaveCount( 1 );
		await expect( items.first() ).toContainText( 'Playwright Image' );

		// with the subfolders.
		await page.goto( `${ cli.serverUrl }/?name=playwright-subfolders` );
		items = page.locator( 'ul.wp-block-downloadlist-list li' );
		await expect( items ).toHaveCount( 2 );
		await expect( page.locator( 'ul.wp-block-downloadlist-list' ) ).toContainText( 'Playwright Text' );
	} );

	// @ts-ignore
	test( 'List shows preview images in frames', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/?name=playwright-preview` );

		const list = page.locator( 'ul.wp-block-downloadlist-list' );
		await expect( list ).toHaveClass( /has-media-frame/ );
		await expect( list ).toHaveAttribute( 'style', /--downloadlist-media-size:64px/ );

		// the image has a preview, the archive keeps its icon.
		const items = list.locator( 'li' );
		await expect( items.nth( 0 ) ).toHaveClass( /has-preview/ );
		await expect( items.nth( 0 ).locator( 'img.downloadlist-preview' ) ).toBeVisible();
		await expect( items.nth( 1 ) ).not.toHaveClass( /has-preview/ );

		// the frame is drawn with the chosen size.
		const box = await items.nth( 0 ).locator( 'img.downloadlist-preview' ).boundingBox();
		expect( Math.round( box?.width ?? 0 ) ).toBe( 64 );
		expect( Math.round( box?.height ?? 0 ) ).toBe( 64 );
	} );
} );
