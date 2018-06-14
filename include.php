<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('h2o.redirect', array(
    // no thanks, bitrix, we better will use psr-4 than your class names convention
    'h2o\Redirect\RedirectTable' => 'lib/redirect.php',
    'h2o\Redirect\CHORedirect' => 'lib/CHORedirect.php',
    'h2o\Redirect\CIBlockPropertyUserID' => 'lib/tools.php',
    'h2o\Redirect\H2oRedirectTools' => 'lib/tools.php',
));

IncludeModuleLangFile(__FILE__);


?>
