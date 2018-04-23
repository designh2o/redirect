<?
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

// ��������� ��� ����������� �����:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // ������ ����� ������
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/h2o.redirect/admin/tools.php");

Loader::includeModule('h2o.redirect');
// ��������� �������� ����
IncludeModuleLangFile(__FILE__);


global $DB;
// ���������� ������ ��������
$aTabs = array(
  array("DIV" => "edit1", "TAB" => GetMessage("H2O_REDIRECT_TAB_MAIN"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("H2O_REDIRECT_TAB_MAIN")),
  
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// ������������� ������������� ������
$message = null;		// ��������� �� ������
$bVarsFromForm = false; // ���� "������ �������� � �����", ������������, ��� ��������� ������ �������� � �����, � �� �� ��.

// ******************************************************************** //
//                ��������� ��������� �����                             //
// ******************************************************************** //

if(
    $REQUEST_METHOD == "POST" // �������� ������ ������ ��������
    &&
    ($save!="" || $apply!="") // �������� ������� ������ "���������" � "���������"
    &&
    check_bitrix_sessid()     // �������� �������������� ������
)
{
  
  $arMap = \h2o\Redirect\RedirectTable::getMap();
  $arFields = array();
  foreach($arMap as $key => $field){
  	if(isset($_REQUEST[$key]) && $field['editable']){
  		$arFields[$key] = $_REQUEST[$key];
  	}elseif($field['data_type'] == 'boolean' && $field['editable']){
  		$arFields[$key] = "N";
  	}
  }
 
  
  // ���������� ������
  if($ID > 0)
  {
    $result = \h2o\Redirect\RedirectTable::update($ID, $arFields);
  }
  else
  {
    $result = \h2o\Redirect\RedirectTable::add($arFields);
    if($result->isSuccess()){
    	$ID = $result->getId();
    }
  }

  if($result->isSuccess())
  {
    // ���� ���������� ������ ������ - ������������ �� ����� �������� 
    // (� ����� ������ �� ��������� �������� ����� �������� ������ "��������" � ��������)
    if ($apply != "")
      // ���� ���� ������ ������ "���������" - ���������� ������� �� �����.
  
      LocalRedirect("/bitrix/admin/h2o_redirect_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
    else
      // ���� ���� ������ ������ "���������" - ���������� � ������ ���������.
      LocalRedirect("/bitrix/admin/h2o_redirect_list.php?lang=".LANG);
  }
  else
  {
    // ���� � �������� ���������� �������� ������ - �������� ����� ������ � ������ ��������������� ����������
    if($e = $result->getErrorMessages())
      $message = new CAdminMessage(GetMessage("H2O_REDIRECT_ERROR").implode("; ",$e));
    $bVarsFromForm = true;
  }
}

// ******************************************************************** //
//                ������� � ���������� ������ �����                     //
// ******************************************************************** //


// ������� ������
if($ID>0)
{
	$res = \h2o\Redirect\RedirectTable::getById($ID);
  
  if(!$redirect_element = $res->fetch())
    $ID=0;
}


// ���� ������ �������� �� �����, �������������� ��
if($bVarsFromForm)
  $DB->InitTableVarsForEdit("b_list_redirect", "", "str_");

// ******************************************************************** //
//                ����� �����                                           //
// ******************************************************************** //

// ��������� ��������� ��������
$APPLICATION->SetTitle(($ID>0? GetMessage("H2O_REDIRECT_EDIT_TITLE").$ID : GetMessage("H2O_REDIRECT_ADD_TITLE")));

// �� ������� ��������� ���������� ������ � �����
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// ������������ ����������������� ����
$aMenu = array(
  array(
    "TEXT"=>GetMessage("H2O_REDIRECT_LIST"),
    "TITLE"=>GetMessage("H2O_REDIRECT_LIST_TITLE"),
    "LINK"=>"h2o_redirect_list.php?lang=".LANG,
    "ICON"=>"btn_list",
  )
);
$toTrackRedirect = false;
if($ID>0)
{
	$toTrackRedirect = Option::get('h2o.redirect', "to_track_redirect", 'Y') == 'Y';
  $aMenu[] = array("SEPARATOR"=>"Y");
  $aMenu[] = array(
    "TEXT"=>GetMessage("H2O_REDIRECT_ADD"),
    "TITLE"=>GetMessage("H2O_REDIRECT_ADD"),
    "LINK"=>"h2o_redirect_edit.php?lang=".LANG,
    "ICON"=>"btn_new",
  );
  $aMenu[] = array(
    "TEXT"=>GetMessage("H2O_REDIRECT_DELETE"),
    "TITLE"=>GetMessage("H2O_REDIRECT_DELETE"),
    "LINK"=>"javascript:if(confirm('".GetMessage("H2O_REDIRECT_DELETE_CONF")."'))window.location='h2o_redirect_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
    "ICON"=>"btn_delete",
  );
  
}

// �������� ���������� ������ ����������������� ����
$context = new CAdminContextMenu($aMenu);

// ����� ����������������� ����
$context->Show();
?>

<?
// ���� ���� ��������� �� ������� ��� �� �������� ���������� - ������� ��.
if($_REQUEST["mess"] == "ok" && $ID>0)
  CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("H2O_REDIRECT_SAVED"), "TYPE"=>"OK"));

if($message)
  echo $message->Show();
elseif($redirect_element->LAST_ERROR!="")
  CAdminMessage::ShowMessage($redirect_element->LAST_ERROR);
?>

<?
// ����� ������� ���������� �����
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" enctype="multipart/form-data" name="redirect_edit_form">
<?// �������� �������������� ������ ?>
<?echo bitrix_sessid_post();?>
<?
// ��������� ��������� ��������
$tabControl->Begin();
CJSCore::Init(array('date'));
?>
<?
//********************
// ������ �������� - ����� �������������� ���������� ��������
//********************
$tabControl->BeginNextTab();

$arMap = \h2o\Redirect\RedirectTable::getMap();
foreach($arMap as $code => $field):
	if($field['hidden'] || $code == 'ID'){
		continue;
	}
	if($ID == 0 && !$field['editable']){
		continue;
	}
	if(!$toTrackRedirect && $code == 'COUNT_REDIRECT'){
		continue;
	}
?>
  <tr>
    <td width="40%">
    	<?if($field['required']):?>
			<span class="adm-required-field"><?echo $field['title']?>:</span>
		<?else:?>
			<?echo $field['title']?>:
		<?endif;?>
	</td>
    <td width="60%">
		<?if($field['editable']):?>
			<?switch($field['data_type']){
				case 'datetime':
					echo CAdminCalendar::CalendarDate($code, $redirect_element[$code]->toString(), 19, true);
					break;
				case 'boolean':
					?><input type="checkbox" name="<?=$code?>" value="Y"<?if($redirect_element[$code] == "Y") echo " checked"?>/>	<?
					break;	
				case 'integer':
				case 'text':
				case 'string':
					if($field['type_field'] == 'element'){
						\h2o\Redirect\H2oRedirectTools::ShowElementField($code,$field,array($redirect_element[$code]));
					}elseif($field['type_field'] == 'user'){
						print \h2o\Redirect\H2oRedirectTools::ShowUserField($code,$field,array("VALUE" => $redirect_element[$code]));
					}else{
						?><input type="text" name="<?=$code?>" value="<?=$redirect_element[$code]?>" style="width: 100%;" />	<?
					}
					break;
			}?>
				
			
			
		<?else:?>
			<?if(is_object($redirect_element[$code])):?>
				<?if(method_exists($redirect_element[$code],'toString')):?>
					<?=$redirect_element[$code]->toString();?>
				<?endif;?>
			<?else:?>
				<?=$redirect_element[$code]?>
			<?endif;?>
		<?endif;?>
		</td>
  </tr>
<?endforeach;?>

<?

// ���������� ����� - ����� ������ ���������� ���������
$tabControl->Buttons(
  array(
    "disabled"=>false,
    "back_url"=>"rubric_admin.php?lang=".LANG,
    
  )
);
?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
  <input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?
// ��������� ��������� ��������
$tabControl->End();
?>

<?
// �������������� ����������� �� ������� - ����� ������ ����� ����, � ������� �������� ������
$tabControl->ShowWarnings("redirect_edit_form", $message);
?>


<?
// �������������� ���������
echo BeginNote();?>

<span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?
// ���������� ��������
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>