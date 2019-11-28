<?php
namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Calendar{

    /*
     * @method: Get Calendar Events By Filter: Start/End week date + selected user ID
     * @return: event array
     * */
    public function getCalendarEvents($filters){
        $result = [
            'errors' => [],
            'result' => [],
//            'filters' => $filters,
        ];

        $filter = [
            '>DATE_FROM' => date('d.m.Y',strtotime($filters['firstWeekDay'])),
            '<=DATE_TO' => date('d.m.Y',strtotime($filters['lastWeekDay'])),
            'USER_ID' => $filters['seletedUserId'],
        ];

        $recordArr = SlotsTable::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => ['ID' => 'ASC'],
        ]);

        while($event = $recordArr->fetch()){
            $colors = self::selectActivityColor($event['DATE_FROM']);
            $result['result'][] = [
                'id' => $event['ID'],
                'title' => 'Встреча #' . $event['USER_ID'],
                'start' => date('Y-m-d H:i:s', strtotime($event['DATE_FROM'])),
                'end' => date('Y-m-d H:i:s', strtotime($event['DATE_TO'])),
                'resourceId' => $event['USER_ID'],
                'color' => $colors['block'],
                'textColor' => $colors['text'],
                'editable' => false, //запрет редактирования записи
            ];
        }

        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method метод для сохранения выбранного рабочего времени каждого сотрудника
    * @return events by filter for cur user
    */
    public function addEventToCalendar($filters){
        $result = ['errors' => [],'result' => false];

        $addRes = Bitrixfunction::addSlot([
            'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['workDayStart'])),"d.m.Y H:i:s"),
            'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['workDayFinish'])),"d.m.Y H:i:s"),
            'USER_ID' => $filters['seletedUserId'],
        ]);

        ($addRes['result'])
            ? $result['result'] = $addRes['result']
            : $result['errors'] = $addRes['errors'];

        Bitrixfunction::sentAnswer($result);
    }


    /*@method: в зависимости от даты начала эвента меняем цвет
    @return: array, цвет блока + цвет текста*/
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
    }


}