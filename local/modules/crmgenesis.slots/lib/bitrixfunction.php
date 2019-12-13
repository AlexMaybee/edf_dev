<?php
namespace Crmgenesis\Slots;

use Crmgenesis\Slots\SlotsTable;

class Bitrixfunction{

    const MODULE_ID = 'crmgenesis.slots';

    public function checkUserIfAdmin(){
        return $GLOBALS['USER']->IsAdmin();
    }

    public function returnCurUserId(){
        return $GLOBALS['USER']->getId();
    }

    public function logData($data){
        $file = $_SERVER["DOCUMENT_ROOT"].'/zzz.log';
        file_put_contents($file, print_r([date('d.m.Y H:i:s'),$data],true), FILE_APPEND | LOCK_EX);
    }

    //ответ в консоль
    public function sentAnswer($answ){
        echo json_encode($answ);
    }

    //пользователи по фильтру
    public function getUsersByFilter($filter,$select){
        return $record = \Bitrix\Main\UserTable::getList([
            'select' => $select,
            'filter' => $filter,
        ])->fetchAll();
    }

    //Моя таблица Slots
    public function addSlot($fields){
        $result = ['result' => false,'errors' => []];
        $addResult = SlotTable::add($fields);
        (!$addResult->isSuccess())
            ? $result['errors'] = $addResult->getErrorMessages()
//            : $result['result'] = $addResult->getData();
            : $result['result'] = $addResult->getId();
        return $result;
    }

    public function deleteSlot($id){
        $result = ['result' => false,'errors' => []];
        $delResult = SlotTable::delete($id);
        (!$delResult->isSuccess())
            ? $result['errors'] = $delResult->getErrorMessages()
            : $result['result'] = $delResult;
        return $result;
    }

    public function updateSlot($id,$updFields){
        $result = ['result' => false,'errors' => []];
        $updResult = SlotTable::Update($id,$updFields);
        (!$updResult->isSuccess())
            ? $result['errors'] = $updResult->getErrorMessages()
            : $result['result'] = $updResult;
        return $result;
    }

    public function getSlotList($filter,$select,$order=[]){
        return $record = SlotTable::getList([
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ])->fetchAll();
    }

    //Моя таблица Slot_business
    public function addSlotBusiness($fields){
        $result = ['result' => false,'errors' => []];
        $addResult = Slot_businessTable::add($fields);
        (!$addResult->isSuccess())
            ? $result['errors'] = $addResult->getErrorMessages()
            : $result['result'] = $addResult->getId();
        return $result;
    }

    public function deleteSlotBusiness($id){
        $result = ['result' => false,'errors' => []];
        $delResult = Slot_businessTable::delete($id);
        (!$delResult->isSuccess())
            ? $result['errors'] = $delResult->getErrorMessages()
            : $result['result'] = $delResult;
        return $result;
    }

    public function updateSlotBusiness($id,$updFields){
        $result = ['result' => false,'errors' => []];
        $updResult = Slot_businessTable::Update($id,$updFields);
        (!$updResult->isSuccess())
            ? $result['errors'] = $updResult->getErrorMessages()
            : $result['result'] = $updResult;
        return $result;
    }

    public function getSlotBusinessList($filter,$select,$order=[]){
        return $record = Slot_businessTable::getList([
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ])->fetchAll();
    }

    public function returnDiffBetweenDatesInCurFormat($curD,$diffD,$form){
        $curDate = new \DateTime($curD); //Сравниваемая дата (текущая) Сюда нужно передавать дату для сравнения DateTime('2017-06-22').
        $diffDate = new \DateTime($diffD);
        $difference = $curDate->diff($diffDate);
        return $difference->format($form);
    }

    public function getCoptionValue($cOption){
        return  \COption::GetOptionInt(self::MODULE_ID, $cOption);
    }




    //***************************************************************************************************

    //ХУЙНЯ, НЕ ОТДАЕТ СВОЙСТВА, но используется!!!
    public function getListElements($filter,$select,$order=[]){
        return $records = \Bitrix\Iblock\ElementTable::getList([
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ])->fetchAll();
    }

