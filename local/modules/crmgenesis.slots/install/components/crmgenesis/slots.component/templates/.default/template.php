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

//Asset::getInstance()->addJs("//unpkg.com/axios/dist/axios.min.js",true);
Asset::getInstance()->addJs("https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js",true);


Asset::getInstance()->addJs("//stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js",true);
Asset::getInstance()->addJs("//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js",true);
//Multiselect component
Asset::getInstance()->addJs("https://unpkg.com/vue-multiselect@2.1.0",true);

//Css
Asset::getInstance()->addCss("//stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css",true);
Asset::getInstance()->addCss("//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css",true);
Asset::getInstance()->addCss("//cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.9.4/scheduler.min.css",true);
Asset::getInstance()->addCss("//use.fontawesome.com/releases/v5.0.6/css/all.css",true);
//Multiselect component
Asset::getInstance()->addCss("https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css",true);


?>


<!--<h1 class="test-h1"><?/*=Loc::getMessage('CRM_GENESIS_SLOTS_MAIN_COMPONENT_TEST_TEXT')*/?><br><br></h1>-->


<div id="slot_calendar">

    <div class="filter-indicators mb-3">

        <div class="col-4">
<!--            <div class="filters col-12 text-center p-2" v-if="isAdmin">-->
<!--                <select class="custom-select col-12" v-model="seletedUserId" @change="getUserSlots">-->
<!--                    <option v-for="user in filterValueLists.users" :value="user.ID">{{user.NAME}}</option>-->
<!--                </select>-->
<!--            </div>-->

            <div class="filters col-12 text-center p-2 row align-items-center">
                <div class="col-3">
                    <label for="employee-main">Сотрудник:</label></div>
                <div class="col-9">
                    <input type="text" class="form-control" id="employee-main"
                           :disabled="(isAdmin > 0) ? false : true"
                           @keyup="userFilter"
                           v-model="selectedUser.name" autofocus>
                    <div class="position-absolute col-11 slot-employee-absolute pt-3 mt-1 rounded"
                         v-show="filterValueLists.sortedUserList.length > 0">
                        <ul class="px-0">
                            <li @click="selectCurrentUserFromListMain(user)"
                                v-for="user in filterValueLists.sortedUserList">{{user.NAME}}</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>



        <div class="col-4 p-2">
            <div class="copy-prev-week-btn col-12 text-center">
<!--                @click="copyPreviousWeekSlots"  --- clickOnCopyPrevWeek -->
                <button type="button" class="btn btn-secondary"
                        @click="clickOnCopyPrevWeek"
                        :disabled="!prevWeekSlotsNum">Копировать предыдущую неделю</button>
            </div>
        </div>


        <div class="col-4">
            <div>
                <div class="week-work-hours-outer text-right">

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
    </div>
    


    <div class="test-btn  mb-3">
        <button type="button" @click="openGspModal" class="btn btn-danger">Создание расписания</button>
    </div>



    <calendar :events="events" :resources="resources" :editable="true" :settings="settings"></calendar>


    <!-- Modal add Slots id deal -->
    <?php include_once 'includes/slotCreateModal.php'?>
    <!-- Modal add Slots id deal -->

    <!-- Modal TEST edit Slots id deal -->
    <?php include_once 'includes/gspModal.php'?>
    <!-- Modal TEST edit Slots id deal -->


    <!--info popup component-->
     <info-popup-component
            :button-success-text = "info.buttonSuccessText"
            :button-reject-text = "info.buttonRejectText"
            :modal-title = "info.modalTitle"
            :popup-class = "info.popupClass"
            :rejectfunctn = "info.rejectFunction"
            :successfunctn = "info.successFunction"
            :text="info.text"
    ></info-popup-component>
    <!--info popup component-->

</div>

    <script src="/local/components/crmgenesis/slots.component/templates/.default/calendar.js"></script>
    <script src="/local/components/crmgenesis/slots.component/templates/.default/vcomponents.js"></script>
    <script src="/local/components/crmgenesis/slots.component/templates/.default/app.js"></script>

