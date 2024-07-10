(function($) {
    "use strict";

    document.querySelectorAll("[data-year]").forEach(function(el) {
        el.textContent = new Date().getFullYear();
    });

    var dropdown = document.querySelectorAll("[data-dropdown]");
    if (dropdown) {
        dropdown.forEach(function(el) {
            let dropdownMenu = el.querySelector(".drop-down-menu");

            function dropdownOP() {
                if (
                    el.getBoundingClientRect().top + dropdownMenu.offsetHeight >
                    window.innerHeight - 60 &&
                    el.getAttribute("data-dropdown-position") !== "top"
                ) {
                    dropdownMenu.style.top = "auto";
                    dropdownMenu.style.bottom = "40px";
                } else {
                    dropdownMenu.style.top = "40px";
                    dropdownMenu.style.bottom = "auto";
                }
            }
            window.addEventListener("click", function(e) {
                if (el.contains(e.target)) {
                    el.classList.toggle("active");
                    setTimeout(function() {
                        el.classList.toggle("animated");
                    }, 0);
                } else {
                    el.classList.remove("active");
                    el.classList.remove("animated");
                }
                dropdownOP();
            });
            window.addEventListener("resize", dropdownOP);
            window.addEventListener("scroll", dropdownOP);
        });
    }

    var toggle = document.querySelectorAll('[data-toggle]');
    if (toggle) {
        toggle.forEach(function(el, id) {
            el.querySelector(".toggle-title").addEventListener("click", () => {
                for (var i = 0; i < toggle.length; i++) {
                    if (i !== id) {
                        toggle[i].classList.remove("active");
                        toggle[i].classList.remove("animated");
                    }
                }
                if (el.classList.contains("active")) {
                    el.classList.remove("active");
                    el.classList.remove("animated");
                } else {
                    el.classList.add("active");
                    setTimeout(function() {
                        el.classList.add("animated");
                    }, 0);
                }
            });
        });
    }

    const dashboard = document.querySelector(".dashboard"),
        dashboardToggleBtn = document.querySelectorAll(".dashboard-toggle-btn");
    if (dashboard) {
        dashboardToggleBtn.forEach((el) => {
            el.addEventListener("click", () => {
                dashboard.classList.toggle("toggle");
            });
        });
        dashboard.querySelector(".dashboard-sidebar .overlay").addEventListener("click", () => {
            dashboard.classList.remove("toggle");
        });
    }

    let inputNumeric = document.querySelectorAll('.input-numeric');
    if (inputNumeric) {
        inputNumeric.forEach((el) => {
            el.oninput = () => {
                el.value = el.value.replace(/[^0-9]/g, '');
            };
        });
    }

    let clipboardBtn = document.querySelectorAll(".btn-copy");
    if (clipboardBtn) {
        clipboardBtn.forEach((el) => {
            let clipboard = new ClipboardJS(el);
            clipboard.on("success", () => {
                toastr.success(config.translates.copied);
            });
        });
    }

    let actionConfirm = $('.action-confirm');
    if (actionConfirm.length) {
        actionConfirm.on('click', function(e) {
            if (!confirm(config.translates.actionConfirm)) {
                e.preventDefault();
            }
        });
    }

    let selectFileBtn = $('#selectFileBtn'),
        selectedFileInput = $("#selectedFileInput"),
        filePreviewBox = $('.file-preview-box'),
        filePreviewImg = $('#filePreview');

    selectFileBtn.on('click', function() {
        selectedFileInput.trigger('click');
    });

    selectedFileInput.on('change', function() {
        var file = true,
            readLogoURL;
        if (file) {
            readLogoURL = function(input_file) {
                if (input_file.files && input_file.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        filePreviewBox.removeClass('d-none');
                        filePreviewImg.attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input_file.files[0]);
                }
            }
        }
        readLogoURL(this);
    });

    let selectpicker = $('.selectpicker');
    if (selectpicker.length) {
        selectpicker.selectpicker({
            noneSelectedText: config.translates.noneSelectedText,
            noneResultsText: config.translates.noneResultsText,
            countSelectedText: config.translates.countSelectedText
        });
    }

})(jQuery);