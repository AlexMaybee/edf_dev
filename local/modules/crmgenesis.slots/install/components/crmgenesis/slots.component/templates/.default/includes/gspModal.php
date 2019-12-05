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

                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="training-type">Тип</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotType" v-if="filterValueLists.slotTypeList.length > 0" class="form-control" id="training-type">
                                        <option selected value="">Не выбрано</option>
                                        <option v-for="type in filterValueLists.slotTypeList" :value="type.ID">{{type.NAME}}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-title">Клуб</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotClub" v-if="filterValueLists.slotClubList.length > 0"
                                            @change="gspZoneFilterByClub" class="form-control" id="training-type">
                                        <option selected value="">Не выбрано</option>
                                        <option v-for="club in filterValueLists.slotClubList" :value="club.ID">{{club.NAME}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-start-from">Период с</label>
                                </div>
                                <div class="col-8">
                                    <input v-model="slotPeriodFrom" type="date" class="form-control" id="group-start-from">
                                </div>
                            </div>

                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="zone">Зона {{filterValueLists.slotSortedZoneList.length}}</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotZone" :disabled="(filterValueLists.slotSortedZoneList.length > 0) ? false : true"
                                            @change="gspLocationFilterByZone" class="form-control" id="zone">
                                        <option selected value="">Не выбрано</option>
                                        <template v-if="filterValueLists.slotSortedZoneList.length > 0" >
                                            <option v-for="zona in filterValueLists.slotSortedZoneList" :value="zona.ID">{{zona.NAME}}</option>
                                        </template>

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-start-to">по</label>
                                </div>
                                <div class="col-8">
                                    <input v-model="slotPeriodTo" type="date" class="form-control" id="group-start-to">
                                </div>
                            </div>

                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="location">Локация {{filterValueLists.slotSortedLocationList.length}}</label>
                                </div>
                                <div class="col-8">
                                    <select v-model="slotLocation" :disabled="(filterValueLists.slotSortedLocationList.length > 0) ? false : true" class="form-control" id="location">
                                        <option selected value="">Не выбрано</option>
                                        <template v-if="filterValueLists.slotSortedLocationList.length > 0" >
                                            <option v-for="location in filterValueLists.slotSortedLocationList" :value="location.ID">{{location.NAME}}</option>
                                        </template>

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="age-start-from">Возраст с</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="age-start-from">
                                </div>

                            </div>

                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-length">Численность группы</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="group-length">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="age-start-to">до</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="age-start-to">
                                </div>
                            </div>

                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-duration">Длительность, мин</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="group-duration">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="employee">Сотрудник</label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input @keyup="gspUserFilter" type="text" class="form-control" id="employee" v-model="slotEmployee.name" autofocus>

                                     <div class="position-absolute col-11 slot-employee-absolute pt-3 mt-1 rounded" v-show="filterValueLists.slotSortedUserList.length > 0">
                                        <ul class="px-0">
                                            <li @click="selectCurrentUserFromList(user)" v-for="user in filterValueLists.slotSortedUserList">{{user.NAME}}</li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-title">Название группы</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="group-title">
                                </div>
                            </div>
                        </div>
                        <!--filterValueLists.slotCheckBoxList-->
                        <div class="form-group row">
                            <div class="col-md-12">

                                <div>{{slotSelectedCheckboxes}}</div>
                                <table class="table my-shedule-table text-center overflow-hidden" style="padding: 0">
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
                                                           v-model="slotSelectedCheckboxes" type="checkbox" :value="tdCol.TIME + '_' + tdCol.DAY">
                                                    </label>
                                                </td>
                                            </template>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>



                        <div class="modal-footer">
                            <button @click="" class="btn btn-primary">Сохранить</button>
                            <button @click="" class="btn btn-danger">Удалить</button>
                        </div>



                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

