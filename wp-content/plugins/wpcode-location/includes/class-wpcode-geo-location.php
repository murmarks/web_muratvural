<?php
/**
 * Base class for location data.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Geo_Location. Exposes the logic to get location data by IP.
 *
 * @package wpcode-location
 */
class WPCode_Geo_Location {

	/**
	 * Available sources for retrieving location data by API.
	 *
	 * @var string[]
	 */
	private $sources = array(
		'wpcode' => 'https://geo.wpcode.com/v3/geolocate/json/%s',
		'ipapi'  => 'https://ipapi.co/%s/json',
		'keycdn' => 'https://tools.keycdn.com/geo.json?host=%s',
	);

	/**
	 * Get an array of continents.
	 *
	 * @return array
	 */
	public static function get_continents() {
		return array(
			'AF' => array(
				'name'      => __( 'Africa', 'wpcode-location' ),
				'countries' => array(
					'AO',
					'BF',
					'BI',
					'BJ',
					'BW',
					'CD',
					'CF',
					'CG',
					'CI',
					'CM',
					'CV',
					'DJ',
					'DZ',
					'EG',
					'EH',
					'ER',
					'ET',
					'GA',
					'GH',
					'GM',
					'GN',
					'GQ',
					'GW',
					'KE',
					'KM',
					'LR',
					'LS',
					'LY',
					'MA',
					'MG',
					'ML',
					'MR',
					'MU',
					'MW',
					'MZ',
					'NA',
					'NE',
					'NG',
					'RE',
					'RW',
					'SC',
					'SD',
					'SH',
					'SL',
					'SN',
					'SO',
					'SS',
					'ST',
					'SZ',
					'TD',
					'TG',
					'TN',
					'TZ',
					'UG',
					'YT',
					'ZA',
					'ZM',
					'ZW',
				),
			),
			'AN' => array(
				'name'      => __( 'Antarctica', 'wpcode-location' ),
				'countries' => array(
					'AQ',
					'BV',
					'GS',
					'HM',
					'TF',
				),
			),
			'AS' => array(
				'name'      => __( 'Asia', 'wpcode-location' ),
				'countries' => array(
					'AE',
					'AF',
					'AM',
					'AZ',
					'BD',
					'BH',
					'BN',
					'BT',
					'CC',
					'CN',
					'CX',
					'CY',
					'GE',
					'HK',
					'ID',
					'IL',
					'IN',
					'IO',
					'IQ',
					'IR',
					'JO',
					'JP',
					'KG',
					'KH',
					'KP',
					'KR',
					'KW',
					'KZ',
					'LA',
					'LB',
					'LK',
					'MM',
					'MN',
					'MO',
					'MV',
					'MY',
					'NP',
					'OM',
					'PH',
					'PK',
					'PS',
					'QA',
					'SA',
					'SG',
					'SY',
					'TH',
					'TJ',
					'TL',
					'TM',
					'TW',
					'UZ',
					'VN',
					'YE',
				),
			),
			'EU' => array(
				'name'      => __( 'Europe', 'wpcode-location' ),
				'countries' => array(
					'AD',
					'AL',
					'AT',
					'AX',
					'BA',
					'BE',
					'BG',
					'BY',
					'CH',
					'CZ',
					'DE',
					'DK',
					'EE',
					'ES',
					'FI',
					'FO',
					'FR',
					'GB',
					'GG',
					'GI',
					'GR',
					'HR',
					'HU',
					'IE',
					'IM',
					'IS',
					'IT',
					'JE',
					'LI',
					'LT',
					'LU',
					'LV',
					'MC',
					'MD',
					'ME',
					'MK',
					'MT',
					'NL',
					'NO',
					'PL',
					'PT',
					'RO',
					'RS',
					'RU',
					'SE',
					'SI',
					'SJ',
					'SK',
					'SM',
					'TR',
					'UA',
					'VA',
				),
			),
			'NA' => array(
				'name'      => __( 'North America', 'wpcode-location' ),
				'countries' => array(
					'AG',
					'AI',
					'AW',
					'BB',
					'BL',
					'BM',
					'BQ',
					'BS',
					'BZ',
					'CA',
					'CR',
					'CU',
					'CW',
					'DM',
					'DO',
					'GD',
					'GL',
					'GP',
					'GT',
					'HN',
					'HT',
					'JM',
					'KN',
					'KY',
					'LC',
					'MF',
					'MQ',
					'MS',
					'MX',
					'NI',
					'PA',
					'PM',
					'PR',
					'SV',
					'SX',
					'TC',
					'TT',
					'US',
					'VC',
					'VG',
					'VI',
				),
			),
			'OC' => array(
				'name'      => __( 'Oceania', 'wpcode-location' ),
				'countries' => array(
					'AS',
					'AU',
					'CK',
					'FJ',
					'FM',
					'GU',
					'KI',
					'MH',
					'MP',
					'NC',
					'NF',
					'NR',
					'NU',
					'NZ',
					'PF',
					'PG',
					'PN',
					'PW',
					'SB',
					'TK',
					'TO',
					'TV',
					'UM',
					'VU',
					'WF',
					'WS',
				),
			),
			'SA' => array(
				'name'      => __( 'South America', 'wpcode-location' ),
				'countries' => array(
					'AR',
					'BO',
					'BR',
					'CL',
					'CO',
					'EC',
					'FK',
					'GF',
					'GY',
					'PE',
					'PY',
					'SR',
					'UY',
					'VE',
				),
			),
		);
	}

