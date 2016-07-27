<?php
/*
 * @package		Joomla! Volunteers
 * @copyright   Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

class VolunteersHelperSelect
{
	protected static function genericlist($list, $name, $attribs, $selected, $idTag)
	{
		if(empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			foreach($attribs as $key=>$value)
			{
				$temp .= $key.' = "'.$value.'"';
			}
			$attribs = $temp;
		}

		return JHTML::_('select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	public static function booleanlist( $name, $attribs = null, $selected = null )
	{
		$options = array(
			JHTML::_('select.option','','---'),
			JHTML::_('select.option',  '0', JText::_( 'No' ) ),
			JHTML::_('select.option',  '1', JText::_( 'Yes' ) )
		);
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function published($selected = null, $id = 'enabled', $attribs = array('class' => 'chosen'))
	{
		$options = array();
		$options[] = JHTML::_('select.option',null,JText::_('COM_FUNXVOTES_SELECT_STATE'));
		$options[] = JHTML::_('select.option',0,JText::_((version_compare(JVERSION, '1.6.0', 'ge')?'J':'').'UNPUBLISHED'));
		$options[] = JHTML::_('select.option',1,JText::_((version_compare(JVERSION, '1.6.0', 'ge')?'J':'').'PUBLISHED'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function status($selected = null, $name = 'status', $attribs = array('class' => 'chosen'))
	{
		$status = JComponentHelper::getParams('com_volunteers')->get('statusoptions');
		$status = explode("\n", $status);
		foreach($status as $state) {
			$list[] = explode("=", $state);
		}

		$options = array();
		$options[] = JHTML::_('select.option','',JText::_('COM_FUNXVOTES_SELECT_STATUS'));
		foreach($list as $item) {
			$options[] = JHTML::_('select.option',$item[0],$item[1]);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Drop down list of groups
	 */
	public static function group($selected = null, $id = 'volunteers_group_id', $attribs = array('class' => 'chosen'))
	{
		$items = FOFModel::getTmpInstance('Groups','VolunteersModel')
			->savestate(0)
			->filter_order('title')
			->filter_order_Dir('ASC')
			->limit(0)
			->limitstart(0)
			->getList();

		$options = array();

		$options[] = JHTML::_('select.option','',JText::_('COM_VOLUNTEERS_SELECT_GROUP'));

		if(count($items)) foreach($items as $item)
		{
			$options[] = JHTML::_('select.option',$item->volunteers_group_id, $item->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	/**
	 * Drop down list of groups
	 */
	public static function volunteer($selected = null, $group = 0, $id = 'volunteers_volunteer_id', $attribs = array('class' => 'chosen'))
	{
		$items = FOFModel::getTmpInstance('Volunteers','VolunteersModel')
			->savestate(0)
			->filter_order('firstname')
			->filter_order_Dir('ASC')
			->limit(0)
			->limitstart(0)
			->getList();

		// If list for group
		if($group)
		{
			$groupmembers = FOFModel::getTmpInstance('Groupmembers', 'VolunteersModel')
				->group($group)
				->limit(0)
				->limitstart(0)
				->getList();
		}

		// Store current group members in array
		$currentmembers = array();
		if(!$selected)
		{
			if(count($groupmembers)) foreach($groupmembers as $groupmember)
			{
				$currentmembers[] = $groupmember->volunteers_volunteer_id;
			}
		}

		$options = array();

		$options[] = JHTML::_('select.option','',JText::_('COM_VOLUNTEERS_SELECT_VOLUNTEER'));

		if(count($items)) foreach($items as $item)
		{
			if(!in_array($item->volunteers_volunteer_id, $currentmembers))
			{
				$options[] = JHTML::_('select.option',$item->volunteers_volunteer_id, $item->firstname.' '.$item->lastname);
			}
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function role($selected = null, $id = 'role', $attribs = array('class' => 'chosen'))
	{
		$options = array();
		$options[] = JHTML::_('select.option',0,JText::_('COM_VOLUNTEERS_SELECT_ROLE'));
		$options[] = JHTML::_('select.option',1,JText::_('COM_VOLUNTEERS_ROLE_MEMBER'));
		$options[] = JHTML::_('select.option',2,JText::_('COM_VOLUNTEERS_ROLE_LEAD'));
		$options[] = JHTML::_('select.option',3,JText::_('COM_VOLUNTEERS_ROLE_LIAISON_CLT'));
		$options[] = JHTML::_('select.option',4,JText::_('COM_VOLUNTEERS_ROLE_LIAISON_PLT'));
		$options[] = JHTML::_('select.option',5,JText::_('COM_VOLUNTEERS_ROLE_LIAISON_OSM'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function ownership($selected = null, $id = 'ownership', $attribs = array('class' => 'chosen'))
	{
		$items = FOFModel::getTmpInstance('Groups','VolunteersModel')
			->filter_order('title')
			->filter_order_Dir('ASC')
			->ownership(1)
			->limit(0)
			->offset(0)
			->getList();

		$options   = array();

		$options[] = JHTML::_('select.option','0',JText::_('COM_VOLUNTEERS_SELECT_OWNERSHIP'));

		foreach($items as $item) {
			$options[] = JHTML::_('select.option',$item->volunteers_group_id,$item->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function countries($selected = null, $id = 'country', $attribs = array('class' => 'chosen'))
	{
		$items = self::$countries;
		asort($items);

		$options   = array();

		$options[] = JHTML::_('select.option','0',JText::_('COM_VOLUNTEERS_SELECT_COUNTRY'));

		foreach($items as $iso => $item) {
			$options[] = JHTML::_('select.option',$iso,$item);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static $countries = array(
		'AD' =>'Andorra', 'AE' =>'United Arab Emirates', 'AF' =>'Afghanistan',
		'AG' =>'Antigua and Barbuda', 'AI' =>'Anguilla', 'AL' =>'Albania',
		'AM' =>'Armenia', 'AO' =>'Angola',
		'AQ' =>'Antarctica', 'AR' =>'Argentina', 'AS' =>'American Samoa',
		'AT' =>'Austria', 'AU' =>'Australia', 'AW' =>'Aruba',
		'AX' =>'Aland Islands', 'AZ' =>'Azerbaijan', 'BA' =>'Bosnia and Herzegovina',
		'BB' =>'Barbados', 'BD' =>'Bangladesh',	'BE' =>'Belgium',
		'BF' =>'Burkina Faso', 'BG' =>'Bulgaria', 'BH' =>'Bahrain',
		'BI' =>'Burundi', 'BJ' =>'Benin', 'BL' =>'Saint Barthélemy',
		'BM' =>'Bermuda', 'BN' =>'Brunei Darussalam', 'BO' =>'Bolivia, Plurinational State of',
		'BQ' => 'Bonaire, Saint Eustatius and Saba',
		'BR' =>'Brazil', 'BS' =>'Bahamas', 'BT' =>'Bhutan', 'BV' =>'Bouvet Island',
		'BW' =>'Botswana', 'BY' =>'Belarus', 'BZ' =>'Belize', 'CA' =>'Canada',
		'CC' =>'Cocos (Keeling) Islands', 'CD' =>'Congo, the Democratic Republic of the',
		'CF' =>'Central African Republic', 'CG' =>'Congo', 'CH' =>'Switzerland',
		'CI' =>'Cote d\'Ivoire', 'CK' =>'Cook Islands', 'CL' =>'Chile',
		'CM' =>'Cameroon', 'CN' =>'China', 'CO' =>'Colombia', 'CR' =>'Costa Rica',
		'CU' =>'Cuba', 'CV' =>'Cape Verde', 'CW' => 'Curaçao', 'CX' =>'Christmas Island', 'CY' =>'Cyprus',
		'CZ' =>'Czech Republic', 'DE' =>'Germany', 'DJ' =>'Djibouti', 'DK' =>'Denmark',
		'DM' =>'Dominica', 'DO' =>'Dominican Republic', 'DZ' =>'Algeria',
		'EC' =>'Ecuador', 'EE' =>'Estonia', 'EG' =>'Egypt', 'EH' =>'Western Sahara',
		'ER' =>'Eritrea', 'ES' =>'Spain', 'ET' =>'Ethiopia', 'FI' =>'Finland',
		'FJ' =>'Fiji', 'FK' =>'Falkland Islands (Malvinas)', 'FM' =>'Micronesia, Federated States of',
		'FO' =>'Faroe Islands', 'FR' =>'France', 'GA' =>'Gabon', 'GB' =>'United Kingdom',
		'GD' =>'Grenada', 'GE' =>'Georgia', 'GF' =>'French Guiana', 'GG' =>'Guernsey',
		'GH' =>'Ghana', 'GI' =>'Gibraltar', 'GL' =>'Greenland', 'GM' =>'Gambia',
		'GN' =>'Guinea', 'GP' =>'Guadeloupe', 'GQ' =>'Equatorial Guinea', 'GR' =>'Greece',
		'GS' =>'South Georgia and the South Sandwich Islands', 'GT' =>'Guatemala',
		'GU' =>'Guam', 'GW' =>'Guinea-Bissau', 'GY' =>'Guyana', 'HK' =>'Hong Kong',
		'HM' =>'Heard Island and McDonald Islands', 'HN' =>'Honduras', 'HR' =>'Croatia',
		'HT' =>'Haiti', 'HU' =>'Hungary', 'ID' =>'Indonesia', 'IE' =>'Ireland',
		'IL' =>'Israel', 'IM' =>'Isle of Man', 'IN' =>'India', 'IO' =>'British Indian Ocean Territory',
		'IQ' =>'Iraq', 'IR' =>'Iran, Islamic Republic of', 'IS' =>'Iceland',
		'IT' =>'Italy', 'JE' =>'Jersey', 'JM' =>'Jamaica', 'JO' =>'Jordan',
		'JP' =>'Japan', 'KE' =>'Kenya', 'KG' =>'Kyrgyzstan', 'KH' =>'Cambodia',
		'KI' =>'Kiribati', 'KM' =>'Comoros', 'KN' =>'Saint Kitts and Nevis',
		'KP' =>'Korea, Democratic People\'s Republic of', 'KR' =>'Korea, Republic of',
		'KW' =>'Kuwait', 'KY' =>'Cayman Islands', 'KZ' =>'Kazakhstan',
		'LA' =>'Lao People\'s Democratic Republic', 'LB' =>'Lebanon',
		'LC' =>'Saint Lucia', 'LI' =>'Liechtenstein', 'LK' =>'Sri Lanka',
		'LR' =>'Liberia', 'LS' =>'Lesotho', 'LT' =>'Lithuania', 'LU' =>'Luxembourg',
		'LV' =>'Latvia', 'LY' =>'Libyan Arab Jamahiriya', 'MA' =>'Morocco',
		'MC' =>'Monaco', 'MD' =>'Moldova, Republic of', 'ME' =>'Montenegro',
		'MF' =>'Saint Martin (French part)', 'MG' =>'Madagascar', 'MH' =>'Marshall Islands',
		'MK' =>'Macedonia, the former Yugoslav Republic of', 'ML' =>'Mali',
		'MM' =>'Myanmar', 'MN' =>'Mongolia', 'MO' =>'Macao', 'MP' =>'Northern Mariana Islands',
		'MQ' =>'Martinique', 'MR' =>'Mauritania', 'MS' =>'Montserrat', 'MT' =>'Malta',
		'MU' =>'Mauritius', 'MV' =>'Maldives', 'MW' =>'Malawi', 'MX' =>'Mexico',
		'MY' =>'Malaysia', 'MZ' =>'Mozambique', 'NA' =>'Namibia', 'NC' =>'New Caledonia',
		'NE' =>'Niger', 'NF' =>'Norfolk Island', 'NG' =>'Nigeria', 'NI' =>'Nicaragua',
		'NL' =>'Netherlands', 'NO' =>'Norway', 'NP' =>'Nepal', 'NR' =>'Nauru', 'NU' =>'Niue',
		'NZ' =>'New Zealand', 'OM' =>'Oman', 'PA' =>'Panama', 'PE' =>'Peru', 'PF' =>'French Polynesia',
		'PG' =>'Papua New Guinea', 'PH' =>'Philippines', 'PK' =>'Pakistan', 'PL' =>'Poland',
		'PM' =>'Saint Pierre and Miquelon', 'PN' =>'Pitcairn', 'PR' =>'Puerto Rico',
		'PS' =>'Palestinian Territory, Occupied', 'PT' =>'Portugal', 'PW' =>'Palau',
		'PY' =>'Paraguay', 'QA' =>'Qatar', 'RE' =>'Reunion', 'RO' =>'Romania',
		'RS' =>'Serbia', 'RU' =>'Russian Federation', 'RW' =>'Rwanda', 'SA' =>'Saudi Arabia',
		'SB' =>'Solomon Islands', 'SC' =>'Seychelles', 'SD' =>'Sudan', 'SE' =>'Sweden',
		'SG' =>'Singapore', 'SH' =>'Saint Helena, Ascension and Tristan da Cunha',
		'SI' =>'Slovenia', 'SJ' =>'Svalbard and Jan Mayen', 'SK' =>'Slovakia',
		'SL' =>'Sierra Leone', 'SM' =>'San Marino', 'SN' =>'Senegal', 'SO' =>'Somalia',
		'SR' =>'Suriname', 'ST' =>'Sao Tome and Principe', 'SV' =>'El Salvador', 'SX' => 'Sint Maarten',
		'SY' =>'Syrian Arab Republic', 'SZ' =>'Swaziland', 'TC' =>'Turks and Caicos Islands',
		'TD' =>'Chad', 'TF' =>'French Southern Territories', 'TG' =>'Togo',
		'TH' =>'Thailand', 'TJ' =>'Tajikistan', 'TK' =>'Tokelau', 'TL' =>'Timor-Leste',
		'TM' =>'Turkmenistan', 'TN' =>'Tunisia', 'TO' =>'Tonga', 'TR' =>'Turkey',
		'TT' =>'Trinidad and Tobago', 'TV' =>'Tuvalu', 'TW' =>'Taiwan',
		'TZ' =>'Tanzania, United Republic of', 'UA' =>'Ukraine', 'UG' =>'Uganda',
		'UM' =>'United States Minor Outlying Islands', 'US' =>'United States',
		'UY' =>'Uruguay', 'UZ' =>'Uzbekistan', 'VA' =>'Holy See (Vatican City State)',
		'VC' =>'Saint Vincent and the Grenadines', 'VE' =>'Venezuela, Bolivarian Republic of',
		'VG' =>'Virgin Islands, British', 'VI' =>'Virgin Islands, U.S.', 'VN' =>'Viet Nam',
		'VU' =>'Vanuatu', 'WF' =>'Wallis and Futuna', 'WS' =>'Samoa', 'YE' =>'Yemen',
		'YT' =>'Mayotte', 'ZA' =>'South Africa', 'ZM' =>'Zambia', 'ZW' =>'Zimbabwe'
	);
}