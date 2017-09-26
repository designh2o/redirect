<?
use Bitrix\Main\Loader;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/h2o.redirect/admin/tools.php");

Loader::includeModule('h2o.redirect');
//CModule::IncludeModule("h2o.redirect");
IncludeModuleLangFile(__FILE__);

$listTableId = "tbl_h2o_redirect_list";

$oSort = new CAdminSorting($listTableId, "ID", "asc");
$arOrder = (strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));

$adminList = new CAdminList($listTableId, $oSort);

// ******************************************************************** //
//                           ������                                     //
// ******************************************************************** //

// *********************** CheckFilter ******************************** //
// �������� �������� ������� ��� �������� ������� � ��������� �������
function CheckFilter()
{
  global $arFilterFields, $adminList;
  foreach ($arFilterFields as $f) global $$f;

  // � ������ ������ ��������� ������. 
  // � ����� ������ ����� ��������� �������� ���������� $find_���
  // � � ������ �������������� ������ ���������� �� ����������� 
  // ����������� $adminList->AddFilterError('�����_������').
  
  return count($adminList->arFilterErrors)==0; // ���� ������ ����, ������ false;
}
// *********************** /CheckFilter ******************************* //

// ������ �������� �������
$FilterArr = Array(
  "find_active",
  );
$arFilterFields = array(
	"find_active",
);
// �������������� ������
$adminList->InitFilter($arFilterFields);

// ���� ��� �������� ������� ���������, ���������� ���
if (CheckFilter())
{
  
  $arFilter = array();
	
	if (!empty($find_active))
		$arFilter["ACTIVE"] = $find_active;

}


// ******************************************************************** //
//                ��������� �������� ��� ���������� ������              //
// ******************************************************************** //

// ���������� ����������������� ���������
if($adminList->EditAction())
{
  // ������� �� ������ ���������� ���������
  foreach($FIELDS as $ID=>$arFields)
  {
    if(!$adminList->IsUpdated($ID))
      continue;
    
    // �������� ��������� ������� ��������
    $DB->StartTransaction();
    $ID = IntVal($ID);
    $res = \h2o\Redirect\RedirectTable::getById($ID);
	if(!$arData = $res->fetch()){
		foreach($arFields as $key=>$value)
        	$arData[$key]=$value;
 		$result = \h2o\Redirect\RedirectTable::update($ID, $arData);
 		
		if(!$result->isSuccess())
		{
			if($e = $result->getErrorMessages())
				$adminList->AddGroupError(GetMessage("REDIRECT_SAVE_ERROR")." ".$e, $ID);
			$DB->Rollback();
		}
	}
    else
    {
      $adminList->AddGroupError(GetMessage("REDIRECT_SAVE_ERROR")." ".GetMessage("REDIRECT_NO_ELEMENT"), $ID);
      $DB->Rollback();
    }
    $DB->Commit();
  }
}

// ��������� ��������� � ��������� ��������
if(($arID = $adminList->GroupAction()))
{
  // ���� ������� "��� ���� ���������"
  if($_REQUEST['action_target']=='selected')
  {
    $rsData = \h2o\Redirect\RedirectTable::getList(
		array(
			"filter" => $arFilter,
			'order' => array($by=>$order)
		)
	);
    while($arRes = $rsData->fetch())
      $arID[] = $arRes['ID'];
  }

  // ������� �� ������ ���������
  foreach($arID as $ID)
  {
    if(strlen($ID)<=0)
      continue;
       $ID = IntVal($ID);
    
    // ��� ������� �������� �������� ��������� ��������
    switch($_REQUEST['action'])
    {
    // ��������
    case "delete":
      @set_time_limit(0);
      $DB->StartTransaction();
      $result = \h2o\Redirect\RedirectTable::delete($ID);
	  if(!$result->isSuccess())
	  {
	      $DB->Rollback();
          $adminList->AddGroupError(GetMessage("REDIRECT_DELETE_ERROR"), $ID);
	  }
      $DB->Commit();
      break;
    
    // ���������/�����������
    case "activate":
    case "deactivate":
      
      if(($rsData = \h2o\Redirect\RedirectTable::getById($ID)) && ($arFields = $rsData->fetch()))
      {
        $arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
        $result = \h2o\Redirect\RedirectTable::update($ID, $arFields);
        if(!$result->isSuccess())
        	if($e = $result->getErrorMessages())
          		$adminList->AddGroupError(GetMessage("REDIRECT_SAVE_ERROR").$e, $ID);
      }
      else
        $adminList->AddGroupError(GetMessage("REDIRECT_SAVE_ERROR")." ".GetMessage("REDIRECT_NO_ELEMENT"), $ID);
      break;
    }
  }
}