	/**
	 * Get an array of countries.
	 *
	 * @return array
	 */
	public static function get_countries() {
		return array(
			'AF' => __( 'Afghanistan', 'wpcode-location' ),
			'AX' => __( 'Åland Islands', 'wpcode-location' ),
			'AL' => __( 'Albania', 'wpcode-location' ),
			'DZ' => __( 'Algeria', 'wpcode-location' ),
			'AS' => __( 'American Samoa', 'wpcode-location' ),
			'AD' => __( 'Andorra', 'wpcode-location' ),
			'AO' => __( 'Angola', 'wpcode-location' ),
			'AI' => __( 'Anguilla', 'wpcode-location' ),
			'AQ' => __( 'Antarctica', 'wpcode-location' ),
			'AG' => __( 'Antigua and Barbuda', 'wpcode-location' ),
			'AR' => __( 'Argentina', 'wpcode-location' ),
			'AM' => __( 'Armenia', 'wpcode-location' ),
			'AW' => __( 'Aruba', 'wpcode-location' ),
			'AU' => __( 'Australia', 'wpcode-location' ),
			'AT' => __( 'Austria', 'wpcode-location' ),
			'AZ' => __( 'Azerbaijan', 'wpcode-location' ),
			'BS' => __( 'Bahamas', 'wpcode-location' ),
			'BH' => __( 'Bahrain', 'wpcode-location' ),
			'BD' => __( 'Bangladesh', 'wpcode-location' ),
			'BB' => __( 'Barbados', 'wpcode-location' ),
			'BY' => __( 'Belarus', 'wpcode-location' ),
			'BE' => __( 'Belgium', 'wpcode-location' ),
			'PW' => __( 'Belau', 'wpcode-location' ),
			'BZ' => __( 'Belize', 'wpcode-location' ),
			'BJ' => __( 'Benin', 'wpcode-location' ),
			'BM' => __( 'Bermuda', 'wpcode-location' ),
			'BT' => __( 'Bhutan', 'wpcode-location' ),
			'BO' => __( 'Bolivia', 'wpcode-location' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'wpcode-location' ),
			'BA' => __( 'Bosnia and Herzegovina', 'wpcode-location' ),
			'BW' => __( 'Botswana', 'wpcode-location' ),
			'BV' => __( 'Bouvet Island', 'wpcode-location' ),
			'BR' => __( 'Brazil', 'wpcode-location' ),
			'IO' => __( 'British Indian Ocean Territory', 'wpcode-location' ),
			'BN' => __( 'Brunei', 'wpcode-location' ),
			'BG' => __( 'Bulgaria', 'wpcode-location' ),
			'BF' => __( 'Burkina Faso', 'wpcode-location' ),
			'BI' => __( 'Burundi', 'wpcode-location' ),
			'KH' => __( 'Cambodia', 'wpcode-location' ),
			'CM' => __( 'Cameroon', 'wpcode-location' ),
			'CA' => __( 'Canada', 'wpcode-location' ),
			'CV' => __( 'Cape Verde', 'wpcode-location' ),
			'KY' => __( 'Cayman Islands', 'wpcode-location' ),
			'CF' => __( 'Central African Republic', 'wpcode-location' ),
			'TD' => __( 'Chad', 'wpcode-location' ),
			'CL' => __( 'Chile', 'wpcode-location' ),
			'CN' => __( 'China', 'wpcode-location' ),
			'CX' => __( 'Christmas Island', 'wpcode-location' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'wpcode-location' ),
			'CO' => __( 'Colombia', 'wpcode-location' ),
			'KM' => __( 'Comoros', 'wpcode-location' ),
			'CG' => __( 'Congo (Brazzaville)', 'wpcode-location' ),
			'CD' => __( 'Congo (Kinshasa)', 'wpcode-location' ),
			'CK' => __( 'Cook Islands', 'wpcode-location' ),
			'CR' => __( 'Costa Rica', 'wpcode-location' ),
			'HR' => __( 'Croatia', 'wpcode-location' ),
			'CU' => __( 'Cuba', 'wpcode-location' ),
			'CW' => __( 'Cura&ccedil;ao', 'wpcode-location' ),
			'CY' => __( 'Cyprus', 'wpcode-location' ),
			'CZ' => __( 'Czech Republic', 'wpcode-location' ),
			'DK' => __( 'Denmark', 'wpcode-location' ),
			'DJ' => __( 'Djibouti', 'wpcode-location' ),
			'DM' => __( 'Dominica', 'wpcode-location' ),
			'DO' => __( 'Dominican Republic', 'wpcode-location' ),
			'EC' => __( 'Ecuador', 'wpcode-location' ),
			'EG' => __( 'Egypt', 'wpcode-location' ),
			'SV' => __( 'El Salvador', 'wpcode-location' ),
			'GQ' => __( 'Equatorial Guinea', 'wpcode-location' ),
			'ER' => __( 'Eritrea', 'wpcode-location' ),
			'EE' => __( 'Estonia', 'wpcode-location' ),
			'ET' => __( 'Ethiopia', 'wpcode-location' ),
			'FK' => __( 'Falkland Islands', 'wpcode-location' ),
			'FO' => __( 'Faroe Islands', 'wpcode-location' ),
			'FJ' => __( 'Fiji', 'wpcode-location' ),
			'FI' => __( 'Finland', 'wpcode-location' ),
			'FR' => __( 'France', 'wpcode-location' ),
			'GF' => __( 'French Guiana', 'wpcode-location' ),
			'PF' => __( 'French Polynesia', 'wpcode-location' ),
			'TF' => __( 'French Southern Territories', 'wpcode-location' ),
			'GA' => __( 'Gabon', 'wpcode-location' ),
			'GM' => __( 'Gambia', 'wpcode-location' ),
			'GE' => __( 'Georgia', 'wpcode-location' ),
			'DE' => __( 'Germany', 'wpcode-location' ),
			'GH' => __( 'Ghana', 'wpcode-location' ),
			'GI' => __( 'Gibraltar', 'wpcode-location' ),
			'GR' => __( 'Greece', 'wpcode-location' ),
			'GL' => __( 'Greenland', 'wpcode-location' ),
			'GD' => __( 'Grenada', 'wpcode-location' ),
			'GP' => __( 'Guadeloupe', 'wpcode-location' ),
			'GU' => __( 'Guam', 'wpcode-location' ),
			'GT' => __( 'Guatemala', 'wpcode-location' ),
			'GG' => __( 'Guernsey', 'wpcode-location' ),
			'GN' => __( 'Guinea', 'wpcode-location' ),
			'GW' => __( 'Guinea-Bissau', 'wpcode-location' ),
			'GY' => __( 'Guyana', 'wpcode-location' ),
			'HT' => __( 'Haiti', 'wpcode-location' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'wpcode-location' ),
			'HN' => __( 'Honduras', 'wpcode-location' ),
			'HK' => __( 'Hong Kong', 'wpcode-location' ),
			'HU' => __( 'Hungary', 'wpcode-location' ),
			'IS' => __( 'Iceland', 'wpcode-location' ),
			'IN' => __( 'India', 'wpcode-location' ),
			'ID' => __( 'Indonesia', 'wpcode-location' ),
			'IR' => __( 'Iran', 'wpcode-location' ),
			'IQ' => __( 'Iraq', 'wpcode-location' ),
			'IE' => __( 'Ireland', 'wpcode-location' ),
			'IM' => __( 'Isle of Man', 'wpcode-location' ),
			'IL' => __( 'Israel', 'wpcode-location' ),
			'IT' => __( 'Italy', 'wpcode-location' ),
			'CI' => __( 'Ivory Coast', 'wpcode-location' ),
			'JM' => __( 'Jamaica', 'wpcode-location' ),
			'JP' => __( 'Japan', 'wpcode-location' ),
			'JE' => __( 'Jersey', 'wpcode-location' ),
			'JO' => __( 'Jordan', 'wpcode-location' ),
			'KZ' => __( 'Kazakhstan', 'wpcode-location' ),
			'KE' => __( 'Kenya', 'wpcode-location' ),
			'KI' => __( 'Kiribati', 'wpcode-location' ),
			'KW' => __( 'Kuwait', 'wpcode-location' ),
			'KG' => __( 'Kyrgyzstan', 'wpcode-location' ),
			'LA' => __( 'Laos', 'wpcode-location' ),
			'LV' => __( 'Latvia', 'wpcode-location' ),
			'LB' => __( 'Lebanon', 'wpcode-location' ),
			'LS' => __( 'Lesotho', 'wpcode-location' ),
			'LR' => __( 'Liberia', 'wpcode-location' ),
			'LY' => __( 'Libya', 'wpcode-location' ),
			'LI' => __( 'Liechtenstein', 'wpcode-location' ),
			'LT' => __( 'Lithuania', 'wpcode-location' ),
			'LU' => __( 'Luxembourg', 'wpcode-location' ),
			'MO' => __( 'Macao', 'wpcode-location' ),
			'MK' => __( 'North Macedonia', 'wpcode-location' ),
			'MG' => __( 'Madagascar', 'wpcode-location' ),
			'MW' => __( 'Malawi', 'wpcode-location' ),
			'MY' => __( 'Malaysia', 'wpcode-location' ),
			'MV' => __( 'Maldives', 'wpcode-location' ),
			'ML' => __( 'Mali', 'wpcode-location' ),
			'MT' => __( 'Malta', 'wpcode-location' ),
			'MH' => __( 'Marshall Islands', 'wpcode-location' ),
			'MQ' => __( 'Martinique', 'wpcode-location' ),
			'MR' => __( 'Mauritania', 'wpcode-location' ),
			'MU' => __( 'Mauritius', 'wpcode-location' ),
			'YT' => __( 'Mayotte', 'wpcode-location' ),
			'MX' => __( 'Mexico', 'wpcode-location' ),
			'FM' => __( 'Micronesia', 'wpcode-location' ),
			'MD' => __( 'Moldova', 'wpcode-location' ),
			'MC' => __( 'Monaco', 'wpcode-location' ),
			'MN' => __( 'Mongolia', 'wpcode-location' ),
			'ME' => __( 'Montenegro', 'wpcode-location' ),
			'MS' => __( 'Montserrat', 'wpcode-location' ),
			'MA' => __( 'Morocco', 'wpcode-location' ),
			'MZ' => __( 'Mozambique', 'wpcode-location' ),
			'MM' => __( 'Myanmar', 'wpcode-location' ),
			'NA' => __( 'Namibia', 'wpcode-location' ),
			'NR' => __( 'Nauru', 'wpcode-location' ),
			'NP' => __( 'Nepal', 'wpcode-location' ),
			'NL' => __( 'Netherlands', 'wpcode-location' ),
			'NC' => __( 'New Caledonia', 'wpcode-location' ),
			'NZ' => __( 'New Zealand', 'wpcode-location' ),
			'NI' => __( 'Nicaragua', 'wpcode-location' ),
			'NE' => __( 'Niger', 'wpcode-location' ),
			'NG' => __( 'Nigeria', 'wpcode-location' ),
			'NU' => __( 'Niue', 'wpcode-location' ),
			'NF' => __( 'Norfolk Island', 'wpcode-location' ),
			'MP' => __( 'Northern Mariana Islands', 'wpcode-location' ),
			'KP' => __( 'North Korea', 'wpcode-location' ),
			'NO' => __( 'Norway', 'wpcode-location' ),
			'OM' => __( 'Oman', 'wpcode-location' ),
			'PK' => __( 'Pakistan', 'wpcode-location' ),
			'PS' => __( 'Palestinian Territory', 'wpcode-location' ),
			'PA' => __( 'Panama', 'wpcode-location' ),
			'PG' => __( 'Papua New Guinea', 'wpcode-location' ),
			'PY' => __( 'Paraguay', 'wpcode-location' ),
			'PE' => __( 'Peru', 'wpcode-location' ),
			'PH' => __( 'Philippines', 'wpcode-location' ),
			'PN' => __( 'Pitcairn', 'wpcode-location' ),
			'PL' => __( 'Poland', 'wpcode-location' ),
			'PT' => __( 'Portugal', 'wpcode-location' ),
			'PR' => __( 'Puerto Rico', 'wpcode-location' ),
			'QA' => __( 'Qatar', 'wpcode-location' ),
			'RE' => __( 'Reunion', 'wpcode-location' ),
			'RO' => __( 'Romania', 'wpcode-location' ),
			'RU' => __( 'Russia', 'wpcode-location' ),
			'RW' => __( 'Rwanda', 'wpcode-location' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'wpcode-location' ),
			'SH' => __( 'Saint Helena', 'wpcode-location' ),
			'KN' => __( 'Saint Kitts and Nevis', 'wpcode-location' ),
			'LC' => __( 'Saint Lucia', 'wpcode-location' ),
			'MF' => __( 'Saint Martin (French part)', 'wpcode-location' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'wpcode-location' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'wpcode-location' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'wpcode-location' ),
			'SM' => __( 'San Marino', 'wpcode-location' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'wpcode-location' ),
			'SA' => __( 'Saudi Arabia', 'wpcode-location' ),
			'SN' => __( 'Senegal', 'wpcode-location' ),
			'RS' => __( 'Serbia', 'wpcode-location' ),
			'SC' => __( 'Seychelles', 'wpcode-location' ),
			'SL' => __( 'Sierra Leone', 'wpcode-location' ),
			'SG' => __( 'Singapore', 'wpcode-location' ),
			'SK' => __( 'Slovakia', 'wpcode-location' ),
			'SI' => __( 'Slovenia', 'wpcode-location' ),
			'SB' => __( 'Solomon Islands', 'wpcode-location' ),
			'SO' => __( 'Somalia', 'wpcode-location' ),
			'ZA' => __( 'South Africa', 'wpcode-location' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'wpcode-location' ),
			'KR' => __( 'South Korea', 'wpcode-location' ),
			'SS' => __( 'South Sudan', 'wpcode-location' ),
			'ES' => __( 'Spain', 'wpcode-location' ),
			'LK' => __( 'Sri Lanka', 'wpcode-location' ),
			'SD' => __( 'Sudan', 'wpcode-location' ),
			'SR' => __( 'Suriname', 'wpcode-location' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'wpcode-location' ),
			'SZ' => __( 'Eswatini', 'wpcode-location' ),
			'SE' => __( 'Sweden', 'wpcode-location' ),
			'CH' => __( 'Switzerland', 'wpcode-location' ),
			'SY' => __( 'Syria', 'wpcode-location' ),
			'TW' => __( 'Taiwan', 'wpcode-location' ),
			'TJ' => __( 'Tajikistan', 'wpcode-location' ),
			'TZ' => __( 'Tanzania', 'wpcode-location' ),
			'TH' => __( 'Thailand', 'wpcode-location' ),
			'TL' => __( 'Timor-Leste', 'wpcode-location' ),
			'TG' => __( 'Togo', 'wpcode-location' ),
			'TK' => __( 'Tokelau', 'wpcode-location' ),
			'TO' => __( 'Tonga', 'wpcode-location' ),
			'TT' => __( 'Trinidad and Tobago', 'wpcode-location' ),
			'TN' => __( 'Tunisia', 'wpcode-location' ),
			'TR' => __( 'Turkey', 'wpcode-location' ),
			'TM' => __( 'Turkmenistan', 'wpcode-location' ),
			'TC' => __( 'Turks and Caicos Islands', 'wpcode-location' ),
			'TV' => __( 'Tuvalu', 'wpcode-location' ),
			'UG' => __( 'Uganda', 'wpcode-location' ),
			'UA' => __( 'Ukraine', 'wpcode-location' ),
			'AE' => __( 'United Arab Emirates', 'wpcode-location' ),
			'GB' => __( 'United Kingdom (UK)', 'wpcode-location' ),
			'US' => __( 'United States (US)', 'wpcode-location' ),
			'UM' => __( 'United States (US) Minor Outlying Islands', 'wpcode-location' ),
			'UY' => __( 'Uruguay', 'wpcode-location' ),
			'UZ' => __( 'Uzbekistan', 'wpcode-location' ),
			'VU' => __( 'Vanuatu', 'wpcode-location' ),
			'VA' => __( 'Vatican', 'wpcode-location' ),
			'VE' => __( 'Venezuela', 'wpcode-location' ),
			'VN' => __( 'Vietnam', 'wpcode-location' ),
			'VG' => __( 'Virgin Islands (British)', 'wpcode-location' ),
			'VI' => __( 'Virgin Islands (US)', 'wpcode-location' ),
			'WF' => __( 'Wallis and Futuna', 'wpcode-location' ),
			'EH' => __( 'Western Sahara', 'wpcode-location' ),
			'WS' => __( 'Samoa', 'wpcode-location' ),
			'YE' => __( 'Yemen', 'wpcode-location' ),
			'ZM' => __( 'Zambia', 'wpcode-location' ),
			'ZW' => __( 'Zimbabwe', 'wpcode-location' ),
		);
	}

