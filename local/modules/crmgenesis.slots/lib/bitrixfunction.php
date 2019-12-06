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
        $addResult = SlotsTable::add($fields);
        (!$addResult->isSuccess())
            ? $result['errors'] = $addResult->getErrorMessages()
            : $result['result'] = $addResult->getData();
        return $result;
    }

    public function deleteSlot($id){
        $result = ['result' => false,'errors' => []];
        $delResult = SlotsTable::delete($id);
        (!$delResult->isSuccess())
            ? $result['errors'] = $delResult->getErrorMessages()
            : $result['result'] = $delResult;
        return $result;
    }

    public function updateSlot($id,$updFields){
        $result = ['result' => false,'errors' => []];
        $updResult = SlotsTable::Update($id,$updFields);
        (!$updResult->isSuccess())
            ? $result['errors'] = $updResult->getErrorMessages()
            : $result['result'] = $updResult;
//        $entity = new SlotsTable(false);
//        $updSlot = $entity->update($id,$updFields);
//        ($updSlot)
//            ? $result['result'] = $updSlot
//            : $result['error'] = $entity->LAST_ERROR;
        return $result;
    }

    public function getSlotList($filter,$select,$order=[]){
        return $record = SlotsTable::getList([
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

}