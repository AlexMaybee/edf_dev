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

// # 2 Получение списков значений в фильтры, если пользователь == админ (над календарем))
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

// #6 Копирование слотов предыдущей недели на текущую
if($data['action'] == 'copyPreviousWeekSlots')
    Crmgenesis\Slots\Calendar::copyPreviousWeekSlots($data['filters']);

// #7 Получение списков значений полей popup Gsp
if($data['action'] == 'getGspModalSelectFields')
    Crmgenesis\Slots\Filter::getGspModalSelectFields($data['filters']);

// #8 Сохранение данных в ранее пустом но созданном слоте
if($data['action'] == 'updateSlot')
    Crmgenesis\Slots\Calendar::updateSlotInCalendar($data['filters'],$data['slotId'],$data['workDayStart'],$data['workDayFinish']);

// #9 Получение данных слота при открытии gsp Modal
if($data['action'] == 'getSlotById')
    Crmgenesis\Slots\Calendar::getSlotInCalendar($data['filters']);

// #10 Создание слотов + данных во второй таблице по чекбоксам за период из gsp Modal
if($data['action'] == 'addFilledSlotsBetweenPeriod')
    Crmgenesis\Slots\Calendar::addFilledSlotsBetweenPeriod($data['filters'],$data['checkboxes']);

// #11 Drag слота (смена дат перетаскиванием)
if($data['action'] == 'changeDateByDragNDrop')
    Crmgenesis\Slots\Calendar::changeDateByDragNDrop($data['slotId'],$data['workDayStart'],$data['workDayFinish']);

// #12 Поиск контактов по имени/телефону
if($data['action'] == 'getContactsByNameOrPhone')
    Crmgenesis\Slots\Filter::getContactsByNameOrPhone($data['filters']);
