<?php

if (!class_exists('WPSE_The_Events_Calendar_Venues')) {

	class WPSE_The_Events_Calendar_Venues extends WPSE_Sheet_Factory {

		function __construct() {
			$allowed_columns = array();

			if (!wpsee_fs()->can_use_premium_code__premium_only()) {
				$allowed_columns = array(
					'ID',
					'post_title',
					'post_content',
					'post_status',
					'post_name',
					'view_post',
				);
			}

			parent::__construct(array(
				'fs_object' => wpsee_fs(),
				'post_type' => array('tribe_venue'),
				'post_type_label' => array('Venues'),
				'serialized_columns' => array(), // column keys
				'columns' => array($this, 'get_columns'),
				'toolbars' => array($this, 'get_toolbars'),
				'allowed_columns' => $allowed_columns,
			));

			add_filter('vg_sheet_editor/provider/post/get_items_args', array($this, 'disable_date_filter'));
		}

		function get_toolbars() {
			$toolbars = array();
			$post_types = array('tribe_events' => 'Events', 'tribe_organizer' => 'Organizers');
			foreach ($post_types as $post_type => $post_type_label) {
				$toolbars['bulk_edit_' . $post_type] = array(
					'type' => 'button',
					'help_tooltip' => __('Open spreadsheet.', vgse_events()->textname),
					'content' => sprintf(__('Edit %s', vgse_events()->textname), esc_html($post_type_label)),
					'icon' => 'fa fa-edit',
					'url' => VGSE()->helpers->get_editor_url($post_type),
				);
			}
			return $toolbars;
		}

		function get_columns() {
			$columns = array();

			if (wpsee_fs()->can_use_premium_code__premium_only()) {
				$countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");

				$columns['_VenueCountry'] = array(
					'data_type' => 'meta_data',
					'title' => __('Country', vgse_events()->textname),
					'formatted' => array('data' => '_VenueCountry',
						'editor' => 'select',
						'selectOptions' => $countries
					),
					'default_value' => 'United States',
				);
				$columns['_VenueOrigin'] = array(
					'data_type' => 'meta_data',
					'title' => __('Origin', vgse_events()->textname),
					'formatted' => array('data' => '_EventOrigin',
						'editor' => 'select',
						'selectOptions' => array(
							'events-calendar' => __('WordPress', vgse_events()->textname),
							'event-aggregator' => __('Percentage discount', vgse_events()->textname),
						)
					),
					'default_value' => 'events-calendar',
				);
				$columns['_VenueShowMapLink'] = array(
					'data_type' => 'meta_data',
					'title' => __('Show map link', vgse_events()->textname),
					'formatted' => array('data' => '_VenueShowMapLink',
						'type' => 'checkbox',
						'checkedTemplate' => '1',
						'uncheckedTemplate' => '',
					),
				);
				$columns['_VenueShowMap'] = array(
					'data_type' => 'meta_data',
					'title' => __('Show map', vgse_events()->textname),
					'formatted' => array('data' => '_VenueShowMap',
						'type' => 'checkbox',
						'checkedTemplate' => '1',
						'uncheckedTemplate' => '',
					),
				);
			}

			return $columns;
		}

		function disable_date_filter($wp_query) {

			if (in_array($wp_query['post_type'], $this->post_type)) {
				$wp_query['tribe_remove_date_filters'] = true;
			}
			return $wp_query;
		}

	}

	new WPSE_The_Events_Calendar_Venues();
}