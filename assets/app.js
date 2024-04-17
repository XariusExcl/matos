import './bootstrap.js';
import './styles/app.css';

// https://stackoverflow.com/questions/23593052/format-javascript-date-as-yyyy-mm-dd
const dateFormat = function date2str(x, y) {
    var z = {
        M: x.getMonth() + 1,
        d: x.getDate(),
        h: x.getHours(),
        m: x.getMinutes(),
        s: x.getSeconds()
    };
    y = y.replace(/(M+|d+|h+|m+|s+)/g, function(v) {
        return ((v.length > 1 ? "0" : "") + z[v.slice(-1)]).slice(-2)
    });

    return y.replace(/(y+)/g, function(v) {
        return x.getFullYear().toString().slice(-v.length)
    });
}

// Audiovisual form date change
const loanDayElement = document.getElementById("audiovisual_loan_day")
loanDayElement?.addEventListener("change", formDateChange);
const loanTimeslot0Element = document.getElementById("audiovisual_loan_timeSlot_0")
loanTimeslot0Element?.addEventListener("change", formDateChange);
const loanTimeslot1Element = document.getElementById("audiovisual_loan_timeSlot_1")
loanTimeslot1Element?.addEventListener("change", formDateChange);
const loanTimeslot2Element = document.getElementById("audiovisual_loan_timeSlot_2")
loanTimeslot2Element?.addEventListener("change", formDateChange);

let cachedRequests = {};

async function formDateChange() {
    if (loanTimeslot0Element.checked && loanTimeslot2Element.checked) {
        // Check the middle timeslot if the first and last are selected
        loanTimeslot1Element.checked = true;
    }

    const dateValue = parseInt(loanDayElement.value);
    const timeslot0Value = loanTimeslot0Element.checked;
    const timeslot1Value = loanTimeslot1Element.checked;
    const timeslot2Value = loanTimeslot2Element.checked;

    if (!timeslot0Value && !timeslot1Value && !timeslot2Value)
        return;

    const startHourIndex = timeslot0Value ? 0 : (timeslot1Value ? 2 : 4);
    const endHourIndex = timeslot2Value ? 5 : (timeslot1Value ? 3 : 1);

    const hours = [
        {hours:9, minutes:15}, {hours:12, minutes:0},
        {hours:14, minutes:0}, {hours:17, minutes:30},
        {hours:17, minutes:30}, {hours:9, minutes:15}
    ];

    const startDate = new Date();
    const endDate = new Date();

    startDate.setDate(startDate.getDate() + dateValue + 1)
    startDate.setHours(
        hours[startHourIndex].hours,
        hours[startHourIndex].minutes,
        0
    );
    
    endDate.setDate(endDate.getDate() + dateValue + (timeslot2Value? 2 : 1))
    endDate.setHours(
        hours[endHourIndex].hours,
        hours[endHourIndex].minutes,
        0
    );

    const startDateStr = dateFormat(startDate, 'yyyy-MM-dd hh:mm')
    const endDateStr = dateFormat(endDate, 'yyyy-MM-dd hh:mm');

    // Fetch and update unavailable equipment
    let key = String(dateValue) + startHourIndex + endHourIndex;
    if (!(key in cachedRequests)) {
        const data = await fetch(`/api/unavailable_equipment?&s=${startDateStr}&e=${endDateStr}`)
        cachedRequests[key] = await data.json();
    }
    updateUnavailableEquipment(cachedRequests[key]);
}

let disabledInputs = [];

function updateUnavailableEquipment(request) {

    disabledInputs.forEach((input) => {
        input.disabled = false;
    });

    Object.entries(request.equipment).forEach(([key, value]) => { 
        if (value === 0 || equipmentQuantity[key] > value) return;

        const input = document.querySelector('[value="'+key+'"]');
        if (!input) return;

        input.disabled = true;
        disabledInputs.push(input);
    });
}