<?//@modal: this is the main modal with adding additional info to selected slots?>
<div class="modal fade" id="gspModal" tabindex="-1" role="dialog" aria-labelledby="gspModalLabel" aria-hidden="true" ref="vuemodalchangeCategoryDeal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gspModalLabel">Добавление расписания</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="addInfoToSlot" onsubmit="return false" method="POST">

                        <!--v-if="workDayStart && workDayFinish"-->
                        <div class="row" v-if="seletedSlotId">

                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="group-start-from">Начало</label>
                                </div>
                                <div class="col-8">
                                    <input v-model="workDayStart"
                                           @change="changeGspDurationField()"
                                           :class="{'my-error-border': slotValidateErrors.workDayStart.length > 0}"
                                           type="datetime-local" class="form-control" id="group-start-from">
                                </div>
                            </div>
                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="group-start-to">Окончание</label>
                                </div>
                                <div class="col-8">
                                    <input v-model="workDayFinish"
                                           @change="changeGspDurationField()"
                                           :class="{'my-error-border': slotValidateErrors.workDayFinish.length > 0}"
                                           type="datetime-local" class="form-control" id="group-start-to">
                                </div>
                            </div>
                           <!-- <div class="col-12 mb-3">
                                <div v-show="slotValidateErrors.workDayStart.length > 0" class="my-error">{{slotValidateErrors.workDayStart}}</div>
                                <div v-show="slotValidateErrors.workDayFinish.length > 0" class="my-error">{{slotValidateErrors.workDayFinish}}</div>
                            </div>-->

                        </div>

                        <div class="row">
                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="employee">Тренер</label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input @keyup="gspUserFilter" type="text" class="form-control" id="employee"
                                           :class="{'my-error-border': slotValidateErrors.employee.length > 0}"
                                           v-model="slotFilters.employee.name" autofocus disabled>
                                    <div class="position-absolute col-11 slot-employee-absolute pt-3 mt-1 rounded"
                                         v-show="filterValueLists.slotSortedUserList.length > 0">
                                        <ul class="px-0">
                                            <li @click="selectCurrentUserFromList(user)"
                                                v-for="user in filterValueLists.slotSortedUserList">{{user.NAME}}</li>
                                        </ul>
                                    </div>

                                </div>
                            </div>

                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="group-size">Численность группы</label>
                                </div>
                                <div class="col-8">
                                    <input v-model="slotFilters.groupSize"
                                           :disabled="!(slotFilters.type in typeIdVal) ? false : true || slotFilters.type == 0"

                                           @keyup="checkGroupSize"
                                           :class="{'my-error-border': slotValidateErrors.groupSize.length > 0}"
                                           type="text" class="form-control" id="group-size">

                                </div>
                            </div>

                            <!--<div class="col-12 mb-3">
                                <div v-show="slotValidateErrors.employee.length > 0" class="my-error">{{slotValidateErrors.employee}}</div>
                                <div v-show="slotValidateErrors.groupSize.length > 0" class="my-error">{{slotValidateErrors.groupSize}}</div>
                            </div>-->
                        </div>

                        <div class="row">
                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="training-type">Тип</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotFilters.type" v-if="filterValueLists.slotTypeList.length > 0"
                                            @change="changeTypeID"
                                            :class="{'my-error-border': slotValidateErrors.type.length > 0}"
                                            class="form-control" id="training-type">
                                        <option selected value="0">Не выбрано</option>
                                        <option v-for="type in filterValueLists.slotTypeList" :value="type.ID">{{type.NAME}}</option>
                                    </select>

                                </div>
                            </div>

                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="group-duration">Длительность, мин</label>
                                </div>
                                <div class="col-8">
                                    <!--disabled-->
                                    <input v-model="slotFilters.durationMins"
                                           @keyup="checkdurationMins"

                                           :class="{'my-error-border': slotValidateErrors.durationMins.length > 0}"
                                           type="text" class="form-control" id="group-duration">

                                </div>
                            </div>

                            <!--<div class="col-12 mb-3">
                                <div v-show="slotValidateErrors.type.length > 0" class="my-error">{{slotValidateErrors.type}}</div>
                                <div v-show="slotValidateErrors.durationMins.length > 0" class="my-error">{{slotValidateErrors.durationMins}}</div>
                            </div>-->
                        </div>

                        <div class="row">
                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="group-title">Клуб</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotFilters.club" v-if="filterValueLists.slotClubList.length > 0"
                                            :class="{'my-error-border': slotValidateErrors.club.length > 0}"
                                            @change="gspZoneFilterByClub" class="form-control" id="club">
                                        <option selected value="0">Не выбрано</option>
                                        <option v-for="club in filterValueLists.slotClubList" :value="club.ID">{{club.NAME}}</option>
                                    </select>

                                </div>
                            </div>

                            <div class="form-group col row align-items-center">

                                <template v-if="(slotFilters.type in typeIdVal) ? false : true">

                                    <div class="col-3 text-right">
                                        <label for="age-start-from">Возраст с</label>
                                    </div>
                                    <div class="col-4">
                                        <input v-model="slotFilters.ageFrom"
                                               @keyup="checkAgeFrom"
                                               :class="{'my-error-border': slotValidateErrors.ageFrom.length > 0}"
                                               type="text" class="form-control" id="age-start-from">

                                    </div>

                                    <div class="col-1 text-right">
                                        <label for="age-start-to">до</label>
                                    </div>
                                    <div class="col-4">
                                        <input v-model="slotFilters.ageTo"
                                               @keyup="checkAgeTo"
                                               :class="{'my-error-border': slotValidateErrors.ageTo.length > 0}"
                                               type="text" class="form-control" id="age-start-to">

                                    </div>
                                </template>

                            </div>
                         <!--   <div class="col-12 mb-3">
                                <div v-show="slotValidateErrors.club.length > 0" class="my-error">{{slotValidateErrors.club}}</div>
                                <div v-show="slotValidateErrors.ageFrom.length > 0" class="my-error col-12">{{slotValidateErrors.ageFrom}}</div>
                                <div v-show="slotValidateErrors.ageTo.length > 0" class="my-error col-12">{{slotValidateErrors.ageTo}}</div>
                            </div>-->

                        </div>


                        <div class="row">
                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="zone">Зона {{filterValueLists.slotSortedZoneList.length}}</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotFilters.zone" :disabled="(filterValueLists.slotSortedZoneList.length > 0) ? false : true"
                                            :class="{'my-error-border': slotValidateErrors.zona.length > 0}"
                                            @change="gspLocationFilterByZone" class="form-control" id="zone">
                                        <option selected value="0">Не выбрано</option>
                                        <template v-if="filterValueLists.slotSortedZoneList.length > 0" >
                                            <option v-for="zona in filterValueLists.slotSortedZoneList" :value="zona.ID">{{zona.NAME}}</option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col row align-items-center">
                                <template v-if="(slotFilters.type in typeIdVal) ? false : true">
                                    <div class="col-3 text-right">
                                        <label for="group-start-from">Период с</label>
                                    </div>
                                    <div class="col-4">
                                        <input v-model="slotFilters.periodFrom"
                                               :disabled="!(slotFilters.type in typeIdVal) ? false : true"
                                               :class="{'my-error-border': slotValidateErrors.periodFrom.length > 0}"
                                               type="date" class="form-control" id="group-start-from">
                                    </div>

                                    <div class="col-1 text-right">
                                        <label for="group-start-to">по</label>
                                    </div>
                                    <div class="col-4">
                                        <input v-model="slotFilters.periodTo"
                                               :disabled="!(slotFilters.type in typeIdVal) ? false : true"
                                               :class="{'my-error-border': slotValidateErrors.periodTo.length > 0}"
                                               type="date" class="form-control" id="group-start-to">
                                    </div>
                                </template>
                            </div>

                           <!-- <div class="col-12 mb-3">
                                <div v-show="slotValidateErrors.zona.length > 0" class="my-error">{{slotValidateErrors.zona}}</div>
                                <div v-show="slotValidateErrors.periodFrom.length > 0" class="my-error">{{slotValidateErrors.periodFrom}}</div>
                                <div v-show="slotValidateErrors.periodTo.length > 0" class="my-error">{{slotValidateErrors.periodTo}}</div>
                            </div>-->

                        </div>


                        <div class="row">

                            <template v-if="slotFilters.zone == 29118">

                                <div class="form-group col row align-items-center">
                                    <div class="col-4 text-right">
                                        <label for="zone">Услуги {{filterValueLists.slotSortedServiceList.length}}</label>
                                    </div>
                                    <div class="col-8">

                                        <!--:disabled="(filterValueLists.slotSortedServiceList.length > 0) ? false : true"-->
                                        <select v-model="slotFilters.service"

                                                :class="{'my-error-border': slotValidateErrors.service.length > 0}"
                                                @change="gspLocationFilterByZone" class="form-control" id="zone">
                                            <option selected value="0">Не выбрано</option>
                                            <template v-if="filterValueLists.slotSortedServiceList.length > 0" >
                                                <option v-for="service in filterValueLists.slotSortedServiceList" :value="service.ID">{{service.MY_PRODUCT_NAME}}</option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group col row align-items-center"></div>

                                <!--<div class="col-12 mb-3">
                                    <div v-show="slotValidateErrors.service.length > 0" class="my-error">{{slotValidateErrors.service}}</div>
                                </div>-->

                            </template>




                        </div>

                        <div class="row">

                            <template v-if="slotFilters.zone == 29118">

                                <div class="form-group col row align-items-center">
                                    <div class="col-4 text-right">
                                        <label for="products">Продукты</label>
                                    </div>

                                    <div class="col-8">
                                        <input @keyup="gspProductFilter" type="text" class="form-control" id="products"
                                               :class="{'my-error-border': slotValidateErrors.products.length > 0}"
                                               v-model="slotFilters.productName" autofocus>

                                        <div class="position-absolute col-11 slot-employee-absolute pt-3 mt-1 rounded"
                                             v-show="filterValueLists.slotSortedProductList.length > 0">
                                            <ul class="px-0">
                                                <li @click="selectCurrentProductFromList(product)"
                                                    v-for="product in filterValueLists.slotSortedProductList">{{product.NAME}}</li>
                                            </ul>
                                        </div>


                                        <div class="col-12">{{slotFilters.productName}}</div>

                                    </div>

                                </div>

                                <div class="form-group col row align-items-center">

                                    <div class="elems-array col-12 mt-1 mb-1" v-if="slotFilters.products.length > 0">
                                        <div class="one-elem rounded pt-1 pb-1 pl-2 pr-2 mb-1 mr-1"
                                             v-for="elem in slotFilters.products"
                                             @click="deleteCurrentProductFromList(elem)">{{elem.NAME}}</div>
                                    </div>

                                </div>

                                <!--<div class="col-12 mb-3">
                                    <div v-show="slotValidateErrors.products.length > 0" class="my-error">{{slotValidateErrors.products}}</div>
                                </div>-->

                            </template>


                        </div>


                        <div class="row">
                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="location">Локация {{filterValueLists.slotSortedLocationList.length}}</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotFilters.location"
                                            @change="gspGroupFilterByLocation"
                                            :disabled="(filterValueLists.slotSortedLocationList.length > 0 && filterValueLists.slotSortedZoneList.length > 0) ? false : true"
                                            :class="{'my-error-border': slotValidateErrors.location.length > 0}"
                                            class="form-control" id="location">
                                        <option selected value="0">Не выбрано</option>
                                        <template v-if="filterValueLists.slotSortedLocationList.length > 0" >
                                            <option v-for="location in filterValueLists.slotSortedLocationList" :value="location.ID">{{location.NAME}}</option>
                                        </template>
                                    </select>

                                </div>
                            </div>

