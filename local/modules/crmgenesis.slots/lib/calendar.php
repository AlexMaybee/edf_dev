<?php
namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Calendar{


    //ЗАГОТОВКА метода для получения событий календаря для выбранного пользователя
    public function getSlotsActivity($userId,$from,$to){
        $result = [
            'errors' => [],
            'activities' => [],
        ];



        Bitrixfunction::sentAnswer($result);
    }

    //метод для сохранения выбранного рабочего времени каждого сотрудника
    public function addWorkHoursSlot($post){
        $result = ['errors' => [],'result' => false];

        $addRes = Bitrixfunction::addSlot([
            'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($post['dateFrom'])),"d.m.Y H:i:s"),
            'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($post['dateTo'])),"d.m.Y H:i:s"),
            'USER_ID' => $post['userID'],
        ]);

        ($addRes['result'])
            ? $result['result'] = $addRes['result']
            : $result['errors'] = $addRes['errors'];

        Bitrixfunction::sentAnswer($result);
    }

}