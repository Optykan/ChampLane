var champData;

function openModal(cName) {
    var remap = ['P', 'Q', 'W', 'E', 'R'];
    document.getElementById("mbg").style.backgroundImage = "url('assets/splash/" + cName.dataset.key + ".jpg')";
    document.getElementById("champ-name").innerHTML = cName.dataset.name;
    document.getElementById("champ-title").innerHTML = champData['title'];

    for (var i = 0; i < 5; i++) {
        document.getElementsByClassName("ability")[i].src = "assets/abilities/" + cName.dataset.key + remap[i] + ".png";
    }
    //document.getElementById("modal").style.display = "block";
    document.getElementById("dim").style.top = "0";
}

function getInfo(elem) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            champData = JSON.parse(xhttp.responseText);
            document.getElementById("ability-desc").innerHTML = champData['passive']['description'];
            makeActive(document.getElementsByClassName("ability")[0]);
            document.getElementById("champ-lore").innerHTML = champData['lore'];
            document.getElementById("ability-cost").innerHTML = "No Cost";
            openModal(elem);
            console.log(champData);
        }
    };
    xhttp.open("GET", "abilities.php?champ=" + elem.dataset.key, true);
    xhttp.send();
}

function findVars(vars, val) {
    //vars is the array
    //val is the one we search for
    for (var i = 0; i < vars.length; i++) {
        if (vars[i]['key'] == val) {
            var type = vars[i]['link'];
            switch (type) {
                case 'bonusattackdamage':
                    type = "Bonus AD"
                    break;
                case 'spelldamage':
                    type = "AP";
                    break;
                case 'attackdamage':
                    type = "AD";
                    break;
            }
            var coeff = (function() {
                var coeffTemp = "";
                for (var j = 0; j < vars[i]['coeff'].length; j++) {
                    if (vars[i]['coeff'][j] == 1) {
                        coeffTemp += "1.0";
                    } else {
                        coeffTemp += vars[i]['coeff'][j].toString();
                    }
                    coeffTemp += "/";
                }
                return coeffTemp.substr(0, coeffTemp.length - 1);
            })();
            return coeff + " " + type;
        }
    }
    return false;
}

function makeActive(elem) {
    console.log(champData);
    var active = document.getElementsByClassName("active")[0];
    if (elem.dataset.id == "passive") {
        document.getElementById("ability-name").innerHTML = champData['passive']['name'];
        document.getElementById("ability-desc").innerHTML = champData['passive']['description'];
        document.getElementById("ability-cost").innerHTML = "No Cost";
    } else {
        document.getElementById("ability-name").innerHTML = champData['spells'][elem.dataset.id]['name'];
        //document.getElementById("ability-desc").innerHTML =
        var tooltip = champData['spells'][elem.dataset.id]['tooltip'];
        var type = champData['spells'][elem.dataset.id]['resource'];

        //get the ability cost type (mana/health/other)
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
            if (regex) {
                output = output.replace(regex[0], champData['spells'][elem.dataset.id]['effectBurn'][regex[0].match(/[0-9]/)[0]]);
            }
            document.getElementById("ability-cost").innerHTML = output;
        }
        //replace the tooltip stuff
        var replace = (function() {
            if (tooltip.match(/{{ [a-z][0-9] }}/g))
                return tooltip.match(/{{ [a-z][0-9] }}/g);
            return 0;
        })();
        var output = tooltip;
        for (var i = 0; i < replace.length; i++) {
            var eora = (function() {
                var replaceMatch = replace[i].match(/[a-z]/)[0];
                if (replaceMatch === "a") {
                    return "a" + replace[i].match(/[0-9]/);
                    //a1
                } else if (replaceMatch === "f") {
                    return "f" + replace[i].match(/[0-9]/);
                    //f1
                } else {
                    return "e" + replace[i].match(/[0-9]/);
                    //e1
                }
            })();
            if (eora.indexOf("a") != -1 || eora.indexOf("f") != -1) {
                //a1
                var fv = findVars(champData['spells'][elem.dataset.id]['vars'], eora);
                if (fv === false) {
                    //because we're missing data go replace in effectBurn list
                    //dammit riot get all the fX values in the json
                    output = output.replace(replace[i], champData['spells'][elem.dataset.id]['effectBurn'][eora.match(/[0-9]/)[0]]);
                } else {
                    output = output.replace(replace[i], fv);
                }
            } else {
                //e1
                console.log(i);
                output = output.replace(replace[i], champData['spells'][elem.dataset.id]['effectBurn'][eora.match(/[0-9]/)[0]]);
            }
            //output= output.replace(replace[i], )
        }
        document.getElementById("ability-desc").innerHTML = output;
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
    snodes[i].style.backgroundImage = "url('assets/splash/center/" + snodes[i].dataset.key + ".jpg')";
    cnodes[i].src = "assets/splash/square/" + snodes[i].dataset.key + ".png";
    snodes[i].onclick = function() {
        getInfo(this);
    }
}