    //ХУЙНЯ, НЕ ОТДАЕТ СВОЙСТВА, НЕ ИСПОЛЬЗУЕТСЯ!!!
    public function getListElementsWithProperties($filter,$select,$order=[]){
        $res = [];
        $dbItems = \Bitrix\Iblock\ElementTable::getList(array(
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ));
        while ($arItem = $dbItems->fetch()){
            \CModule::IncludeModule("iblock");
            $dbProperty = \CIBlockElement::getProperty(
                $arItem['IBLOCK_ID'],
                $arItem['ID']
            );
            while($arProperty = $dbProperty->Fetch()){
                $arItem['PROPERTIES'][$arProperty['CODE']] = $arProperty;
            }
            $res[] = $arItem;
        }
        return $res;
    }

    //РАБОЧИЙ, НО СТАРЫЙ!!!
    public function getListElemsOld($filter,$select,$order){
        $result = [];
        $arr = \CIBlockElement::GetList($order,$filter,false,false,$select);
        while($ob = $arr->getNext())
            $result[] = $ob;
        return $result;
    }

    //***************************************************************************************************



    //получение № дня по дате
    public function getDayWeek($date_custom){
        return  $day_week = date('N', strtotime($date_custom));
//        return $day_week;
    }

    //получение данных польз. групп
    public function getListGroups($filter,$select,$order=[]){
        return $result = \Bitrix\Main\GroupTable::getList([
            'select'  => $select,
            'filter'  => $filter, //array('!ID'=>'1', 'ACTIVE' => 'Y'),
            'order'  => $order,
        ])->fetchAll();
    }

    //получение групп пользователя
    public function getUserGroups($filter,$select,$order=[]){
        return $result = \Bitrix\Main\UserGroupTable::getList([
            'select'  => $select,
            'filter'  => $filter, //array('!ID'=>'1', 'ACTIVE' => 'Y'),
            'order'  => $order,
        ])->fetchAll();
    }

    //получение контактов
    public function getContactsList($filter,$select,$order=[],$limit=''){
        return $record = \Bitrix\Crm\ContactTable::getList([
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
            'limit' => $limit,
        ])->fetchAll();
    }

    //получение 1го контакта
    public function getContactData($filter,$select){
        return $record = \Bitrix\Crm\ContactTable::getList([
            'select' => $select,
            'filter' => $filter,
        ])->fetch();
    }





    /*функции для файла option.php*/

    //получение массива списков для файла option.php
    public function getListsArrForOptionsPhp(){
        $result = [];
        $rsIblock = \Bitrix\Iblock\IblockTable::getList(['select' => ['ID', 'NAME']]);
        while($arIblock = $rsIblock->fetch())
            $result[$arIblock['ID']] = $arIblock['NAME'].' ('.$arIblock['ID'].')';
        return $result;
    }

    //получение элементов выбранного списка для поля "Статус по умолчанию"
    public function getDefaultStatusFromListForOptionPhp(){
        $result = [];
        $statusListID = self::getCoptionValue('SLOT_STATUS_LIST');
        if($statusListID > 0){
            $defList = self::getListElements(['IBLOCK_ID' => $statusListID],['ID','NAME'],['DATE_CREATE' => 'DESC']);
            if($defList)
                foreach ($defList as $list)
                    $result[$list['ID']] = $list['NAME'].' ('.$list['ID'].')';
        }
        return $result;
    }

    //получение списка значений типов (индивид., групп., сплит)
    public function getTypeIdValList(){
        $result = [];
        $typeIdListID = self::getCoptionValue('SLOT_TYPE_LIST');
        if($typeIdListID > 0){
            $typeIdList = self::getListElements(['IBLOCK_ID' => $typeIdListID],['ID','NAME'],['DATE_CREATE' => 'DESC']);
            if($typeIdList)
                foreach ($typeIdList as $list)
                    $result[$list['ID']] = $list['NAME'].' ('.$list['ID'].')';
        }

        return $result;
    }
    
    //получение массива польз. групп
    public function getGroupsArrFroOprionPhp(){
        $result = [];
        $groupArr = self::getListGroups(['!ID'=>'1', 'ACTIVE' => 'Y'],['*'],['ID' => 'ASC']);
        if($groupArr)
            foreach ($groupArr as $group)
                $result[$group['ID']] = $group['NAME'].' ('.$group['ID'].')';
        return $result;
    }



}