var champData;

function getInfo(cName) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            champData = JSON.parse(xhttp.responseText);
            document.getElementById("ability-desc").innerHTML = champData['passive']['description'];
            makeActive(document.getElementsByClassName("ability")[0]);
            document.getElementById("champ-lore").innerHTML = champData['lore'];
            document.getElementById("ability-cost").innerHTML = "No Cost";
        }
    };
    xhttp.open("GET", "abilities.php?champ=" + cName, true);
    xhttp.send();
}


function openModal(cName) {
    var remap = ['P', 'Q', 'W', 'E', 'R'];
    getInfo(cName.dataset.splash);
    document.getElementById("mbg").style.backgroundImage = "url('assets/splash/" + cName.dataset.splash + ".jpg')";
    document.getElementById("champ-name").innerHTML = cName.dataset.name;
    document.getElementById("champ-title").innerHTML = champData['title'];

    for (var i = 0; i < 5; i++) {
        document.getElementsByClassName("ability")[i].src = "assets/abilities/" + cName.dataset.splash + remap[i] + ".png";
    }
    //document.getElementById("modal").style.display = "block";
    document.getElementById("dim").style.top = "0";
}

function makeActive(elem) {
    var active = document.getElementsByClassName("active")[0];
    if (elem.dataset.id == "passive") {
        document.getElementById("ability-name").innerHTML = champData['passive']['name'];
        document.getElementById("ability-desc").innerHTML = champData['passive']['description'];
        document.getElementById("ability-cost").innerHTML = "No Cost";
    } else {
        document.getElementById("ability-name").innerHTML = champData['spells'][elem.dataset.id]['name'];
        document.getElementById("ability-desc").innerHTML = champData['spells'][elem.dataset.id]['description'];
        var type = champData['spells'][elem.dataset.id]['resource'];
        if (type === "No Cost") {
            document.getElementById("ability-cost").innerHTML = "No Cost";
        } else {
            var costBurn = champData['spells'][elem.dataset.id]['costBurn'];
            var output = type;
            if (output.indexOf("{{ cost }}") >= 0) {
                // {{ cost }} Mana {{ e4 }} mana/second
                output = output.replace("{{ cost }}", costBurn);
            }
            var regex = output.match(/{{ e[0-9] }}/);
            console.log(regex[0]);
            if (regex[0] != null) {
                output = output.replace(regex[0], champData['spells'][elem.dataset.id]['effectBurn'][regex[0].match(/[0-9]/)[0]]);
            }
            document.getElementById("ability-cost").innerHTML = output;

        }

    }
    active.className = "ability";
    elem.className = "ability active";
}

function closeModal() {
    document.getElementById("dim").style.top = "100%";
}

var cnodes = document.getElementsByClassName("champ");
var snodes = document.getElementsByTagName("splash");
for (var a = 0; a < 5; a++) {
    document.getElementsByClassName("ability")[a].onclick = function() {
        makeActive(this);
    }
}

for (var i = 0; i < snodes.length; i++) {
    snodes[i].style.backgroundImage = "url('assets/splash/center/" + snodes[i].dataset.splash + ".jpg')";
    cnodes[i].src = "assets/splash/square/" + snodes[i].dataset.splash + ".png";
    snodes[i].onclick = function() {
        openModal(this);
    }
}