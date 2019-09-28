(function () {
    //copy dir path
    const dir = document.querySelector(".dir");
    dir.addEventListener("click", function () {
        const copyFrom = document.createElement("textarea");
        copyFrom.textContent = dir.dataset.dir;
        const bodyElm = document.getElementsByTagName("body")[0];
        bodyElm.appendChild(copyFrom);
        copyFrom.select();
        document.execCommand('copy');
        bodyElm.removeChild(copyFrom);

        window.open();
    }, false);

    const toggle = document.querySelector("span.toggle");
    if (toggle) {
        toggle.addEventListener("click", function () {
            const files = document.querySelector("div.phpfiles");
            if (files.style.display === "none") {
                toggle.innerHTML = '<span class="icon-chevron-down"></span>';
                files.style.display = "block";
            } else {
                toggle.innerHTML = '<span class="icon-chevron-right"></span>';
                files.style.display = "none";
            }
        });
    }

    var targetedArray = [];
    function targetNameChange() {
        if (location.hash) {
            const class_name = location.hash.replace('#', '');
            const target_name = document.querySelector("span." + class_name);

            targetedArray.forEach(function (elem) {
                elem.style.backgroundColor = "";
                elem.style.color = "";
            });

            if (!targetedArray.includes(target_name)) {
                targetedArray.push(target_name);
            }

            target_name.style.backgroundColor = "#e2e6ff";
            target_name.style.color = "#191919";
        }
    }

    window.onhashchange = targetNameChange;
    targetNameChange();

    //change background color of links
    const links = document.querySelectorAll('[role="link"]');
    const pressedLinks = [];
    links.forEach(function (link) {
        link.addEventListener("mouseup", function (e) {
            if (e.which === 1 || e.which === 2) {
                pressedLinks.forEach(function (link) {
                    link.style.backgroundColor = "";
                    link.style.color = "";
                });

                if (!pressedLinks.includes(link)) {
                    pressedLinks.push(link);
                }

                link.style.backgroundColor = "#dcf7d4";
                link.style.color = "#191919";
            }
        });
    });

    const toggleButton = document.querySelector('button[role="toggle_input_text"]');
    const inputTextAndButton = document.querySelector('div[role="input_text_and_button"]');
    function toggleInputTextAndButton(evt) {
        if (inputTextAndButton.classList.contains('none')) {
            inputTextAndButton.classList.remove('none');
            evt.target.classList.replace('icon-chevron-right', 'icon-chevron-left');
        } else {
            inputTextAndButton.classList.add('none');
            evt.target.classList.replace('icon-chevron-left', 'icon-chevron-right');
        }
    }
    toggleButton.addEventListener('click', toggleInputTextAndButton);
})();