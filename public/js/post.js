
$(window).on('load resize', function () {
    var windowWidth = $(window).width();
    if (windowWidth < 768) {
        $('#right-content').foundation('_destroy');
    }
});
document.addEventListener("DOMContentLoaded", function () {
    var fixedButton = document.getElementById("fixedButton");
    var closeTable = document.getElementById("close-table");
    var tableOfContentsMobile = document.querySelector(".table-of-contents-mobile");
    var header = document.querySelector(".header");
    var main = document.querySelector(".main");
    var footer = document.querySelector("footer");


    fixedButton.addEventListener("click", function () {
        fixedButton.classList.add("clicked");
        setTimeout(function () {
            fixedButton.classList.remove("clicked");
        }, 5000);
    });
    // Thêm event listener cho #fixedButton
    fixedButton.addEventListener("click", function (event) {
        if (event.target === fixedButton || fixedButton.contains(event.target)) {
            tableOfContentsMobile.classList.toggle("active");
            header.classList.add("blurred");
            main.classList.add("blurred");
            footer.classList.add("blurred");
        }
    });

    closeTable.addEventListener("click", function (event) {
        tableOfContentsMobile.classList.remove("active");
        header.classList.remove("blurred");
        main.classList.remove("blurred");
        footer.classList.remove("blurred");
    });

    // Thêm event listener cho toàn bộ document để kiểm tra click
    document.addEventListener("click", function (event) {
        if (!tableOfContentsMobile.contains(event.target) && event.target !== fixedButton && !fixedButton.contains(event.target)) {
            tableOfContentsMobile.classList.remove("active");
            header.classList.remove("blurred");
            main.classList.remove("blurred");
            footer.classList.remove("blurred");
        }
    });

    // Thêm event listener cho các liên kết trong .table-of-contents-mobile
    var linksInTableOfContents = tableOfContentsMobile.querySelectorAll("a");
    linksInTableOfContents.forEach(function (link) {
        link.addEventListener("click", function () {
            tableOfContentsMobile.classList.remove("active");
            header.classList.remove("blurred");
            main.classList.remove("blurred");
            footer.classList.remove("blurred");
        });
    });
});

