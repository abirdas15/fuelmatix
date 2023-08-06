const popupWrappper = document.querySelector('.popup-wrapper-modal'),
    popupBox = document.querySelector('.popup-box'),
    closeBtn = document.querySelector('.popup-box .closeBtn'),
    submitBtn = document.querySelectorAll('.popup-btn');

window.onclick = function (e) {

    if (e.target.classList.contains('popup-btn') || e.target.parentElement.classList.contains('popup-btn')) {
        e.preventDefault();
        popupWrappper.classList.remove('d-none')
    }

    if (e.target == popupWrappper && e.target != popupBox) {
        popupWrappper.classList.add('d-none')
    }
}
if (closeBtn){
    closeBtn.onclick = function () {
        popupWrappper.classList.add('d-none')
    }
}
