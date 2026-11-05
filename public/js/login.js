$(function() {
            window.onscroll = function() {
                scrollFunction()
            };
        });


        function scrollFunction() {
            if ($(window).scrollTop() > 100) {
                $(".logoUASLP").removeClass("d-block").addClass("d-none");
                $(".textoUASLP").removeClass("d-none").addClass("d-block");
                $(".divisorUASLP-ENTIDAD").removeClass("d-block").addClass("d-none");
                $(".divisorUASLP-ENTIDADScroll").removeClass("d-none").addClass("d-block");
                $(".header").css("height", "85px");

            }
            if ($(window).scrollTop() < 40) {
                $(".logoUASLP").removeClass("d-none").addClass("d-block");
                $(".textoUASLP").removeClass("d-block").addClass("d-none");
                $(".divisorUASLP-ENTIDAD").removeClass("d-none").addClass("d-block");
                $(".divisorUASLP-ENTIDADScroll").removeClass("d-block").addClass("d-none");
                $(".header").css("height", "120px");
            }
        }