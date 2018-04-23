<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'h2o.redirect');

global $USER, $APPLICATION;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

if (!$USER->isAdmin()) {
	$APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot() . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);
if(!Bitrix\Main\Loader::includeModule("iblock") || !Bitrix\Main\Loader::includeModule(ADMIN_MODULE_NAME)){
	CAdminMessage::showMessage(array(
		"MESSAGE" => Loc::getMessage("H2O_ERROR_MODULE"),
		"TYPE" => "ERROR",
	));
	return;
}
$tabControl = new CAdminTabControl("tabControl", array(
	array(
		"DIV" => "edit1",
		"TAB" => Loc::getMessage("MAIN_TAB_SET"),
		"TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
	),
));

if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
	if (!empty($restore)) {
		Option::delete(ADMIN_MODULE_NAME);
		CAdminMessage::showMessage(array(
			"MESSAGE" => Loc::getMessage("H2O_OPTIONS_RESTORED"),
			"TYPE" => "OK",
		));
	} elseif (
		$request->getPost('status')
	) {

		Option::set(
			ADMIN_MODULE_NAME,
			"status",
			$request->getPost('status')
		);
		Option::set(
			ADMIN_MODULE_NAME,
			"to_track_redirect",
			$request->getPost('to_track_redirect') == 'Y' ? "Y" : "N"
		);

		CAdminMessage::showMessage(array(
			"MESSAGE" => Loc::getMessage("H2O_OPTIONS_SAVED"),
			"TYPE" => "OK",
		));
	} else {
		CAdminMessage::showMessage(Loc::getMessage("H2O_INVALID_VALUE"));
	}
}

$tabControl->begin();
?>

<form method="post"
      action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">
	<?php
	echo bitrix_sessid_post();
	$tabControl->beginNextTab();
	?>
	<tr>
		<td width="40%">
			<label for="status"><?= Loc::getMessage("H2O_REDIRECT_STATUS") ?>:</label>
		<td width="60%">
			<select name="status" id="status">
				<option value="301" <?= (Option::get(ADMIN_MODULE_NAME, "status", '301') == '301') ? 'selected' : '' ?>><?= Loc::getMessage("H2O_REDIRECT_STATUS_301") ?></option>
				<option value="302" <?= (Option::get(ADMIN_MODULE_NAME, "status", '301') == '302') ? 'selected' : '' ?>><?= Loc::getMessage("H2O_REDIRECT_STATUS_302") ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="to_track_redirect"><?= Loc::getMessage("H2O_TO_TRACK_REDIRECT") ?>:</label>
		<td width="60%">
			<input
					type="checkbox"
					id="to_track_redirect"
					name="to_track_redirect"
					<?= (Option::get(ADMIN_MODULE_NAME, "to_track_redirect", 'Y') == 'Y') ? 'checked' : '' ?>
					value="Y">
		</td>
	</tr>
	<?php
	$tabControl->buttons();
	?>
	<input type="submit"
	       name="save"
	       value="<?= Loc::getMessage("MAIN_SAVE") ?>"
	       title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
	       class="adm-btn-save"
		/>
	<input type="submit"
	       name="restore"
	       title="<?= Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
	       onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
	       value="<?= Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
		/>
	<?php
	$tabControl->end();
	?>
</form>
