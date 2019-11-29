<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//header('Content-type: application/json');

use Bitrix\Main\Loader;

Loader::includeSharewareModule("crmgenesis.slots");

//$data = (array)json_decode(file_get_contents("php://input")); - так не переводятся в array вложенные объекты STD
$data = json_decode(json_encode(json_decode(file_get_contents("php://input"))),true);


// # 1 Получение роли и ID пользователя
if($data['action'] == 'getUserRoleAndId')
    Crmgenesis\Slots\Filter::getUserRoleAndId();

// # 2 Получение списков значений в фильтры, если пользователь == админ
if($data['action'] == 'getDataForFilters')
    Crmgenesis\Slots\Filter::getDataForFilters();

// # 3 Получение рабочих дней календаря по фильтру
if($data['action'] == 'getUserSlots')
    Crmgenesis\Slots\Calendar::getUserSlots($data['filters']);

// # 4 Создание рабочего дня в календаре при подтверждении в Popup
if($data['action'] == 'addWorkPeriodToCalendar')
    Crmgenesis\Slots\Calendar::addWorkPeriodToCalendar($data['filters']);

// #5 Удаление рабочего дня из Popup
if($data['action'] == 'deleteSlot')
    Crmgenesis\Slots\Calendar::deleteSlotFromCalendar($data['filters']);