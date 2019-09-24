//copy dir path
var dir = document.querySelector("span.dir");
dir.addEventListener("click", function() {
    var copyFrom = document.createElement("textarea");
    copyFrom.textContent = dir.dataset.dir;
    var bodyElm = document.getElementsByTagName("body")[0];
    bodyElm.appendChild(copyFrom);
    copyFrom.select();
    document.execCommand('copy');
    bodyElm.removeChild(copyFrom);

    window.open();
}, false);

var toggle = document.querySelector("span.toggle");
toggle.addEventListener("click", function() {
    var files = document.querySelector("div.phpfiles");
    if (files.style.display === "none") {
        toggle.innerHTML = "[▼]";
        files.style.display = "block";
    } else {
        toggle.innerHTML = "[▶]";
        files.style.display = "none";
    }
});

var targetedArray = [];
function targetNameChange() {
    if (location.hash) {
        var class_name = location.hash.replace('#', '');
        var target_name = document.querySelector("span." + class_name);

        targetedArray.forEach(function(elem) {
            elem.style.backgroundColor = "";
            elem.style.color = "";
        });

        if (! targetedArray.includes(target_name)) {
            targetedArray.push(target_name);
        }
        
        target_name.style.backgroundColor = "#e2e6ff";
        target_name.style.color = "#191919";
    }
}

window.onhashchange = targetNameChange;
targetNameChange();

//change background color of links
var links = document.querySelectorAll('[role="link"]');
var pressedLinks = [];
links.forEach(function(link) {
    link.addEventListener("mouseup", function(e) {
        if (e.which === 1 || e.which === 2) {
            pressedLinks.forEach(function(link) {
                link.style.backgroundColor = "";
                link.style.color = "";
            });

            if (! pressedLinks.includes(link)) {
                pressedLinks.push(link);
            }

            link.style.backgroundColor = "#dcf7d4";
            link.style.color = "#191919";
        }
    });
});