	/**
	 * Get location information by ip address.
	 *
	 * @param string $ip The IP address for which we want location data.
	 *
	 * @return array
	 */
	public function get_location( $ip = '' ) {
		if ( empty( $ip ) ) {
			$ip = $this->get_ip();
		}

		if ( $this->is_local( $ip ) ) {
			return array();
		}

		foreach ( $this->get_sources() as $source ) {
			if ( ! is_string( $source ) ) {
				continue;
			}

			$data = $this->retrieve_body( $ip, $source );

			if ( ! empty( $data ) ) {
				return array_map( 'sanitize_text_field', $data );
			}
		}

		return array();
	}

	/**
	 * Is the ip address for a local host.
	 *
	 * @param string $ip IP Address.
	 *
	 * @return bool
	 */
	private function is_local( $ip = '' ) {
		return wpcode_is_local( $ip );
	}

	/**
	 * Get the list of sources for retrieving geolocation data.
	 *
	 * @return array
	 */
	private function get_sources() {

		return (array) apply_filters( 'wpcode_location_data_get_sources', array_keys( $this->sources ) );
	}

	/**
	 * Retrieve geolocation for a specific source by IP.
	 *
	 * @param string $ip IP Address.
	 * @param string $source A source name.
	 *
	 * @return array
	 */
	private function retrieve_body( $ip, $source ) {

		if ( ! empty( $this->sources[ $source ] ) ) {
			return $this->request( $source, $this->sources[ $source ], $ip );
		}

		/**
		 * Allow modifying location data for custom providers.
		 *
		 * @param array  $data The geolocation data.
		 * @param string $ip IP Address.
		 *
		 * @return array
		 */
		$data = (array) apply_filters( "wpcode_location_data_retrieve_body_{$source}", array(), $ip );

		if ( empty( $data['latitude'] ) || empty( $data['longitude'] ) ) {
			return array();
		}

		return wp_parse_args(
			$data,
			array(
				'latitude'  => '',
				'longitude' => '',
				'city'      => '',
				'region'    => '',
				'country'   => '',
				'postal'    => '',
			)
		);
	}

