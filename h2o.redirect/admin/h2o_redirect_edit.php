<?
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/h2o.redirect/admin/tools.php");

Loader::includeModule('h2o.redirect');
// подключим языковой файл
IncludeModuleLangFile(__FILE__);


global $DB;
// сформируем список закладок
$aTabs = array(
  array("DIV" => "edit1", "TAB" => GetMessage("H2O_REDIRECT_TAB_MAIN"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("H2O_REDIRECT_TAB_MAIN")),
  
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// идентификатор редактируемой записи
$message = null;		// сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //

if(
    $REQUEST_METHOD == "POST" // проверка метода вызова страницы
    &&
    ($save!="" || $apply!="") // проверка нажатия кнопок "Сохранить" и "Применить"
    &&
    check_bitrix_sessid()     // проверка идентификатора сессии
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
 
  
  // сохранение данных
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
    // если сохранение прошло удачно - перенаправим на новую страницу 
    // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
    if ($apply != "")
      // если была нажата кнопка "Применить" - отправляем обратно на форму.
  
      LocalRedirect("/bitrix/admin/h2o_redirect_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
    else
      // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
      LocalRedirect("/bitrix/admin/h2o_redirect_list.php?lang=".LANG);
  }
  else
  {
    // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
    if($e = $result->getErrorMessages())
      $message = new CAdminMessage(GetMessage("H2O_REDIRECT_ERROR").implode("; ",$e));
    $bVarsFromForm = true;
  }
}

// ******************************************************************** //
//                ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ                     //
// ******************************************************************** //


// выборка данных
if($ID>0)
{
	$res = \h2o\Redirect\RedirectTable::getById($ID);
  
  if(!$redirect_element = $res->fetch())
    $ID=0;
}


// если данные переданы из формы, инициализируем их
if($bVarsFromForm)
  $DB->InitTableVarsForEdit("b_list_redirect", "", "str_");

// ******************************************************************** //
//                ВЫВОД ФОРМЫ                                           //
// ******************************************************************** //

// установим заголовок страницы
$APPLICATION->SetTitle(($ID>0? GetMessage("H2O_REDIRECT_EDIT_TITLE").$ID : GetMessage("H2O_REDIRECT_ADD_TITLE")));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// конфигурация административного меню
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

// создание экземпляра класса административного меню
$context = new CAdminContextMenu($aMenu);

// вывод административного меню
$context->Show();
?>

<?
// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if($_REQUEST["mess"] == "ok" && $ID>0)
  CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("H2O_REDIRECT_SAVED"), "TYPE"=>"OK"));

if($message)
  echo $message->Show();
elseif($redirect_element->LAST_ERROR!="")
  CAdminMessage::ShowMessage($redirect_element->LAST_ERROR);
?>

<?
// далее выводим собственно форму
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" enctype="multipart/form-data" name="redirect_edit_form">
<?// проверка идентификатора сессии ?>
<?echo bitrix_sessid_post();?>
<?
// отобразим заголовки закладок
$tabControl->Begin();
CJSCore::Init(array('date'));
?>
<?
//********************
// первая закладка - форма редактирования параметров рассылки
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

// завершение формы - вывод кнопок сохранения изменений
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
// завершаем интерфейс закладок
$tabControl->End();
?>

<?
// дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
$tabControl->ShowWarnings("redirect_edit_form", $message);
?>


<?
// информационная подсказка
echo BeginNote();?>

<span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>