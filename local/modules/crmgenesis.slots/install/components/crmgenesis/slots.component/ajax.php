<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//header('Content-type: application/json');

use Bitrix\Main\Loader;

Loader::includeSharewareModule("crmgenesis.slots");

//$data = (array)json_decode(file_get_contents("php://input")); - так не переводятся в array вложенные объекты STD
$data = json_decode(json_encode(json_decode(file_get_contents("php://input"))),true);


// 1.Запрос пользователей для фильтра (пока одного)
if($data['action'] == 'checkRoleAndGetFilters')
    Crmgenesis\Slots\Filter::checkRoleAndGetFilterValues();

// 2.запрос евентов календаря выбранного пользователя при загрузке страницы или смены значения в селекте пользователей
if($data['action'] == 'getEventsByFilter')
    Crmgenesis\Slots\Calendar::getEventsByFilter($data['filters']);



//Резервация нового слота
if($data['action'] == 'addEventToCalendar')
    Crmgenesis\Slots\Calendar::addEventToCalendar($data['filters']);