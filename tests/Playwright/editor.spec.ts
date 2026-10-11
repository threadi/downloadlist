import { test, expect, openEditor, getPostId } from './fixtures';

/**
 * Tests for the block in the block editor.
 */
test.describe( 'Block editor', () => {
	// @ts-ignore
	test( 'Existing list shows its files in the editor', async ( { page, cli } ) => {
		await page.goto( `${ cli.serverUrl }/wp-admin/` );
		const postId = await getPostId( page, 'playwright-files' );
		await openEditor( page, `${ cli.serverUrl }/wp-admin/post.php?post=${ postId }&action=edit` );

		const canvas = page.frameLocator( 'iframe[name="editor-canvas"]' );
		const items = canvas.locator( '.wp-block-downloadlist-list-draggable' );
		await expect( items ).toHaveCount( 2 );
		await expect( items.nth( 0 ) ).toContainText( 'Playwright Image' );
		await expect( items.nth( 1 ) ).toContainText( 'Playwright Archive' );
	} );

	// @ts-ignore
	test( 'New block offers files, download lists and folders as source', async ( { page, cli } ) => {
		await openEditor( page, `${ cli.serverUrl }/wp-admin/post-new.php` );

		// insert the block.
		await page.evaluate( () => {
			// @ts-ignore
			const block = window.wp.blocks.createBlock( 'downloadlist/list' );
			// @ts-ignore
			window.wp.data.dispatch( 'core/block-editor' ).insertBlocks( block );
		} );

		const canvas = page.frameLocator( 'iframe[name="editor-canvas"]' );
		await expect( canvas.getByRole( 'button', { name: 'Add your first file' } ) ).toBeVisible();

		// the detected folder plugin is offered.
		const folderButton = canvas.getByRole( 'button', { name: 'Show a folder from Playwright Folders' } );
		await expect( folderButton ).toBeVisible();

		// choose the folder with its subfolders.
		await folderButton.click();
		await page.getByRole( 'menuitemradio', { name: /Playwright Folder \(/ } ).click();
		await expect( canvas.locator( '.wp-block-downloadlist-list-draggable' ) ).toHaveCount( 1 );
		await expect( canvas.locator( '.wp-block-downloadlist-list-draggable' ).first() ).toContainText( 'Playwright Image' );

		// the attributes of the block are set.
		const attributes = await page.evaluate( () => {
			// @ts-ignore
			return window.wp.data.select( 'core/block-editor' ).getBlocks()[ 0 ].attributes;
		} );
		expect( attributes.folderTaxonomy ).toBe( 'playwright_folders' );
		expect( attributes.folder ).toBeGreaterThan( 0 );
	} );

	// @ts-ignore
	test( 'Block settings offer previews and styles', async ( { page, cli } ) => {
		await openEditor( page, `${ cli.serverUrl }/wp-admin/post-new.php` );
		await page.evaluate( () => {
			// @ts-ignore
			const block = window.wp.blocks.createBlock( 'downloadlist/list' );
			// @ts-ignore
			window.wp.data.dispatch( 'core/block-editor' ).insertBlocks( block );
			// @ts-ignore
			window.wp.data.dispatch( 'core/edit-post' ).openGeneralSidebar( 'edit-post/block' );
		} );

		// the setting for preview images.
		const preview = page.getByRole( 'checkbox', { name: 'Show preview images' } );
		await expect( preview ).toBeVisible();
		await preview.check();
		const attributes = await page.evaluate( () => {
			// @ts-ignore
			return window.wp.data.select( 'core/block-editor' ).getBlocks()[ 0 ].attributes;
		} );
		expect( attributes.showPreview ).toBe( true );

		// the styles for the frames.
		await page.getByRole( 'tab', { name: 'Styles' } ).click();
		await expect( page.getByText( 'Frame for icons and previews' ) ).toBeVisible();
		await expect( page.getByRole( 'spinbutton', { name: 'Frame width in pixel' } ) ).toBeVisible();
	} );
} );
