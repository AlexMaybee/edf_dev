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
        <select class="custom-select col-md-3 mb-3" v-model="seletedUserId" @change="getUserSlots">
            <option v-for="user in filterValueLists.users" :value="user.ID">{{user.NAME}}</option>
        </select>
    </div>
    <div class="mb-3">

        <div>
            <div class="week-work-hours-outer">

                <animate-time-counters
                    :hours="workHoursThisWeek.hours"
                    :my-class="workHoursThisWeek.class"
                    :text="lang.weekHourText"
                    :measure="lang.measureText">
                </animate-time-counters>

                <animate-time-counters
                    :hours="workHoursThisMonth.hours"
                    :my-class="workHoursThisMonth.class"
                    :text="lang.monthHourText"
                    :measure="lang.measureText">
                </animate-time-counters>

            </div>
        </div>

    </div>

    <div class="copy-prev-week-btn  mb-3">
        <button type="button" :disabled="!prevWeekSlotsNum" @click="copyPreviousWeekSlots" class="btn btn-secondary">Копировать предыдущую неделю</button>
    </div>

    <div class="test-btn  mb-3">
        <button type="button" @click="openGspModal" class="btn btn-danger">Popup добавления инфы в слот</button>
    </div>

    <calendar :events="events" :resources="resources" :editable="true" :settings="settings"></calendar>


    <!-- Modal add Slots id deal -->
    <?php include_once 'includes/slotCreateModal.php'?>
    <!-- Modal add Slots id deal -->

    <!-- Modal TEST edit Slots id deal -->
    <?php include_once 'includes/gspModal.php'?>
    <!-- Modal TEST edit Slots id deal -->

</div>

    <script src="/local/components/crmgenesis/slots.component/templates/.default/calendar.js"></script>
    <script src="/local/components/crmgenesis/slots.component/templates/.default/vcomponents.js"></script>
    <script src="/local/components/crmgenesis/slots.component/templates/.default/app.js"></script>

