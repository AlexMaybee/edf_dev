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
                                    <select class="form-control" id="training-type">
                                        <option selected value="">Не выбрано</option>
                                        <option value="1">Групповая</option>
                                        <option value="2">Индивидуальная</option>
                                        <option value="3">Сплит</option>
                                    </select>
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


                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-length">Численность группы</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="group-length">
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
                                    <label for="zone">Зона</label>
                                </div>
                                <div class="col-8">
                                    <select class="form-control" id="zone">
                                        <option selected value="">Не выбрано</option>
                                        <option value="4">Финтесс</option>
                                        <option value="5">Бассейн</option>
                                        <option value="6">Спа</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="location">Локация</label>
                                </div>
                                <div class="col-8">
                                    <select class="form-control" id="location">
                                        <option selected value="">Не выбрано</option>
                                        <option value="7">Зал № 1</option>
                                        <option value="8">Зал № 2</option>
                                        <option value="9">Зал № 3</option>
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
                                    <input type="date" class="form-control" id="group-start-from">
                                </div>
                            </div>
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="group-start-to">по</label>
                                </div>
                                <div class="col-8">
                                    <input type="date" class="form-control" id="group-start-to">
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
                                    <label for="age-start-to">до</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="age-start-to">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col row">
                                <div class="col-4 text-right">
                                    <label for="employee">Сотрудник</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="employee">
                                </div>
                            </div>
                            <div class="form-group col row"></div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                               <!-- <label for="">Дни недели</label>-->
                                <table class="table my-shedule-table text-center overflow-hidden" style="padding: 0">
                                    <thead>
                                        <tr>
                                            <td>Время</td>
                                            <td>ПН</td>
                                            <td>ВТ</td>
                                            <td>СР</td>
                                            <td>ЧТ</td>
                                            <td>ПТ</td>
                                            <td>СБ</td>
                                            <td>ВС</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>07.00</td>
                                            <td>
                                                <input type="checkbox" value="1" id="chbx-mon">
                                            </td>
                                            <td>
                                                <input type="checkbox" value="2" id="chbx-tue">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="3" id="chbx-wed">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-thu">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-fri">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-sat">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-sun">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>08.00</td>
                                            <td>
                                                <input type="checkbox" value="1" id="chbx-mon">
                                            </td>
                                            <td>
                                                <input type="checkbox" value="2" id="chbx-tue">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="3" id="chbx-wed">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-thu">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-fri">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-sat">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-sun">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>09.00</td>
                                            <td>
                                                <input type="checkbox" value="1" id="chbx-mon">
                                            </td>
                                            <td>
                                                <input type="checkbox" value="2" id="chbx-tue">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="3" id="chbx-wed">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-thu">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-fri">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-sat">
                                            </td>
                                            <td>
                                                <input class="" type="checkbox" value="1" id="chbx-sun">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>10.00</td>
                                        </tr>
                                        <tr>
                                            <td>11.00</td>
                                        </tr>
                                        <tr>
                                            <td>12.00</td>
                                        </tr>
                                        <tr>
                                            <td>13.00</td>
                                        </tr>
                                        <tr>
                                            <td>14.00</td>
                                        </tr>
                                        <tr>
                                            <td>15.00</td>
                                        </tr>
                                        <tr>
                                            <td>16.00</td>
                                        </tr>
                                        <tr>
                                            <td>17.00</td>
                                        </tr>
                                        <tr>
                                            <td>18.00</td>
                                        </tr>
                                        <tr>
                                            <td>19.00</td>
                                        </tr>
                                        <tr>
                                            <td>20.00</td>
                                        </tr>
                                        <tr>
                                            <td>21.00</td>
                                        </tr>
                                        <tr>
                                            <td>22.00</td>
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