<!--                            <div class="form-group col row align-items-center">-->
<!--                                <div class="col-4 text-right">-->
<!--                                    <label for="contacts">Клиенты</label>-->
<!--                                </div>-->
<!--                                <div class="col-8 position-relative">-->
<!--                                    <input @keyup="getContactsToFilter" type="text" class="form-control" id="contacts"-->
<!--                                           :class="{'my-error-border': slotValidateErrors.contacts.length > 0}"-->
<!--                                           v-model="slotFilters.currentContact.name" autofocus>-->
<!---->
<!--                                    {{slotFilters.contacts}}-->
<!---->
<!--                                    <div class="position-absolute col-11 slot-employee-absolute pt-3 mt-1 rounded"-->
<!--                                         v-show="filterValueLists.slotRequestedContacts.length > 0">-->
<!--                                        <ul class="px-0">-->
<!--                                            <li @click="selectCurrentContactFromList(contact)"-->
<!--                                                v-for="contact in filterValueLists.slotRequestedContacts">{{contact.NAME}}</li>-->
<!--                                        </ul>-->
<!--                                    </div>-->
<!---->
<!---->
<!--                                    <div v-show="slotValidateErrors.contacts.length > 0" class="my-error">{{slotValidateErrors.contacts}}</div>-->
<!--                                </div>-->
<!--                                <div class="col-8" v-if="slotFilters.contacts.length > 0">-->
<!--                                    <span v-for="contact in slotFilters.contacts">{{contact.NAME}}</span>-->
<!--                                </div>-->
<!--                            </div>-->


                            <div class="form-group col row align-items-center">
                                <div class="col-4 text-right">
                                    <label for="contacts">Клиенты</label>
                                </div>
                                <div class="col-8 position-relative">
                                    <multiselect
                                            v-model="slotFilters.contacts"
                                            :options="filterValueLists.slotRequestedContacts"
                                            :multiple="true"
                                            :max="3"
                                            :max-limit="showContactsMaxLimitError"
                                            :clear-on-select="true"
                                            value="ID"
                                            label="NAME"
                                            track-by="NAME"
                                            placeholder="Выберите контакт"
                                            @input="testGetContacts"
                                    >
                                    </multiselect>

                                </div>
