<?php


namespace h2o\Redirect;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;

Class CHORedirect
{
    /**
     * ����� ���������� ������� �������� ���� � �������
     * @param $aGlobalMenu
     * @param $aModuleMenu
     */
    public static function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
    {
        if($GLOBALS['APPLICATION']->GetGroupRight("main") < "R")
            return;

        $MODULE_ID = basename(dirname(__FILE__));
        $aMenu = array(
            "parent_menu" => "global_menu_content",
            "section" => $MODULE_ID,
            "sort" => 50,
            "text" => \h2o\Redirect\H2oRedirectTools::decodeUtf8(GetMessage('H2O_REDIRECT_TITLE')),
            "title" => \h2o\Redirect\H2oRedirectTools::decodeUtf8(GetMessage('H2O_REDIRECT_TITLE')),
            "icon" => "",
            "page_icon" => "",
            "items_id" => $MODULE_ID."_items",
            "more_url" => array(),
            "items" => array()
        );

        if (file_exists($path = dirname(__FILE__).'/../admin'))
        {
            if ($dir = opendir($path))
            {
                $arFiles = array("h2o_redirect_list.php");
                sort($arFiles);
                $arTitles = array(
                    'h2o_redirect_list.php' => \h2o\Redirect\H2oRedirectTools::decodeUtf8(GetMessage("H2O_REDIRECT_LIST")),

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

    /**
     * ����� ���������� ������� OnBeforeProlog
     */
    public static function onRedirect(){

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // ���� �������� � ������ AJAX-�������, �������� ����, ������ ��� ��� ���� �������� �������� �� �����
            return;
        }

        //������� ���� � ������� ����������
        $reqUri = Context::getCurrent()->getRequest()->getRequestUri();
        $reqUriArr = parse_url($reqUri);
        $uri = $reqUriArr['path'];
        $getParamStr = '';
        if(!empty($reqUriArr['query'])) {
            $getParamStr = $reqUriArr['query'];
        }

        $db_redirect = \h2o\Redirect\RedirectTable::getList(array(
            'filter' => array(
                'ACTIVE' => 'Y',
                'IS_REGEXP' => 'N',
                array(
                    'LOGIC' => 'OR',
                    '=REDIRECT_FROM' => array(\h2o\Redirect\H2oRedirectTools::getCurrentScheme() . '://'.$_SERVER['HTTP_HOST'].$uri,$uri)
                )
            ),'cache'  => [
                'ttl'         => 36000000,
                'cache_joins' => true,
            ]
        ));

        if($arRedirect = $db_redirect->fetch()){
            if($arRedirect['REDIRECT_TO'] != ""){
                $redirectTo = $arRedirect['REDIRECT_TO'];
                if(!empty($getParamStr)) {
                    $redirectTo .= "?".$getParamStr;
                }
                self::doRedirect($redirectTo, $arRedirect);
            }
        }else{
            //������� ����� � ����������
            $db_redirect = \h2o\Redirect\RedirectTable::getList(array(
                'filter' => array(
                    "ACTIVE" => "Y",
                    "IS_REGEXP" => "Y"
                ),'cache'  => [
                    'ttl'         => 36000000,
                    'cache_joins' => true,
                ]
            ));
            while($arRedirect = $db_redirect->fetch()){
                if($arRedirect['REDIRECT_TO'] != "") {
                    $uri=\h2o\Redirect\H2oRedirectTools::getCurrentScheme() . '://'.$_SERVER['HTTP_HOST'].$uri;
                    if (preg_match($arRedirect['REDIRECT_FROM'], $uri)) {
                        $redirectTo= preg_replace($arRedirect['REDIRECT_FROM'], $arRedirect['REDIRECT_TO'], $uri);
                        if(!empty($getParamStr)) {
                            $redirectTo .= "?".$getParamStr;
                        }
                        self::doRedirect($redirectTo, $arRedirect);
                        break;
                    }
                }
            }
        }

    }

    /**
     * ���������� ���������
     *
     * @param string      $url
     * @param bool|array  $arRedirect
     */
    protected static function doRedirect($url, $arRedirect = false){
        $status = Option::get('h2o.redirect', "status", '301'); //������ ���������
        $toTrackRedirect = Option::get('h2o.redirect', "to_track_redirect", 'Y'); //����� ���������� �� ����������
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
        if($toTrackRedirect == 'Y' && $arRedirect){
            \h2o\Redirect\RedirectTable::update($arRedirect['ID'], array(
                "COUNT_REDIRECT" => $arRedirect['COUNT_REDIRECT'] + 1
            ));
        }
        LocalRedirect($url, false, $status_string);
    }
}