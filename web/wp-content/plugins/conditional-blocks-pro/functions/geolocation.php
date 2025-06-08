<?php

/**
 * Countries list.
 * 
 * @return array
 */
function conditional_blocks_get_countries() {
	return [ 
		"AF" => "Afghanistan",
		"AX" => "Åland Islands",
		"AL" => "Albania",
		"DZ" => "Algeria",
		"AS" => "American Samoa",
		"AD" => "Andorra",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AQ" => "Antarctica",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BD" => "Bangladesh",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"PW" => "Belau",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BQ" => "Bonaire, Saint Eustatius and Saba",
		"BA" => "Bosnia and Herzegovina",
		"BW" => "Botswana",
		"BV" => "Bouvet Island",
		"BR" => "Brazil",
		"IO" => "British Indian Ocean Territory",
		"BN" => "Brunei",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"KH" => "Cambodia",
		"CM" => "Cameroon",
		"CA" => "Canada",
		"CV" => "Cape Verde",
		"KY" => "Cayman Islands",
		"CF" => "Central African Republic",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CX" => "Christmas Island",
		"CC" => "Cocos (Keeling) Islands",
		"CO" => "Colombia",
		"KM" => "Comoros",
		"CG" => "Congo (Brazzaville)",
		"CD" => "Congo (Kinshasa)",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"HR" => "Croatia",
		"CU" => "Cuba",
		"CW" => "Cura&ccedil;ao",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"DK" => "Denmark",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"EC" => "Ecuador",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"GQ" => "Equatorial Guinea",
		"ER" => "Eritrea",
		"EE" => "Estonia",
		"SZ" => "Eswatini",
		"ET" => "Ethiopia",
		"FK" => "Falkland Islands",
		"FO" => "Faroe Islands",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GF" => "French Guiana",
		"PF" => "French Polynesia",
		"TF" => "French Southern Territories",
		"GA" => "Gabon",
		"GM" => "Gambia",
		"GE" => "Georgia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GU" => "Guam",
		"GT" => "Guatemala",
		"GG" => "Guernsey",
		"GN" => "Guinea",
		"GW" => "Guinea-Bissau",
		"GY" => "Guyana",
		"HT" => "Haiti",
		"HM" => "Heard Island and McDonald Islands",
		"HN" => "Honduras",
		"HK" => "Hong Kong",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IR" => "Iran",
		"IQ" => "Iraq",
		"IE" => "Ireland",
		"IM" => "Isle of Man",
		"IL" => "Israel",
		"IT" => "Italy",
		"CI" => "Ivory Coast",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JE" => "Jersey",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Laos",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LS" => "Lesotho",
		"LR" => "Liberia",
		"LY" => "Libya",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macao",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"MX" => "Mexico",
		"FM" => "Micronesia",
		"MD" => "Moldova",
		"MC" => "Monaco",
		"MN" => "Mongolia",
		"ME" => "Montenegro",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MZ" => "Mozambique",
		"MM" => "Myanmar",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NU" => "Niue",
		"NF" => "Norfolk Island",
		"KP" => "North Korea",
		"MK" => "North Macedonia",
		"MP" => "Northern Mariana Islands",
		"NO" => "Norway",
		"OM" => "Oman",
		"PK" => "Pakistan",
		"PS" => "Palestinian Territory",
		"PA" => "Panama",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PN" => "Pitcairn",
		"PL" => "Poland",
		"PT" => "Portugal",
		"PR" => "Puerto Rico",
		"QA" => "Qatar",
		"RE" => "Reunion",
		"RO" => "Romania",
		"RU" => "Russia",
		"RW" => "Rwanda",
		"ST" => "S&atilde;o Tom&eacute; and Pr&iacute;ncipe",
		"BL" => "Saint Barth&eacute;lemy",
		"SH" => "Saint Helena",
		"KN" => "Saint Kitts and Nevis",
		"LC" => "Saint Lucia",
		"SX" => "Saint Martin (Dutch part)",
		"MF" => "Saint Martin (French part)",
		"PM" => "Saint Pierre and Miquelon",
		"VC" => "Saint Vincent and the Grenadines",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"RS" => "Serbia",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"ZA" => "South Africa",
		"GS" => "South Georgia/Sandwich Islands",
		"KR" => "South Korea",
		"SS" => "South Sudan",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"SD" => "Sudan",
		"SR" => "Suriname",
		"SJ" => "Svalbard and Jan Mayen",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"SY" => "Syria",
		"TW" => "Taiwan",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania",
		"TH" => "Thailand",
		"TL" => "Timor-Leste",
		"TG" => "Togo",
		"TK" => "Tokelau",
		"TO" => "Tonga",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos Islands",
		"TV" => "Tuvalu",
		"UG" => "Uganda",
		"UA" => "Ukraine",
		"AE" => "United Arab Emirates",
		"GB" => "United Kingdom (UK)",
		"US" => "United States (US)",
		"UM" => "United States (US) Minor Outlying Islands",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"VU" => "Vanuatu",
		"VA" => "Vatican",
		"VE" => "Venezuela",
		"VN" => "Vietnam",
		"VG" => "Virgin Islands (British)",
		"VI" => "Virgin Islands (US)",
		"WF" => "Wallis and Futuna",
		"EH" => "Western Sahara",
		"YE" => "Yemen",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe"
	];
}


