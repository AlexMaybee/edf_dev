<div class="modal fade" id="workDayInCalendar" tabindex="-1" role="dialog" aria-labelledby="workDayInCalendarLabel" aria-hidden="true" ref="vuemodalchangeCategoryDeal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workDayInCalendarLabel">Подтвердите выбор часов</h5>
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