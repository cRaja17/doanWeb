$(document).foundation()



const slider = document.querySelector('.slider');
const track = document.querySelector('.slider-track');

function animate(a, b, items) {
    a.style.transform += `translateX(${-items[0].offsetWidth}px)`;
    b.style.transform += `translateX(${items[0].offsetWidth}px)`;
}

function previous(items) {
    track.prepend(items[items.length - 1]);
    animate(slider, track, items);
}

function next(items) {
    track.append(items[0]);
    animate(track, slider, items);
}

function activate(e) {
    const items = document.querySelectorAll('.item');
    e.target.closest('.next') && next(items);
    e.target.closest('.prev') && previous(items);
}

document.addEventListener('click', activate, false);
// header
const burgerButton = document.getElementById("burger");
const menu = document.getElementById("menu");
const overlay = document.querySelector(".overlay");


burgerButton.addEventListener("click", function () {
    burgerButton.classList.toggle("active");
    overlay.classList.toggle("active");
    menu.classList.toggle("active");
});

overlay.addEventListener("click", function () {
    burgerButton.classList.remove("active");
    overlay.classList.remove("active");
    menu.classList.remove("active");
});






