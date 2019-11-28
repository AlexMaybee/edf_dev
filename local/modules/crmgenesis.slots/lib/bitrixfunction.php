<?php
namespace Crmgenesis\Slots;

use Crmgenesis\Slots\SlotsTable;

class Bitrixfunction{

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

}