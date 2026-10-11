import { test, expect } from './fixtures';

/**
 * Tests for the REST API and the abilities of the plugin.
 */
test.describe( 'REST API and abilities', () => {
	// @ts-ignore
	test( 'Endpoints for the block editor return the files and folders', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/wp-admin/` );

		const result = await page.evaluate( async () => {
			// @ts-ignore
			const media = await window.wp.apiFetch( { path: '/wp/v2/media?search=Playwright%20Image&_fields=id' } );
			// @ts-ignore
			const files = await window.wp.apiFetch( { path: `/downloadlist/v1/files?post_ids[]=${ media[ 0 ].id }` } );
			// @ts-ignore
			const folders = await window.wp.apiFetch( { path: '/downloadlist/v1/folders' } );
			return { files, folders };
		} );

		expect( result.files ).toHaveLength( 1 );
		expect( result.files[ 0 ].title ).toBe( 'Playwright Image' );
		expect( result.files[ 0 ].downloadlist_preview_type ).toBe( 'image' );
		expect( result.folders.map( ( folder: { name: string } ) => folder.name ) ).toEqual( [ 'Playwright Folder', 'Playwright Subfolder' ] );
	} );

	// @ts-ignore
	test( 'Abilities are available and find where a file is shown', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/wp-admin/` );

		const result = await page.evaluate( async () => {
			// @ts-ignore
			const apiFetch = window.wp.apiFetch;
			try {
				const abilities = await apiFetch( { path: '/wp-abilities/v1/abilities?category=download-list-block-with-icons' } );
				const media = await apiFetch( { path: '/wp/v2/media?search=Playwright%20Archive&_fields=id' } );
				const usage = await apiFetch( { path: `/wp-abilities/v1/abilities/downloadlist/get-file-usage/run?input[file_id]=${ media[ 0 ].id }` } );
				return { available: true, names: abilities.map( ( ability: { name: string } ) => ability.name ), usage };
			} catch ( error ) {
				// the Abilities API is part of WordPress since 6.9.
				return { available: false, names: [], usage: null };
			}
		} );

		test.skip( ! result.available, 'The Abilities API is not available in this WordPress version.' );

		// only the reading abilities are offered by default.
		expect( result.names ).toContain( 'downloadlist/get-usage' );
		expect( result.names ).toContain( 'downloadlist/get-file-usage' );
		expect( result.names ).not.toContain( 'downloadlist/add-list-to-post' );

		// the archive is shown directly and via the download list.
		const titles = result.usage.objects.map( ( object: { title: string } ) => object.title ).sort();
		expect( titles ).toEqual( [ 'Playwright Download List', 'Playwright Files', 'Playwright Preview' ] );
	} );
} );