	/**
	 * Make the request to the source API to get the location data for an IP.
	 *
	 * @param string $source Source name.
	 * @param string $endpoint Endpoint.
	 * @param string $ip IP address.
	 *
	 * @return array
	 * @uses  wpcode_response, ipapi_response, keycdn_response
	 */
	private function request( $source, $endpoint, $ip ) {

		$endpoint = sprintf( $endpoint, $ip );
		$data     = array();

		/**
		 * Allow modifying request arguments.
		 *
		 * @param array  $args Request arguments.
		 * @param string $ip IP address.
		 *
		 * @return array
		 */
		$args = (array) apply_filters( "wpcode_location_data_request_{$source}_args", array(), $ip );

		$request = wp_remote_get( $endpoint, $args );

		if ( ! is_wp_error( $request ) ) {
			$request = json_decode( wp_remote_retrieve_body( $request ), true );
			$method  = $source . '_response';
			if ( method_exists( $this, $method ) ) {
				$data = $this->{$method}( $request );
			}
		}

		return $data;
	}

	/**
	 * Processing request from WPCode.
	 *
	 * @param array $request_body Request body.
	 *
	 * @return array
	 */
	private function wpcode_response( $request_body ) {

		if ( empty( $request_body['latitude'] ) || empty( $request_body['longitude'] ) ) {
			return array();
		}

		return array(
			'latitude'  => $request_body['latitude'],
			'longitude' => $request_body['longitude'],
			'city'      => ! empty( $request_body['city'] ) ? $request_body['city'] : '',
			'region'    => ! empty( $request_body['region_name'] ) ? $request_body['region_name'] : '',
			'country'   => ! empty( $request_body['country_iso'] ) ? $request_body['country_iso'] : '',
			'postal'    => ! empty( $request_body['zip_code'] ) ? $request_body['zip_code'] : '',
		);
	}

