<?php
namespace Crmgenesis\Slots;

class Bitrixfunction{

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

}