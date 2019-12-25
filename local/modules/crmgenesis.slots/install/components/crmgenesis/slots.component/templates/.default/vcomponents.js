/*@component: 1. Hours animate component
*/
Vue.component('animate-time-counters',{
    props: {
        hours: 0,
        myClass: '',
        text: '',
        measure: '',
    },
    data: function(){
        return{
            hourStart: 0,
        }
    },
    watch: {
        hours: function () {
            this.animateHour();
        },
    },
    computed: {
        hourNew: function () {
            return this.hourStart.toFixed(2);
        },
    }, methods: {
        animateHour: function () {
            let self = this,
                id = setInterval(function () {

                if(self.hourStart.toFixed(2) < self.hours) {
                    return self.hourStart += 0.01;
                }
                else if(self.hourStart.toFixed(2) > self.hours){
                    return self.hourStart -= 0.01;
                }
                else clearInterval(id);
            },0.001)
        },
    },
    mounted: function () {
        this.animateHour();
    },
    template:
        `<div>
            <span>{{text}}
                <span :class="myClass">{{hourNew}} </span>{{measure}}
            </span>
        </div>`,
});

/*@component: 2. Click on Col component
*/
Vue.component('th-function-component', {
    props: {
        dayId: 0,
        name: '',
    },
    methods: {
        selectDayAllCols: function () {
            if(this.dayId > 0){
                let checkboxes = $('input[data-day="' + this.dayId + '"]'),
                    checkboxesChecked = $('input[data-day="' + this.dayId + '"]:checked');
                if(checkboxes.length > 0){
                    (checkboxes.length == checkboxesChecked.length)
                        ? $(checkboxes).prop('checked', false)
                        : $(checkboxes).prop('checked', true);
                    $.each(checkboxes, function (index,elem) {
                        elem.dispatchEvent(new Event('change'));
                    });
                }
            }
        }
    },
    template:
        `<th @click="selectDayAllCols()">{{name}}</th>`
});

/*@component: 3. Click on row component
*/
Vue.component('tr-td-function-component', {
    props: {
        timeId: 0,
        time: '',
    },
    methods: {
        selectTimeAllCols: function () {
            if(this.timeId > 0){
                let checkboxes = $('input[data-time="' + this.timeId + '"]'),
                    checkboxesChecked = $('input[data-time="' + this.timeId + '"]:checked');
                if(checkboxes.length > 0){
                    (checkboxes.length == checkboxesChecked.length)
                        ? $(checkboxes).prop('checked', false)
                        : $(checkboxes).prop('checked', true);
                    $.each(checkboxes, function (index,elem) {
                        elem.dispatchEvent(new Event('change'));
                    });
                }
            }
        },
    },
    template:
        `<td @click="selectTimeAllCols()">{{time}}</td>`
});

/*@component: 4. info Popup
*/
Vue.component('info-popup-component', {
    props: {
        text: '',
        buttonSuccessText: '',
        buttonRejectText: '',
        modalTitle: '',
        successfunctn: '',
        rejectfunctn: '',
        popupClass: '',
    },
    template:
        `<div class="modal fade" id="infoModalCenter" tabindex="-1" role="dialog" aria-labelledby="infoModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" 
                 :class="popupClass">
                    <div class="modal-header">
                        <h5 class="modal-title" id="infoModalCenterTitle">{{modalTitle}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                       <h4>{{text}}</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="rejectfunctn" class="btn btn-secondary" data-dismiss="modal">{{buttonRejectText}}</button>
                        <button type="button" @click="successfunctn" class="btn btn-primary">{{buttonSuccessText}}</button>
                    </div>
                </div>
            </div>
        </div>`
});



// Vue.component('select-user-from-list-component', {
//     data:  function(){
//         return  {
//             selectedUserId: 0,
//         }
//     },
//     props: {
//         filteredList: {},
//     },
//     watch:{
//         selectedUserId: function () {
//           console.log('selected User #',this.selectedUserId);
//         },
//     },
//     template:
//         `<div class="position-absolute col-11 slot-employee-absolute" v-show="filteredList.length > 0">
//             {{filteredList.length}}
//             <ul>
//                 <li v-if="filteredList.length > 0" @click="selectedUserId = user.ID"
//                 :data-userId="user.ID" v-for="user in filteredList">{{user.ID}} - {{user.NAME}}</li>
//             </ul>
//         </div>`,
//
// });