$myData = \h2o\Redirect\RedirectTable::getList(
	array(
		'filter' => $arFilter,
		'order' => $arOrder
	)
);

$myData = new CAdminResult($myData, $listTableId);
$myData->NavStart();

$adminList->NavText($myData->GetNavPrint(GetMessage("H2O_REDIRECT_ADMIN_NAV")));

$cols = \h2o\Redirect\RedirectTable::getMap();
$colHeaders = array();
foreach ($cols as $colId => $col)
{
	if($col['hidden']){
		continue;
	}
	$colHeaders[] = array(
		"id" => $colId,
		"content" => $col["title"],
		"sort" => $colId,
		"default" => true,
	);
}
$adminList->AddHeaders($colHeaders);

$visibleHeaderColumns = $adminList->GetVisibleHeaderColumns();
$arUsersCache = array();
$arElementCache = array();
while ($arRes = $myData->GetNext())
{
	$row =& $adminList->AddRow($arRes["ID"], $arRes);

	if (in_array("ACTIVE", $visibleHeaderColumns)){
		$row->AddViewField("ACTIVE", $arRes['ACTIVE'] == 'Y'?GetMessage("H2O_REDIRECT_YES"):GetMessage("H2O_REDIRECT_NO"));
	}
	//$el_edit_url = htmlspecialcharsbx(\h2o\Redirect\H2oRedirectTools::GetAdminElementEditLink($arRes["ID"]));
	/*$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("H2O_REDIRECT_EDIT"),
		"ACTION" => $adminList->ActionRedirect($el_edit_url),
		"DEFAULT" => true,
	);*/
	$el_edit_url = htmlspecialcharsbx(\h2o\Redirect\H2oRedirectTools::GetAdminElementEditLink($arRes["ID"]));
	$arActions = array();
	$arActions[] = array("ICON" => "edit", "TEXT" => GetMessage("H2O_REDIRECT_EDIT"), "ACTION" => $adminList->ActionRedirect($el_edit_url), "DEFAULT" => true,);
	$arActions[] = array("ICON" => "delete", "TEXT" => GetMessage("H2O_REDIRECT_DELETE"), "ACTION" => "if(confirm('" . GetMessageJS("H2O_REDIRECT_DEL_CONF") . "')) " . $adminList->ActionDoGroup($arRes["ID"], "delete"),);
	$row->AddActions($arActions);
}



$adminList->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $myData->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

// ��������� ��������
$adminList->AddGroupActionTable(Array(
  "delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"), // ������� ��������� ��������
  "activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"), // ������������ ��������� ��������
  "deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // �������������� ��������� ��������
  ));

//���������� ������ �� ���������� �������� � �������
$aContext = array();

if (empty($aContext))
{
	$aContext[] = array(
			"ICON" => "btn_new",
			"TEXT" => GetMessage("H2O_REDIRECT_ADD"),
			"LINK" => h2o\Redirect\H2oRedirectTools::GetAdminElementEditLink(0) ,
			"LINK_PARAM" => "",
			"TITLE" => GetMessage("H2O_REDIRECT_ADD")
	);
}

$adminList->AddAdminContextMenu($aContext); //����������� ������������ ����
$adminList->CheckListMode();

$APPLICATION->SetTitle(GetMessage("H2O_REDIRECT_ADMIN_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<?
$adminList->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>