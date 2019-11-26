<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//header('Content-type: application/json');

use Bitrix\Main\Loader;

Loader::includeSharewareModule("crmgenesis.slots");


//запрос пользователей для фильтра (пока одного)
if($_POST['action'] == 'getValuesListForFilters')
    Crmgenesis\Slots\Filter::getFilterValues();