<!--                                :maxElements="showContactsMaxLimitError"-->
<!--                                :noResult="showContactsMaxLimitError"-->
                            </div>

                            <!--<div class="col-12 mb-3">
                                <div v-show="slotValidateErrors.location.length > 0" class="my-error">{{slotValidateErrors.location}}</div>
                                <div v-show="slotValidateErrors.contacts.length > 0" class="my-error">{{slotValidateErrors.contacts}}</div>
                            </div>-->

                        </div>

                        <div class="row">

                            <div class="form-group col row align-items-center">

                                <template v-if="(slotFilters.type in typeIdVal) ? false : true">
                                    <div class="col-4 text-right">
                                        <label for="trainingGroup">Группы {{filterValueLists.slotSortedTrainingGroupList.length}}</label>
                                    </div>
                                    <div class="col-8">
                                        <select v-model="slotFilters.groupId"
                                                :disabled="(filterValueLists.slotGroupTrainingList.length > 0
                                            && filterValueLists.slotSortedZoneList.length > 0
                                            && filterValueLists.slotSortedLocationList.length > 0) ? false : true"
                                                :class="{'my-error-border': slotValidateErrors.groupId.length > 0}"
                                                class="form-control" id="trainingGroup">
                                            <option selected value="0">Не выбрано</option>
                                            <template v-if="filterValueLists.slotSortedTrainingGroupList.length > 0" >
                                                <option v-for="group in filterValueLists.slotSortedTrainingGroupList" :value="group.ID">{{group.NAME}}</option>
                                            </template>
                                            <option :value="slotFilters.slotShowGroupNameFieldDefault">Создать новую</option>
                                        </select>
                                    </div>
                                </template>

                            </div>

                            <div class="form-group col row align-items-center"></div>