function conditional_blocks_get_continents() {
	return [ 
		"AF" => "Africa",
		"AN" => "Antarctica",
		"AS" => "Asia",
		"EU" => "Europe",
		"NA" => "North America",
		"OC" => "Oceania",
		"SA" => "South America"
	];
}

/**
 * Get country to continent mapping
 * 
 * @return array
 */
function conditional_blocks_get_country_to_continent_map() {
	$country_to_continent = [ 
		'AF' => 'AS',
		'AX' => 'EU',
		'AL' => 'EU',
		'DZ' => 'AF',
		'AS' => 'OC',
		'AD' => 'EU',
		'AO' => 'AF',
		'AI' => 'NA',
		'AQ' => 'AN',
		'AG' => 'NA',
		'AR' => 'SA',
		'AM' => 'AS',
		'AW' => 'NA',
		'AU' => 'OC',
		'AT' => 'EU',
		'AZ' => 'AS',
		'BS' => 'NA',
		'BH' => 'AS',
		'BD' => 'AS',
		'BB' => 'NA',
		'BY' => 'EU',
		'BE' => 'EU',
		'BZ' => 'NA',
		'BJ' => 'AF',
		'BM' => 'NA',
		'BT' => 'AS',
		'BO' => 'SA',
		'BA' => 'EU',
		'BW' => 'AF',
		'BV' => 'AN',
		'BR' => 'SA',
		'IO' => 'AS',
		'BN' => 'AS',
		'BG' => 'EU',
		'BF' => 'AF',
		'BI' => 'AF',
		'KH' => 'AS',
		'CM' => 'AF',
		'CA' => 'NA',
		'CV' => 'AF',
		'KY' => 'NA',
		'CF' => 'AF',
		'TD' => 'AF',
		'CL' => 'SA',
		'CN' => 'AS',
		'CX' => 'AS',
		'CC' => 'AS',
		'CO' => 'SA',
		'KM' => 'AF',
		'CG' => 'AF',
		'CD' => 'AF',
		'CK' => 'OC',
		'CR' => 'NA',
		'CI' => 'AF',
		'HR' => 'EU',
		'CU' => 'NA',
		'CY' => 'EU',
		'CZ' => 'EU',
		'DK' => 'EU',
		'DJ' => 'AF',
		'DM' => 'NA',
		'DO' => 'NA',
		'EC' => 'SA',
		'EG' => 'AF',
		'SV' => 'NA',
		'GQ' => 'AF',
		'ER' => 'AF',
		'EE' => 'EU',
		'ET' => 'AF',
		'FK' => 'SA',
		'FO' => 'EU',
		'FJ' => 'OC',
		'FI' => 'EU',
		'FR' => 'EU',
		'GF' => 'SA',
		'PF' => 'OC',
		'TF' => 'AN',
		'GA' => 'AF',
		'GM' => 'AF',
		'GE' => 'AS',
		'DE' => 'EU',
		'GH' => 'AF',
		'GI' => 'EU',
		'GR' => 'EU',
		'GL' => 'NA',
		'GD' => 'NA',
		'GP' => 'NA',
		'GU' => 'OC',
		'GT' => 'NA',
		'GG' => 'EU',
		'GN' => 'AF',
		'GW' => 'AF',
		'GY' => 'SA',
		'HT' => 'NA',
		'HM' => 'AN',
		'VA' => 'EU',
		'HN' => 'NA',
		'HK' => 'AS',
		'HU' => 'EU',
		'IS' => 'EU',
		'IN' => 'AS',
		'ID' => 'AS',
		'IR' => 'AS',
		'IQ' => 'AS',
		'IE' => 'EU',
		'IM' => 'EU',
		'IL' => 'AS',
		'IT' => 'EU',
		'JM' => 'NA',
		'JP' => 'AS',
		'JE' => 'EU',
		'JO' => 'AS',
		'KZ' => 'AS',
		'KE' => 'AF',
		'KI' => 'OC',
		'KP' => 'AS',
		'KR' => 'AS',
		'KW' => 'AS',
		'KG' => 'AS',
		'LA' => 'AS',
		'LV' => 'EU',
		'LB' => 'AS',
		'LS' => 'AF',
		'LR' => 'AF',
		'LY' => 'AF',
		'LI' => 'EU',
		'LT' => 'EU',
		'LU' => 'EU',
		'MO' => 'AS',
		'MK' => 'EU',
		'MG' => 'AF',
		'MW' => 'AF',
		'MY' => 'AS',
		'MV' => 'AS',
		'ML' => 'AF',
		'MT' => 'EU',
		'MH' => 'OC',
		'MQ' => 'NA',
		'MR' => 'AF',
		'MU' => 'AF',
		'YT' => 'AF',
		'MX' => 'NA',
		'FM' => 'OC',
		'MD' => 'EU',
		'MC' => 'EU',
		'MN' => 'AS',
		'ME' => 'EU',
		'PA' => 'NA',
		'PG' => 'OC',
		'PY' => 'SA',
		'PE' => 'SA',
		'PH' => 'AS',
		'PN' => 'OC',
		'PL' => 'EU',
		'PT' => 'EU',
		'PR' => 'NA',
		'QA' => 'AS',
		'RE' => 'AF',
		'RO' => 'EU',
		'RU' => 'AS',
		'RW' => 'AF',
		'BL' => 'NA',
		'SH' => 'AF',
		'KN' => 'NA',
		'LC' => 'NA',
		'MF' => 'NA',
		'PM' => 'NA',
		'VC' => 'NA',
		'WS' => 'OC',
		'SM' => 'EU',
		'ST' => 'AF',
		'SA' => 'AS',
		'SN' => 'AF',
		'RS' => 'EU',
		'SC' => 'AF',
		'SL' => 'AF',
		'SG' => 'AS',
		'SK' => 'EU',
		'SI' => 'EU',
		'SB' => 'OC',
		'SO' => 'AF',
		'ZA' => 'AF',
		'GS' => 'AN',
		'SS' => 'AF',
		'ES' => 'EU',
		'LK' => 'AS',
		'SD' => 'AF',
		'SR' => 'SA',
		'SJ' => 'EU',
		'SZ' => 'AF',
		'SE' => 'EU',
		'CH' => 'EU',
		'SY' => 'AS',
		'TW' => 'AS',
		'TJ' => 'AS',
		'TZ' => 'AF',
		'TH' => 'AS',
		'TL' => 'AS',
		'TG' => 'AF',
		'TK' => 'OC',
		'TO' => 'OC',
		'TT' => 'NA',
		'TN' => 'AF',
		'TR' => 'AS',
		'TM' => 'AS',
		'TC' => 'NA',
		'TV' => 'OC',
		'UG' => 'AF',
		'UA' => 'EU',
		'AE' => 'AS',
		'GB' => 'EU',
		'US' => 'NA',
		'UM' => 'OC',
		'UY' => 'SA',
		'UZ' => 'AS',
		'VU' => 'OC',
		'VE' => 'SA',
		'VN' => 'AS',
		'VG' => 'NA',
		'VI' => 'NA',
		'WF' => 'OC',
		'EH' => 'AF',
		'YE' => 'AS',
		'ZM' => 'AF',
		'ZW' => 'AF',
		'PW' => 'OC',
		'BQ' => 'NA',
		'CW' => 'NA',
		'SX' => 'NA',
		'NR' => 'OC',
		'NP' => 'AS',
		'NL' => 'EU',
	];

	return apply_filters( 'conditional_blocks_country_to_continent_map', $country_to_continent );
}

