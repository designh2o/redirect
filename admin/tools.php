<?
namespace h2o\Redirect;
if(! \Bitrix\Main\Loader::includeModule ('iblock'))
{
	ShowError(H2oRedirectTools::decodeUtf8(GetMessage('IBLOCK_MODULE_NOT_INSTALLED')));
	return;
}
IncludeModuleLangFile(__FILE__);

class CIBlockPropertyUserID
{
	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => "S",
			"USER_TYPE" => "UserID",
			"DESCRIPTION" => H2oRedirectTools::decodeUtf8(GetMessage("IBLOCK_PROP_USERID_DESC")),
			"GetAdminListViewHTML" => array("CIBlockPropertyUserID","GetAdminListViewHTML"),
			"GetPropertyFieldHtml" => array("CIBlockPropertyUserID","GetPropertyFieldHtml"),
			"ConvertToDB" => array("CIBlockPropertyUserID","ConvertToDB"),
			"ConvertFromDB" => array("CIBlockPropertyUserID","ConvertFromDB"),
			"GetSettingsHTML" => array("CIBlockPropertyUserID","GetSettingsHTML"),
		);
	}
	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		$value = intVal($value["VALUE"]);
		if(!array_key_exists($value, $cache))
		{
			$rsUsers = CUser::GetList($by, $order, array("ID" => $value));
			$cache[$value] = $rsUsers->Fetch();
		}
		$arUser = $cache[$value];
		if($arUser)
		{
			return "[<a title='".H2oRedirectTools::decodeUtf8(GetMessage("MAIN_EDIT_USER_PROFILE"))."' href='user_edit.php?ID=".$arUser["ID"]."&lang=".LANG."'>".$arUser["ID"]."</a>] (".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
		}
		else
			return "&nbsp;";
	}
	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		global $USER;
		$default_value = intVal($value["VALUE"]);
		$res = "";
		if ($default_value == $USER->GetID())
		{
			$select = "CU";
			$res = "[<a title='".H2oRedirectTools::decodeUtf8(GetMessage("MAIN_EDIT_USER_PROFILE"))."'  href='/bitrix/admin/user_edit.php?ID=".$USER->GetID()."&lang=".LANG."'>".$USER->GetID()."</a>] (".htmlspecialcharsbx($USER->GetLogin()).") ".htmlspecialcharsbx($USER->GetFirstName())." ".htmlspecialcharsbx($USER->GetLastName());
		}
		elseif ($default_value > 0)
		{
			$select = "SU";
			$rsUsers = \CUser::GetList($by, $order, array("ID" => $default_value));
			if ($arUser = $rsUsers->Fetch())
				$res = "[<a title='".H2oRedirectTools::decodeUtf8(GetMessage("MAIN_EDIT_USER_PROFILE"))."'  href='/bitrix/admin/user_edit.php?ID=".$arUser["ID"]."&lang=".LANG."'>".$arUser["ID"]."</a>] (".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
			else
				$res = "&nbsp;".H2oRedirectTools::decodeUtf8(GetMessage("MAIN_NOT_FOUND"));
		}
		else
		{
			$select = "none";
			$default_value = "";
		}
		
		$name_x = preg_replace("/([^a-z0-9])/is", "x", $strHTMLControlName["VALUE"]);
		if (strLen(trim($strHTMLControlName["FORM_NAME"])) <= 0)
			$strHTMLControlName["FORM_NAME"] = "form_element";
		ob_start();
		?><select id="SELECT<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>" name="SELECT<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>" onchange="if(this.value == 'none')
						{
							var v=document.getElementById('<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>');
							v.value = '';
							v.readOnly = true;
							document.getElementById('FindUser<?=$name_x?>').disabled = true;
						}
						else
						{
							var v=document.getElementById('<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>');
							v.value = this.value == 'CU'?'<?=$USER->GetID()?>':'';
							v.readOnly = false;
							document.getElementById('FindUser<?=$name_x?>').disabled = false;
						}">
					<option value="none"<?if($select=="none")echo " selected"?>><?=H2oRedirectTools::decodeUtf8(GetMessage("IBLOCK_PROP_USERID_NONE"))?></option>
					<option value="CU"<?if($select=="CU")echo " selected"?>><?=H2oRedirectTools::decodeUtf8(GetMessage("IBLOCK_PROP_USERID_CURR"))?></option>
					<option value="SU"<?if($select=="SU")echo " selected"?>><?=H2oRedirectTools::decodeUtf8(GetMessage("IBLOCK_PROP_USERID_OTHR"))?></option>
				</select>&nbsp;
				<?echo self::FindUserIDNew(htmlspecialcharsbx($strHTMLControlName["VALUE"]), $value["VALUE"], $res, htmlspecialcharsEx($strHTMLControlName["FORM_NAME"]), $select);
			$return = ob_get_contents();
			ob_end_clean();
		return  $return;
	}
	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//array of error messages
	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//DB form of the value
	public static function ConvertToDB($arProperty, $value)
	{
		$value["VALUE"] = intval($value["VALUE"]);
		if($value["VALUE"] <= 0)
			$value["VALUE"] = "";
		return $value;
	}
	public static function ConvertFromDB($arProperty, $value)
	{
		$value["VALUE"] = intval($value["VALUE"]);
		if($value["VALUE"] <= 0)
			$value["VALUE"] = "";
		return $value;
	}
	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("WITH_DESCRIPTION"),
		);
		return '';
	}
	
	public static function FindUserIDNew($tag_name, $tag_value, $user_name="", $form_name = "form1", $select="none", $tag_size = "3", $tag_maxlength="", $button_value = "...", $tag_class="typeinput", $button_class="tablebodybutton", $search_page="/bitrix/admin/user_search.php")
	{
		global $APPLICATION, $USER;
		$tag_name_x = preg_replace("/([^a-z0-9])/is", "x", $tag_name);
		$tag_name_escaped = \CUtil::JSEscape($tag_name);
		
		if($APPLICATION->GetGroupRight("main") >= "R")
		{
			$strReturn = "
	<input type=\"text\" name=\"".$tag_name."\" id=\"".$tag_name."\" value=\"".($select=="none"?"":$tag_value)."\" size=\"".$tag_size."\" maxlength=\"".$tag_maxlength."\" class=\"".$tag_class."\">
	<IFRAME style=\"width:0px; height:0px; border: 0px\" src=\"javascript:void(0)\" name=\"hiddenframe".$tag_name."\" id=\"hiddenframe".$tag_name."\"></IFRAME>
	<input class=\"".$button_class."\" type=\"button\" name=\"FindUser".$tag_name_x."\" id=\"FindUser".$tag_name_x."\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=".$form_name."&FC=".$tag_name_escaped."', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"".$button_value."\" ".($select=="none"?"disabled":"").">
	<span id=\"div_".$tag_name."\">".$user_name."</span>
	<script>
	";
			if($user_name=="")
				$strReturn.= "var tv".$tag_name_x."='';\n";
			else
				$strReturn.= "var tv".$tag_name_x."='".\CUtil::JSEscape($tag_value)."';\n";
			$strReturn.= "
	function Ch".$tag_name_x."()
	{
		var DV_".$tag_name_x.";
		DV_".$tag_name_x." = document.getElementById(\"div_".$tag_name_escaped."\");
		if (!!DV_".$tag_name_x.")
		{
			if (
				document.".$form_name."
				&& document.".$form_name."['".$tag_name_escaped."']
				&& typeof tv".$tag_name_x." != 'undefined'
				&& tv".$tag_name_x." != document.".$form_name."['".$tag_name_escaped."'].value
			)
			{
				tv".$tag_name_x."=document.".$form_name."['".$tag_name_escaped."'].value;
				if (tv".$tag_name_x."!='')
				{
					DV_".$tag_name_x.".innerHTML = '<i>".H2oRedirectTools::decodeUtf8(GetMessage("MAIN_WAIT"))."</i>';
					if (tv".$tag_name_x."!=".intVal($USER->GetID()).")
					{
						document.getElementById(\"hiddenframe".$tag_name_escaped."\").src='/bitrix/admin/get_user.php?ID=' + tv".$tag_name_x."+'&strName=".$tag_name_escaped."&lang=".LANG.(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"")."';
						document.getElementById('SELECT".$tag_name_escaped."').value = 'SU';
					}
					else
					{
						DV_".$tag_name_x.".innerHTML = '".\CUtil::JSEscape("[<a title=\"".H2oRedirectTools::decodeUtf8(GetMessage("MAIN_EDIT_USER_PROFILE"))."\" class=\"tablebodylink\" href=\"/bitrix/admin/user_edit.php?ID=".$USER->GetID()."&lang=".LANG."\">".$USER->GetID()."</a>] (".htmlspecialcharsbx($USER->GetLogin()).") ".htmlspecialcharsbx($USER->GetFirstName())." ".htmlspecialcharsbx($USER->GetLastName()))."';
						document.getElementById('SELECT".$tag_name_escaped."').value = 'CU';
					}
				}
				else
				{
					DV_".$tag_name_x.".innerHTML = '';
					document.getElementById('SELECT".$tag_name_escaped."').value = 'SU';
				}
			}
			else if (
				DV_".$tag_name_x."
				&& DV_".$tag_name_x.".innerHTML.length > 0
				&& document.".$form_name."
				&& document.".$form_name."['".$tag_name_escaped."']
				&& document.".$form_name."['".$tag_name_escaped."'].value == ''
			)
			{
				document.getElementById('div_".$tag_name."').innerHTML = '';
			}
		}
		setTimeout(function(){Ch".$tag_name_x."()},1000);
	}
	Ch".$tag_name_x."();
	//-->
	</script>
	";
		}
		else
		{
			$strReturn = "
				<input type=\"text\" name=\"$tag_name\" id=\"$tag_name\" value=\"$tag_value\" size=\"$tag_size\" maxlength=\"strMaxLenght\">
				<input type=\"button\" name=\"FindUser".$tag_name_x."\" id=\"FindUser".$tag_name_x."\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=$form_name&FC=$tag_name_escaped', '', 'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"$button_value\">
				$user_name
				";
		}
		return $strReturn;
	}
}


class H2oRedirectTools{
	static public function ShowElementField($name, $property_fields, $values, $bVarsFromForm = false)
	{
		global $bCopy;
		$index = 0;
		$show = true;
	
		$MULTIPLE_CNT = intval($property_fields["MULTIPLE_CNT"]);
		if ($MULTIPLE_CNT <= 0 || $MULTIPLE_CNT > 30)
			$MULTIPLE_CNT = 5;
	
		$bInitDef = $bInitDef && (strlen($property_fields["DEFAULT_VALUE"]) > 0);
	
		$cnt = ($property_fields["MULTIPLE"] == "Y"? $MULTIPLE_CNT + ($bInitDef? 1: 0) : 1);
	
		if(!is_array($values))
			$values = array();
	
		$fixIBlock = $property_fields["LINK_IBLOCK_ID"] > 0;
	
		echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
		foreach ($values as $key=>$val)
		{
			$show = false;
			if ($bCopy)
			{
				$key = "n".$index;
				$index++;
			}
	
			if (is_array($val) && array_key_exists("VALUE", $val))
				$val = $val["VALUE"];
	
			$db_res = \CIBlockElement::GetByID($val);
			$ar_res = $db_res->GetNext();
			echo '<tr><td>'.
			'<input name="'.$name.'" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$ar_res["IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.($fixIBlock ? '&amp;iblockfix=y' : '').'\', 900, 700);">'.
			'&nbsp;<span id="sp_'.md5($name).'_'.$key.'" >'.$ar_res['NAME'].'</span>'.
			'</td></tr>';
	
			if ($property_fields["MULTIPLE"] != "Y")
			{
				$bVarsFromForm = true;
				break;
			}
		}
	
		if (!$bVarsFromForm || $show)
		{
			for ($i = 0; $i < $cnt; $i++)
			{
				$val = "";
				$key = "n".$index;
				$index++;
	
				echo '<tr><td>'.
				'<input name="'.$name.'['.$key.']" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
				'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.($fixIBlock ? '&amp;iblockfix=y' : '').'\', 900, 700);">'.
				'&nbsp;<span id="sp_'.md5($name).'_'.$key.'"></span>'.
				'</td></tr>';
			}
		}
	
		if($property_fields["MULTIPLE"]=="Y")
		{
			echo '<tr><td>'.
				'<input type="button" value="'.H2oRedirectTools::decodeUtf8(GetMessage("IBLOCK_AT_PROP_ADD")).'..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;m=y&amp;k='.$key.($fixIBlock ? '&amp;iblockfix=y' : '').'\', 900, 700);">'.
				'<span id="sp_'.md5($name).'_'.$key.'" ></span>'.
				'</td></tr>';
		}
	
		echo '</table>';
		echo '<script type="text/javascript">'."\r\n";
		echo "var MV_".md5($name)." = ".$index.";\r\n";
		echo "function InS".md5($name)."(id, name){ \r\n";
		echo "	oTbl=document.getElementById('tb".md5($name)."');\r\n";
		echo "	oRow=oTbl.insertRow(oTbl.rows.length-1); \r\n";
		echo "	oCell=oRow.insertCell(-1); \r\n";
		echo "	oCell.innerHTML=".
			"'<input name=\"".$name."[n'+MV_".md5($name)."+']\" value=\"'+id+'\" id=\"".$name."[n'+MV_".md5($name)."+']\" size=\"5\" type=\"text\">'+\r\n".
			"'<input type=\"button\" value=\"...\" '+\r\n".
			"'onClick=\"jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=".LANGUAGE_ID."&amp;IBLOCK_ID=".$property_fields["LINK_IBLOCK_ID"]."&amp;n=".$name."&amp;k=n'+MV_".md5($name)."+'".($fixIBlock ? '&amp;iblockfix=y' : '')."\', '+\r\n".
			"' 900, 700);\">'+".
			"'&nbsp;<span id=\"sp_".md5($name)."_'+MV_".md5($name)."+'\" >'+name+'</span>".
			"';";
		echo 'MV_'.md5($name).'++;';
		echo '}';
		echo "\r\n</script>";
	}
	
	static public function ShowUserField($name, $property_fields, $values, $form_name = "preorder_edit_form", $bCopy = false){
		return CIBlockPropertyUserID::GetPropertyFieldHtml(
			
				array(
					'ID' => 10101,
		            'CODE' => $name,
		            'PROPERTY_TYPE' => 'S',
		            'MULTIPLE' => 'N',
		            'USER_TYPE' => 'UserID',
				),
				$values,
				array(
					
			            'VALUE' => $name,//"PROP[100][][VALUE]",
			            'DESCRIPTION' => $name."[DESCRIPTION]",
			            'FORM_NAME' => $form_name,
			            'MODE' => 'FORM_FILL',
			            'COPY' => "" 
			        
				)
			
		);
		
		
	}
	
	public static function GetAdminElementEditLink($ELEMENT_ID, $arParams = array(), $strAdd = "")
    {
        
		$url = "h2o_redirect_edit.php";
		if($ELEMENT_ID !== null)
			$url.= "?ID=".intval($ELEMENT_ID);
		else
			return false;
		$url.= "&lang=".urlencode(LANGUAGE_ID);
		foreach ($arParams as $name => $value)
			if (isset($value))
				$url.= "&".urlencode($name)."=".urlencode($value);
		
		return $url.$strAdd;
    }

    /**
     * Декодирует строку из utf8 в кодировку сайта или другую, заданную явно через параметр. Если желаемая кодировка utf8, конвертация не произойдёт.
     * @param string|array $text
     * @param string $toEncoding
     * @return string|array
     */
    public static function decodeUtf8($text, $toEncoding = SITE_CHARSET)
    {
        if (strtolower($toEncoding) != 'utf-8') {
            if(is_array($text))
            {
                array_walk_recursive(
                    $text,
                    function (&$value, $key, $toEncoding){
                        $value = \iconv('utf-8', $toEncoding . "//IGNORE", $value);
                    },
                    $toEncoding
                );
                return $text;
            }else{
                return \iconv('utf-8', $toEncoding . "//IGNORE", $text);
            }
        } else {
            return $text;
        }
    }

    /**
     * Возвращает текущую схему запроса
     * @return mixed
     */
    public static function getCurrentScheme()
    {
        return $_SERVER['HTTP_X_FORWARDED_PROTO'] ?: $_SERVER['HTTP_X_FORWARDED_SCHEME'] ?: $_SERVER['REQUEST_SCHEME'];
    }
}