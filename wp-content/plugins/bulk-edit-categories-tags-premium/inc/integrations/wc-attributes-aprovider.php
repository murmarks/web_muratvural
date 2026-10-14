<?php

// We added the "a" prefix to the name so this file loads first
// Fix. If they update one plugin and use an old version of another,
// the Abstract class might not exist and they will get fatal errors.
// So we make sure it loads the class from the current plugin if it's missing
// This can be removed in a future update.
if (!class_exists('VGSE_Provider_Abstract')) {
	require_once vgse_taxonomy_terms()->plugin_dir . '/modules/wp-sheet-editor/inc/providers/abstract.php';
}

class VGSE_Provider_Wc_attributes extends VGSE_Provider_Custom_table {

	var $key = 'wc_attributes';

}
