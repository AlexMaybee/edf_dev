<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Page\Asset,
    \Bitrix\Main\Localization\Loc;


//Штатная библиотека
if(!\CJSCore::Init(["jquery2"]))
    \CJSCore::Init(["jquery2"]);


//JS
Asset::getInstance()->addJs("//ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js",true);
Asset::getInstance()->addJs("//cdn.jsdelivr.net/npm/vue/dist/vue.js",true);
Asset::getInstance()->addJs("//cdnjs.cloudflare.com/ajax/libs/moment.js/2.16.0/moment.min.js",true);
Asset::getInstance()->addJs("//momentjs.com/downloads/moment-timezone-with-data.min.js",true);
Asset::getInstance()->addJs("//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js",true);
Asset::getInstance()->addJs("//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/locale/ru.js",true);
Asset::getInstance()->addJs("//cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.9.4/scheduler.min.js",true);
Asset::getInstance()->addJs("//unpkg.com/axios/dist/axios.min.js",true);
Asset::getInstance()->addJs("//stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js",true);
Asset::getInstance()->addJs("//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js",true);

//Css
Asset::getInstance()->addCss("//stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css",true);
Asset::getInstance()->addCss("//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css",true);
Asset::getInstance()->addCss("//cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.9.4/scheduler.min.css",true);
Asset::getInstance()->addCss("//use.fontawesome.com/releases/v5.0.6/css/all.css",true);


?>

<h1 class="test-h1"><?=Loc::getMessage('CRM_GENESIS_SLOTS_MAIN_COMPONENT_TEST_TEXT')?><br><br></h1>


<div id="slot_calendar">

    <div class="filters" v-if="isAdmin">
        <select class="custom-select col-md-3 mb-3" v-model="seletedUserId">
            <option v-for="user in filterValueLists.users" :value="user.ID">{{user.NAME}}</option>
        </select>
    </div>
    <div>
        <div>
            <div class="week-work-hours-outer mb-3">
                <div><span>Отмечено за неделю
                        <span :class="workHoursThisWeek.class">{{workHoursThisWeek.hours}} </span>часов
                    </span>
                </div>
                <div><span>Отмечено за месяц
                        <span :class="workHoursThisMonth.class">{{workHoursThisMonth.hours}} </span>часов
                    </span>
                </div>
            </div>
        </div>
    </div>

    <calendar :events="events" :resources="resources" :editable="true" :settings="settings"></calendar>


    <!-- Modal add Slots id deal -->
    <div class="modal fade" id="workDayInCalendar" tabindex="-1" role="dialog" aria-labelledby="workDayInCalendarLabel" aria-hidden="true" ref="vuemodalchangeCategoryDeal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="workDayInCalendarLabel">Подтвердите евент</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!--@submit.prevent="addWorkPeriodToCalendar"-->
                    <form id="sendFormCategoryDeal" onsubmit="return false" method="POST">
                        <div class="form-group required">
                            <label class="form-control-label" for="workDayStart">Начало:</label>
                            <input type="datetime-local" id="workDayStart" name="workDayStart" class="form-control"
                                   disabled="disabled" v-model="workDayStart">
                        </div>
                        <div class="form-group required">
                            <label class="form-control-label" for="workDayFinish">Окончание:</label>
                            <input type="datetime-local" id="workDayFinish" name="workDayFinish" class="form-control"
                                   disabled="disabled" v-model="workDayFinish">
                        </div>
                        <div class="modal-footer">
                            <button v-if="!seletedSlotId" @click="addWorkPeriodToCalendar" class="btn btn-primary">Сохранить</button>
                            <button v-if="seletedSlotId" @click="deleteSlot" class="btn btn-danger">Удалить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

    <script src="/local/components/crmgenesis/slots.component/templates/.default/calendar.js"></script>
    <script src="/local/components/crmgenesis/slots.component/templates/.default/app.js"></script>