/**
 * Get continent code for a given country code
 * 
 * @param string $country_code Two-letter country code
 * @return string|null Continent code or null if not found
 */
function conditional_blocks_get_continent_by_country( $country_code ) {
	$country_to_continent = conditional_blocks_get_country_to_continent_map();

	// Convert country code to uppercase to ensure consistent matching
	$country_code = strtoupper( $country_code );

	return isset( $country_to_continent[ $country_code ] ) ? $country_to_continent[ $country_code ] : null;
}

/**
 * Get geolocation data from IPInfo API
 * 
 * @param string $user_ip IP address to lookup
 * @return array|false Geolocation data array or false on failure
 */
function conditional_blocks_get_ipinfo_data( $user_ip ) {
	// Likely localhost or other non-public IPs - prevent API call
	if ( empty( $user_ip ) ) {
		return false;
	}

	$api_token = get_option( 'conditional_blocks_ipinfo_api_key', false );

	if ( empty( $api_token ) ) {
		return false;
	}

	$args = array(
		'headers' => array(
			'Authorization' => 'Bearer ' . $api_token,
			'Referer' => home_url(),
		),
		'timeout' => 15,
	);

	$response = wp_remote_get( "https://ipinfo.io/{$user_ip}", $args );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	return ! empty( $data ) ? $data : false;
}

