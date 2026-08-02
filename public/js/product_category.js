// range price 
const ranges = [...document.querySelectorAll(".duel-range")];
const leftInput = document.querySelector(".left");
const rightInput = document.querySelector(".right");

const priceRange = document.querySelector("#info-price-range");

ranges.forEach(range => {
  range.addEventListener("input", updateRange);
});

function updateRange(e) {
  const isLeft = e.target.classList.contains("left-range");
  const parent = e.target.parentNode;
  var leftPos;
  var rightPos;

  if (isLeft) {
    leftPos = e.target;
    rightPos = parent.querySelector(".right-range");

    if (parseFloat(leftPos.value) > parseFloat(rightPos.value)) {
      e.target.value = parseFloat(rightPos.value)
    }
  }
  else {
    leftPos = parent.querySelector(".left-range");
    rightPos = e.target;

    if (parseFloat(rightPos.value) < parseFloat(leftPos.value)) {
      e.target.value = parseFloat(leftPos.value);
    };
  }

  const leftPer = (Math.round(leftPos.value) / parseFloat(leftPos.max)) * 100;
  const rightPer = (Math.round(rightPos.value) / parseFloat(rightPos.max)) * 100;
  leftPos.style.setProperty("--left-per", `${leftPer}%`);
  leftPos.style.setProperty("--right-per", `${rightPer}%`);

  updateDualSliderValue(Math.round(leftPos.value), Math.round(rightPos.value), parent.parentNode);

}
function updateDualSliderValue(left, right, element) {
  element.querySelector(".start > span").innerText = Math.round(left);
  element.querySelector(".end > span").innerText = Math.round(right);

}
