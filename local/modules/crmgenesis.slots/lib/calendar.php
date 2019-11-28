<?php
namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Calendar{

    /*
     * @method ЗАГОТОВКА метода для получения событий календаря для выбранного пользователя
     * @return events by filter for cur user
     */
    public function getEventsByFilter($filters){
        $result = [
            'errors' => [],
            'result' => [],//date('Y-m-d',strtotime($filters['firstWeekDay'].' +1 week')),
            'test' => $filters,
        ];

        $eventsList = Bitrixfunction::getSlotList(
            [
//                '>DATE_FROM' => date('d.m.Y',strtotime($filters['firstWeekDay'])),
                '>DATE_FROM' => date('d.m.Y',strtotime($filters['firstWeekDay'])),
                '<=DATE_TO' => date('d.m.Y',strtotime($filters['firstWeekDay'].' next Sunday')),
                'USER_ID' => $filters['curUserId'],
            ],
            ['*']
        );

        if($eventsList)
            foreach ($eventsList as $event){

                //цвет блока дня в зависимости от даты
                $colors = self::selectActivityColor($event['DATE_FROM']);

                $result['result'][] = [
                    'id' => $event['ID'],
                    'title' => 'Встреча #' . $event['TITLE'],
                    'start' => date('Y-m-d H:i:s', strtotime($event['DATE_FROM'])),
                    'end' => date('Y-m-d H:i:s', strtotime($event['DATE_TO'])),
                    'resourceId' => $event['USER_ID'],
                    'color' => $colors['block'],
                    'textColor' => $colors['text'],
                ];
            }
//            $result['result'] = $eventsList;

        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method метод для сохранения выбранного рабочего времени каждого сотрудника
    * @return events by filter for cur user
    */
    public function addEventToCalendar($filters){
        $result = ['errors' => [],'result' => false];

        $addRes = Bitrixfunction::addSlot([
            'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['slotDateFrom'])),"d.m.Y H:i:s"),
            'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['slotDateTo'])),"d.m.Y H:i:s"),
            'USER_ID' => $filters['curUserId'],
        ]);

        ($addRes['result'])
            ? $result['result'] = $addRes['result']
            : $result['errors'] = $addRes['errors'];

        Bitrixfunction::sentAnswer($result);
    }


    private function selectActivityColor($dateStart){
        $diffRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
            date ('d.m.Y'), date('d.m.Y',strtotime($dateStart)),'%R%a');
        switch (true){
            case ($diffRes < 0): //прошлый день
                $color = [
                    'block' => '#000',
                    'text' => '#fff'
                ];
                break;
            case ($diffRes > 0): //будущий день
                $color = [
                    'block' => '#007bff',
                    'text' => '#fff'
                ];
                break;
            default:   //текущий день
                $color = [
                    'block' => '#d00000',
                    'text' => '#fff'
                    ];
                break;
        }
        return $color;
//        return $diffRes;
    }


}