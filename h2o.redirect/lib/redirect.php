<?php
namespace h2o\Redirect;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class RedirectTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> REDIRECT_FROM string optional
 * <li> REDIRECT_TO string optional
 * </ul>
 *
 * @package Bitrix\Redirect
 **/

class RedirectTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'h2o_redirect';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('REDIRECT_ENTITY_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('REDIRECT_ENTITY_ACTIVE_FIELD'),
				'editable' => true
			),
			'REDIRECT_FROM' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REDIRECT_ENTITY_REDIRECT_FROM_FIELD'),
				'editable' => true
			),
			'REDIRECT_TO' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REDIRECT_ENTITY_REDIRECT_TO_FIELD'),
				'editable' => true
			),
		);
	}
}