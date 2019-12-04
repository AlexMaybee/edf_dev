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
            return this.hourStart.toFixed(1);
        },
    }, methods: {
        animateHour: function () {
            var self = this;
            var id = setInterval(function () {
                if(self.hourStart < self.hours) {
                    return self.hourStart += 0.5;
                }
                else if(self.hourStart > self.hours){
                    return self.hourStart -= 0.5;
                }
                else clearInterval(id);
            },50)
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