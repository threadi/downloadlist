import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginBlockSettingsMenuItem } from '@wordpress/edit-post';

const PluginBlockSettingsMenuGroupTest = () => (
	<PluginBlockSettingsMenuItem
		allowedBlocks={ [ 'downloadlist/list' ] }
		icon="smiley"
		label={__( 'Block settings', 'downloadlist')}
		onClick={ () => {
			alert( 'clicked' );
		} }
	/>
);

registerPlugin( 'block-settings-menu-group-test', {
	render: PluginBlockSettingsMenuGroupTest,
} );
