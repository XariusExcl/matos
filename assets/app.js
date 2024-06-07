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
let cachedRequests = {};
let isDateValid = false;
let unavailableStartTimeSlots = {};

// To grey out the days/timeslots that are not available
function processUnavailableDays() {
    let index = 0;
    let inUnavailablePeriod = false;
    let timeslotCount = loanStartTimeslotElement.options.length;

    loanableDays.forEach((day) => {
        [...loanStartTimeslotElement.options].map((o) => {
            let date = new Date();
            date.setDate(date.getDate() + day + 1);
            date.setHours(parseInt(o.value.substring(0, 2)), parseInt(o.value.substring(2, 4)), 0);
    
            if (!inUnavailablePeriod) {
                if (unavailableDays[index] && date >= unavailableDays[index].start && date <= unavailableDays[index].end) {
                    inUnavailablePeriod = true;
                }
            } else {
                if (unavailableDays[index] && date > unavailableDays[index].end) {
                    inUnavailablePeriod = false;
                    index++;
                }
            }

            if (inUnavailablePeriod) {
                if (unavailableStartTimeSlots[day] === undefined) {
                    unavailableStartTimeSlots[day] = [];
                }
                unavailableStartTimeSlots[day].push(o.value);
            }
        });
        if (unavailableStartTimeSlots[day] !== undefined && unavailableStartTimeSlots[day].length === timeslotCount) {
            loanStartDayElement.querySelector(`option[value="${day}"]`).disabled = true;
            loanEndDayElement.querySelector(`option[value="${day}"]`).disabled = true; // FIXME
        }
    });
}

async function formDateChange() {
    const loanStartDay = parseInt(loanStartDayElement.value);
    const loanStartTimeslot = parseInt(loanStartTimeslotElement.value);
    const loanEndDay = parseInt(loanEndDayElement.value);
    const loanEndTimeslot = parseInt(loanEndTimeslotElement.value);

    // Reenable timeslots
    loanStartTimeslotElement.querySelectorAll('option').forEach((option) => {
        option.disabled = false;
    });

    // Disable end day if it's before start day
    loanEndDayElement.querySelectorAll('option').forEach((option) => {
        option.disabled = false;
        if (parseInt(option.value) < loanStartDay) {
            option.disabled = true;
        }
    });

    // Disable preprocessed unavailable timeslots
    if (unavailableStartTimeSlots[loanStartDay]) {
        unavailableStartTimeSlots[loanStartDay].forEach((timeslot) => {
            loanStartTimeslotElement.querySelector(`option[value="${timeslot}"]`).disabled = true;
            loanEndTimeslotElement.querySelector(`option[value="${timeslot}"]`).disabled = true; // FIXME
        });
    }

    // Make sure the start date is before the end date
    if (loanEndDay < loanStartDay || (loanEndDay == loanStartDay && loanEndTimeslot <= loanStartTimeslot)) {
        document.querySelector('#creneau-error').innerText = "La date de fin doit être après la date de début.";
        isDateValid = false;
        return;
    }
    
    // Don't exceed max loan duration
    if(loanName == "audiovisual" && loanableDays.indexOf(loanEndDay) - loanableDays.indexOf(loanStartDay) > 1){
        document.querySelector('#creneau-error').innerText = "La durée maximale d'emprunt est de 1 jour.";
        isDateValid = false;
        return;
    } else if ((loanName == "vr" || loanName == "graphic_design") && loanableDays.indexOf(loanEndDay) - loanableDays.indexOf(loanStartDay) > 5) {
        document.querySelector('#creneau-error').innerText = "La durée maximale d'emprunt est de 1 semaine.";
        isDateValid = false;
        return;
    }

    // Calculate the start and end date
    const startDate = new Date();
    const endDate = new Date();
    startDate.setDate(startDate.getDate() + loanStartDay + 1);
    startDate.setHours(
        parseInt(loanStartTimeslotElement.value.substring(0, 2)),
        parseInt(loanStartTimeslotElement.value.substring(2, 4)),
        0
    );

    endDate.setDate(endDate.getDate() + loanEndDay + 1);
    endDate.setHours(
        parseInt(loanEndTimeslotElement.value.substring(0, 2)),
        parseInt(loanEndTimeslotElement.value.substring(2, 4)),
        0
    );

    // Check if date is not in unavailable dates
    let flag = false;
    unavailableDays.forEach((unavailableDate) => {
        if (
            (startDate >= unavailableDate.start && startDate <= unavailableDate.end)
            || (endDate >= unavailableDate.start && endDate <= unavailableDate.end)
            || (startDate <= unavailableDate.start && endDate >= unavailableDate.end)
        ) {
            document.querySelector('#creneau-error').innerText = "Le créneau d'emprunt est indisponible.";
            isDateValid = false;
            flag = true;
            return;
        }
    });
    if (flag) return;

    isDateValid = true;
    document.querySelector('#creneau-error').innerText = "";

    // Fetch and update unavailable equipment
    const startDateStr = dateFormat(startDate, 'yyyy-MM-dd hh:mm')
    const endDateStr = dateFormat(endDate, 'yyyy-MM-dd hh:mm');

    const key = String(loanStartDay) + loanStartTimeslot + loanEndDay + loanEndTimeslot;
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
        if (value === 0 || equipmentInfo[key]["quantity"] > value) return;

        const input = document.querySelector('[value="'+key+'"]');
        if (!input) return;

        input.disabled = true;
        disabledInputs.push(input);
    });
}

function checkFormValidity() {
    const form = document.querySelector('form');

    if (!isDateValid) {
        scrollToInvalidInput(loanStartDayElement);
        let error = document.querySelector('#creneau-error');
        if (error.innerText === "")
            error.innerHTML = "Le créneau d'emprunt est invalide !";
        return;
    }
    
    // check if required inputs are filled
    const requiredInputs = form.querySelectorAll('[required]');
    let isValid = true;
    for(let input of requiredInputs) {
        if (!input.reportValidity()) {
            scrollToInvalidInput(input);
            isValid = false;
            return;
        }
    }
    if (!isValid) return;
   
   // create a list of selected equipment
   let selectedEquipment = [];
   
   const equipmentInputs = document.querySelectorAll('#equipmentSelection input[type="checkbox"]:checked, #equipmentSelection input[type="radio"]:checked');
   equipmentInputs.forEach((input) => {
       if (input.checked && input.value != "") {
           selectedEquipment.push(equipmentInfo[input.value]["name"]);
        }
    });

    if (selectedEquipment.length === 0) {
        document.querySelector('#error_modal_content').innerHTML = "Votre emprunt est vide !";
        document.querySelector('#error_modal').showModal();
        return;
    }

    // Show the list in the modal
    document.querySelector('#confirmation_modal_content').innerHTML = "• " + selectedEquipment.join('<br> • ');

    // Show the modal
    document.querySelector('#confirmation_modal').showModal()
}

function scrollToInvalidInput(input) {
    input.scrollIntoView({behavior: 'smooth', block: 'center'});
}

function submitLoanForm() {
    document.querySelector('form').submit();
}

window.formDateChange = formDateChange;
window.checkFormValidity = checkFormValidity;
window.submitLoanForm = submitLoanForm;
window.processUnavailableDays = processUnavailableDays;