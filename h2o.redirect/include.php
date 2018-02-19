<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;

Loader::registerAutoLoadClasses('h2o.redirect', array(
    // no thanks, bitrix, we better will use psr-4 than your class names convention
    'h2o\Redirect\RedirectTable' => 'lib/redirect.php',
));
IncludeModuleLangFile(__FILE__);

Class CHORedirect
{
	function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
	{
		if($GLOBALS['APPLICATION']->GetGroupRight("main") < "R")
			return;

		$MODULE_ID = basename(dirname(__FILE__));
		$aMenu = array(
			//"parent_menu" => "global_menu_services",
			"parent_menu" => "global_menu_content",
			"section" => $MODULE_ID,
			"sort" => 50,
			"text" => GetMessage('H2O_REDIRECT_TITLE'),
			"title" => GetMessage('H2O_REDIRECT_TITLE'),
//			"url" => "partner_modules.php?module=".$MODULE_ID,
			"icon" => "",
			"page_icon" => "",
			"items_id" => $MODULE_ID."_items",
			"more_url" => array(),
			"items" => array()
		);

		if (file_exists($path = dirname(__FILE__).'/admin'))
		{
			if ($dir = opendir($path))
			{
				$arFiles = array("h2o_redirect_list.php");


				
				sort($arFiles);
				$arTitles = array(
					'h2o_redirect_list.php' => GetMessage("H2O_REDIRECT_LIST"),
					
				);

				foreach($arFiles as $item)
					$aMenu['items'][] = array(
						'text' => $arTitles[$item],
						'url' => $item,
						'module_id' => $MODULE_ID,
						"title" => "",
					);
			}
		}
		$aModuleMenu[] = $aMenu;
	}

	function onRedirect(){
		global $APPLICATION;
		$cur_page = $APPLICATION->GetCurPageParam("",array(),false);
		$cur_page_index = $APPLICATION->GetCurPageParam("", array(), true);
		if(Bitrix\Main\Loader::includeModule('h2o.redirect')){
			$status = Option::get('h2o.redirect', "status", '301');
			switch($status){
				case 301:
					$status_string = "301 Moved permanently";
					break;
				case 302:
					$status_string = "302 Found";
					break;
				default:
					$status_string = "301 Moved permanently";
			}
			//сначала ищем в обычных редиректах
			$db_redirect = h2o\Redirect\RedirectTable::getList(array(
				'filter' => array(
					"ACTIVE" => "Y",
					"IS_REGEXP" => "N",
					array(
						"LOGIC" => "OR",
						array("=REDIRECT_FROM" => urldecode($cur_page)),
						array("=REDIRECT_FROM" => urldecode($cur_page_index)),
					)
				)
			));
			if($arRedirect = $db_redirect->fetch()){
				if($arRedirect['REDIRECT_TO'] != ""){
					LocalRedirect($arRedirect['REDIRECT_TO'], false, $status_string);
				}
			}else{
				//пробуем найти в регулярках
				$db_redirect = h2o\Redirect\RedirectTable::getList(array(
					'filter' => array(
						"ACTIVE" => "Y",
						"IS_REGEXP" => "Y"
					)
				));
				while($arRedirect = $db_redirect->fetch()){
					if($arRedirect['REDIRECT_TO'] != "") {
						if (preg_match($arRedirect['REDIRECT_FROM'], urldecode($cur_page))) {
							LocalRedirect(preg_replace($arRedirect['REDIRECT_FROM'], $arRedirect['REDIRECT_TO'], urldecode($cur_page)),
								false, $status_string);
							break;
						}
						if (preg_match($arRedirect['REDIRECT_FROM'], urldecode($cur_page_index))) {
							LocalRedirect(preg_replace($arRedirect['REDIRECT_FROM'], $arRedirect['REDIRECT_TO'], urldecode($cur_page_index)),
								false, $status_string);
							break;
						}
					}
				}
			}

		}
	}
}
?>