<!--                            <div class="col-12 mb-3">-->
<!--                                <div v-show="slotValidateErrors.groupId.length > 0" class="my-error">{{slotValidateErrors.groupId}}</div>-->
<!--                            </div>-->

                        </div>

                        <div class="row">
                            <template v-if="(slotFilters.type in typeIdVal) ? false : true">
                                <div class="form-group col row align-items-center">

                                    <template v-if="slotFilters.groupId == slotFilters.slotShowGroupNameFieldDefault">
                                        <div class="col-4 text-right">
                                            <label for="group-title">Название группы</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" class="form-control" id="group-title"
                                                   :class="{'my-error-border': slotValidateErrors.groupName.length > 0}"
                                                   v-model="slotFilters.groupName">
                                        </div>
                                    </template>

                                </div>

                                <div class="form-group col row align-items-center">
                                    <template v-if="slotFilters.groupId == slotFilters.slotShowGroupNameFieldDefault">
                                        <div class="col-4 text-right">
                                           <button class="btn btn-info"
                                           @click="addNewGroupGsp()">Создать группу</button>
                                        </div>
                                    </template>
                                </div>
                            </template>


                            <!--<div class="col-12 mb-3">
                                <div v-show="slotValidateErrors.groupName.length > 0" class="my-error">{{slotValidateErrors.groupName}}</div>
                            </div>-->
                        </div>

                        <div class="col-12 mb-3 text-center">
                            <div class="my-error" v-show="slotValidateErrors.phperror.length > 0"
                                 v-for="error in slotValidateErrors.phperror">{{error}}</div>

                            <div v-show="slotValidateErrors.workDayStart.length > 0" class="my-error">{{slotValidateErrors.workDayStart}}</div>
                            <div v-show="slotValidateErrors.workDayFinish.length > 0" class="my-error">{{slotValidateErrors.workDayFinish}}</div>
                            <div v-show="slotValidateErrors.employee.length > 0" class="my-error">{{slotValidateErrors.employee}}</div>
                            <div v-show="slotValidateErrors.groupSize.length > 0" class="my-error">{{slotValidateErrors.groupSize}}</div>
                            <div v-show="slotValidateErrors.type.length > 0" class="my-error">{{slotValidateErrors.type}}</div>
                            <div v-show="slotValidateErrors.durationMins.length > 0" class="my-error">{{slotValidateErrors.durationMins}}</div>
                            <div v-show="slotValidateErrors.club.length > 0" class="my-error">{{slotValidateErrors.club}}</div>
                            <div v-show="slotValidateErrors.ageFrom.length > 0" class="my-error col-12">{{slotValidateErrors.ageFrom}}</div>
                            <div v-show="slotValidateErrors.ageTo.length > 0" class="my-error col-12">{{slotValidateErrors.ageTo}}</div>
                            <div v-show="slotValidateErrors.zona.length > 0" class="my-error">{{slotValidateErrors.zona}}</div>
                            <div v-show="slotValidateErrors.periodFrom.length > 0" class="my-error">{{slotValidateErrors.periodFrom}}</div>
                            <div v-show="slotValidateErrors.periodTo.length > 0" class="my-error">{{slotValidateErrors.periodTo}}</div>
                            <div v-show="slotValidateErrors.service.length > 0" class="my-error">{{slotValidateErrors.service}}</div>
                            <div v-show="slotValidateErrors.products.length > 0" class="my-error">{{slotValidateErrors.products}}</div>
                            <div v-show="slotValidateErrors.location.length > 0" class="my-error">{{slotValidateErrors.location}}</div>
                            <div v-show="slotValidateErrors.contacts.length > 0" class="my-error">{{slotValidateErrors.contacts}}</div>
                            <div v-show="slotValidateErrors.groupId.length > 0" class="my-error">{{slotValidateErrors.groupId}}</div>
                            <div v-show="slotValidateErrors.groupName.length > 0" class="my-error">{{slotValidateErrors.groupName}}</div>
                            <div v-show="slotValidateErrors.checkboxes.length > 0" class="my-error">{{slotValidateErrors.checkboxes}}</div>
                        </div>

                        {{slotFilters.contacts}}

                        <!--filterValueLists.slotCheckBoxList-->
                        <div class="form-group row"
                             v-if="(slotFilters.type in typeIdVal) ? false : true">
                            <div class="col-md-12">

                                <div>{{slotSelectedCheckboxes}}</div>
                                <!--<div v-show="slotValidateErrors.checkboxes.length > 0" class="my-error">{{slotValidateErrors.checkboxes}}</div>-->
                                <table class="table my-shedule-table text-center overflow-hidden"
                                       :class="{'my-bg-error': slotValidateErrors.checkboxes.length > 0}">
                                    <thead>
                                        <tr>
                                            <template v-for="(thCol,colThInd) in filterValueLists.slotCheckBoxList.ths">
                                                <th v-if="colThInd == 0">{{thCol.NAME}}</th>
                                                <template v-else>
                                                    <th-function-component
                                                            :day-id="thCol.ID"
                                                            :name="thCol.NAME">
                                                    </th-function-component>
                                                </template>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="rowTD in filterValueLists.slotCheckBoxList.tds">
                                            <template v-for="(tdCol,colTbInd) in rowTD">

                                                <template v-if="colTbInd == 0">
                                                    <tr-td-function-component
                                                            :time-id="tdCol.ID"
                                                            :time="tdCol.TIME">
                                                    </tr-td-function-component>
                                                </template>
                                                <td v-else >
                                                    <label :for="'chbox-' + tdCol.TIME + '_' + tdCol.DAY">
                                                    <input :data-time="tdCol.TIME" :data-day="tdCol.DAY"
                                                           :id="'chbox-' + tdCol.TIME + '_' + tdCol.DAY"

                                                           v-model="slotSelectedCheckboxes" type="checkbox" :value="tdCol.DAY + '_' + tdCol.TIME">
                                                    </label>
                                                </td>
                                            </template>

<!--                                            v-model="slotSelectedCheckboxes" type="checkbox" :value="tdCol.TIME + '_' + tdCol.DAY">-->
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>



                        <div class="modal-footer">
                            <button @click="validateGspModal" class="btn btn-primary">Сохранить</button>
                            <button v-if="seletedSlotId" @click="deleteSlot" class="btn btn-danger">Удалить</button>
                        </div>



                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