	/**
	 * Processing request from IpAPI.
	 *
	 * @param array $request_body Request body.
	 *
	 * @return array
	 */
	private function ipapi_response( $request_body ) {

		if ( empty( $request_body['latitude'] ) || empty( $request_body['longitude'] ) ) {
			return array();
		}

		return array(
			'latitude'  => $request_body['latitude'],
			'longitude' => $request_body['longitude'],
			'city'      => ! empty( $request_body['city'] ) ? $request_body['city'] : '',
			'region'    => ! empty( $request_body['region'] ) ? $request_body['region'] : '',
			'country'   => ! empty( $request_body['country'] ) ? $request_body['country'] : '',
			'postal'    => ! empty( $request_body['postal'] ) ? $request_body['postal'] : '',
		);
	}

	/**
	 * Processing request from KeyCDN.
	 *
	 * @param array $request_body Request body.
	 *
	 * @return array
	 */
	private function keycdn_response( $request_body ) {

		if ( empty( $request_body['data']['geo']['latitude'] ) || empty( $request_body['data']['geo']['longitude'] ) ) {
			return array();
		}

		return array(
			'latitude'  => $request_body['data']['geo']['latitude'],
			'longitude' => $request_body['data']['geo']['longitude'],
			'city'      => ! empty( $request_body['data']['geo']['city'] ) ? $request_body['data']['geo']['city'] : '',
			'region'    => ! empty( $request_body['data']['geo']['region_name'] ) ? $request_body['data']['geo']['region_name'] : '',
			'country'   => ! empty( $request_body['data']['geo']['country_code'] ) ? $request_body['data']['geo']['country_code'] : '',
			'postal'    => ! empty( $request_body['data']['geo']['postal_code'] ) ? $request_body['data']['geo']['postal_code'] : '',
		);
	}

	/**
	 * Get the user IP address.
	 *
	 * @return string
	 */
	public function get_ip() {
		return wpcode_get_user_ip();
	}

	/**
	 * Get the continent from a country code.
	 *
	 * @param string $country_code Country code.
	 *
	 * @return string
	 */
	public function get_continent( $country_code ) {
		$continent  = '';
		$countries  = $this->get_countries();
		$continents = $this->get_continents();

		if ( ! empty( $countries[ $country_code ] ) ) {
			foreach ( $continents as $code => $data ) {
				if ( in_array( $country_code, $data['countries'], true ) ) {
					$continent = $code;
					break;
				}
			}
		}

		return $continent;
	}
}