/**
 * Get user's country from geolocation data
 * 
 * @return string|false Country code or false on failure
 */
function conditional_blocks_get_user_country() {
	static $cached_country = null;

	// Return cached result if available
	if ( $cached_country !== null ) {
		return $cached_country;
	}

	// Check for simulation if enabled
	if ( defined( 'CONDITIONAL_BLOCKS_GEOLOCATION_SIMULATION' ) && ! empty( $_GET['cb_country'] ) ) {
		$cached_country = sanitize_text_field( wp_unslash( $_GET['cb_country'] ) );
		return $cached_country;
	}

	// Get real geolocation data
	$user_ip = conditional_blocks_get_ip_address();
	$geo_data = conditional_blocks_get_ipinfo_data( $user_ip );

	if ( ! empty( $geo_data['country'] ) ) {
		$cached_country = $geo_data['country'];
		return $cached_country;
	}

	return false;
}

/**
 * Get user's continent from geolocation data
 * 
 * @return string|false Continent code or false on failure
 */
function conditional_blocks_get_user_continent() {
	static $cached_continent = null;

	// Return cached result if available
	if ( $cached_continent !== null ) {
		return $cached_continent;
	}

	// Check for simulation if enabled
	if ( defined( 'CONDITIONAL_BLOCKS_GEOLOCATION_SIMULATION' ) && ! empty( $_GET['cb_continent'] ) ) {
		$cached_continent = sanitize_text_field( wp_unslash( $_GET['cb_continent'] ) );
		return $cached_continent;
	}

	// Get country first
	$country = conditional_blocks_get_user_country();

	if ( $country ) {
		$cached_continent = conditional_blocks_get_continent_by_country( $country );
		return $cached_continent;
	}

	return false;
}