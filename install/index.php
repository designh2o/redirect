<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
IncludeModuleLangFile(__FILE__);
require_once(realpath(__DIR__ . '/../include.php'));
if (class_exists("h2o_redirect"))
	return;
use \h2o\Redirect\H2oRedirectTools;

Class h2o_redirect extends CModule
{
	const MODULE_ID = 'h2o.redirect';
	var $MODULE_ID = 'h2o.redirect'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = H2oRedirectTools::decodeUtf8(GetMessage("h2o.redirect_MODULE_NAME"));
		$this->MODULE_DESCRIPTION = H2oRedirectTools::decodeUtf8(GetMessage("h2o.redirect_MODULE_DESC"));

		$this->PARTNER_NAME = H2oRedirectTools::decodeUtf8(GetMessage("h2o.redirect_PARTNER_NAME"));
		$this->PARTNER_URI = H2oRedirectTools::decodeUtf8(GetMessage("h2o.redirect_PARTNER_URI"));
	}

	function InstallDB($arParams = array())
	{
		global $DB;
		RegisterModule(self::MODULE_ID);

       AddEventHandler("main", "OnBeforeProlog", "AjaxHandler", 1);
		/**
		 * Установка таблицы
		 */
		$DB->RunSQLBatch(dirname(__FILE__)."/sql/install.sql");

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB;
		UnRegisterModule(self::MODULE_ID);

		$DB->RunSQLBatch(dirname(__FILE__)."/sql/uninstall.sql");

		return true;
	}

	function InstallEvents()
	{
        /**
         * Создание глобального меню
         */
        RegisterModuleDependences('main', 'OnBeforeProlog', $this->MODULE_ID, 'h2o\Redirect\CHORedirect', 'onRedirect', 20);
        /**
         * Сам редирект
         */
        RegisterModuleDependences('main', 'OnBuildGlobalMenu', $this->MODULE_ID, 'h2o\Redirect\CHORedirect', 'OnBuildGlobalMenu', 20);
		return true;
	}

	function UnInstallEvents()
	{
        UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CHOredirect', 'OnBuildGlobalMenu');
        UnRegisterModuleDependences('main', 'OnBeforeProlog', self::MODULE_ID, 'CHOredirect', 'onRedirect');

        return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles(dirname(__FILE__)."/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles(dirname(__FILE__)."/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		return true;
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallEvents();
		$this->InstallDB();
	}

	function DoUninstall()
	{
		global $APPLICATION;
		
		$this->UnInstallEvents();
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
