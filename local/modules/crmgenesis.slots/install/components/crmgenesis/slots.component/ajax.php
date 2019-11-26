<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//header('Content-type: application/json');

use Bitrix\Main\Loader;

Loader::includeSharewareModule("crmgenesis.slots");

$data = (array)json_decode(file_get_contents("php://input"));


//запрос евентов календаря при загрузке страницы или смены значения в селекте пользователей


//запрос пользователей для фильтра (пока одного)
if($data['action'] == 'checkRoleAndGetFilters')
    Crmgenesis\Slots\Filter::checkRoleAndGetFilterValues();

//reserveWorkHoursToSlot
if($data['action'] == 'reserveWorkHoursToSlot')
    Crmgenesis\Slots\Calendar::addWorkHoursSlot($data);