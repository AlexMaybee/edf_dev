<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//use \Bitrix\Main;
//use \Bitrix\Main\Loader;
//use Bitrix\Main\UserTable;

$APPLICATION->SetTitle("Slot WorkDesc");
CJSCore::Init();


echo '<br><br><hr><br>';

$APPLICATION->IncludeComponent(
    "crmgenesis:slots.component",
    ".default",
    [],
    false
);