import { test, expect } from './fixtures';

/**
 * Tests for the settings of the plugin.
 */
test.describe( 'Settings', () => {
	// @ts-ignore
	test( 'Settings page loads with its tabs', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/wp-admin/options-general.php?page=downloadlist_settings` );
		await expect( page.getByRole( 'heading', { level: 1 } ) ).toContainText( 'Settings for Download List Block with Icons' );
		for ( const tab of [ 'Block settings', 'Settings', 'Tasks', 'Usage' ] ) {
			await expect( page.getByRole( 'tab', { name: tab, exact: true } ) ).toBeVisible();
		}
	} );

	// @ts-ignore
	test( 'Saved block setting is used as default by the lists', async ( { page, cli } ) => {
		// this test loads the settings page several times.
		test.slow();

		// the file size is shown by default.
		await page.goto( `${ cli.serverUrl }/?name=playwright-files` );
		await expect( page.locator( 'ul.wp-block-downloadlist-list li' ).first() ).toContainText( /B\)/ );

		// hide the file sizes in the settings.
		await page.goto( `${ cli.serverUrl }/wp-admin/options-general.php?page=downloadlist_settings` );
		const hideFileSizes = page.getByRole( 'checkbox', { name: 'Hide file sizes' } );
		await hideFileSizes.check();
		await page.getByRole( 'button', { name: 'Save', exact: true } ).click();
		await page.reload();
		await expect( page.getByRole( 'checkbox', { name: 'Hide file sizes' } ) ).toBeChecked();

		// the list uses the new default.
		await page.goto( `${ cli.serverUrl }/?name=playwright-files` );
		await expect( page.locator( 'ul.wp-block-downloadlist-list li' ).first() ).not.toContainText( /B\)/ );

		// restore the setting for the other tests.
		await page.goto( `${ cli.serverUrl }/wp-admin/options-general.php?page=downloadlist_settings` );
		await page.getByRole( 'checkbox', { name: 'Hide file sizes' } ).uncheck();
		await page.getByRole( 'button', { name: 'Save', exact: true } ).click();
		await page.reload();
		await expect( page.getByRole( 'checkbox', { name: 'Hide file sizes' } ) ).not.toBeChecked();
	} );

	// @ts-ignore
	test( 'Settings of the plugin show the folder plugin and the abilities', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/wp-admin/options-general.php?page=downloadlist_settings&tab=downloadlist_plugin` );

		// the folder taxonomy of the test is detected.
		await expect( page.getByRole( 'checkbox', { name: 'Use folders as source of download lists' } ) ).toBeChecked();
		await expect( page.getByText( 'Found in this project: Playwright Folders' ) ).toBeVisible();

		// the abilities are enabled, read only is preselected.
		await expect( page.getByRole( 'checkbox', { name: 'Enable abilities' } ) ).toBeChecked();
		await expect( page.getByRole( 'combobox', { name: 'Allowed abilities' } ) ).toHaveValue( 'read' );
		await expect( page.getByText( 'Can the abilities be used in this project?' ) ).toBeVisible();
	} );

	// @ts-ignore
	test( 'Usage tab lists all objects with download lists', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/wp-admin/options-general.php?page=downloadlist_settings&tab=downloadlist_usage` );

		const table = page.locator( 'table.wp-list-table' );
		for ( const title of [ 'Playwright Files', 'Playwright Download List', 'Playwright Folder', 'Playwright Subfolders', 'Playwright Preview' ] ) {
			await expect( table.getByRole( 'link', { name: title, exact: true } ).first() ).toBeVisible();
		}

		// the number of files of the list with subfolders.
		const row = table.locator( 'tr', { has: page.getByRole( 'link', { name: 'Playwright Subfolders', exact: true } ) } );
		await expect( row.locator( 'td.column-files' ) ).toHaveText( '2' );
	} );
} );
