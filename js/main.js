var snodes = document.getElementsByTagName("splash");
var cnodes = document.getElementsByClassName("champ");
var splash = document.getElementsByTagName("splash");
for (var i = 0; i < snodes.length; i++) {
    snodes[i].style.backgroundImage = "url('assets/splash/center/" + snodes[i].dataset.splash + ".jpg')";
    cnodes[i].src = "assets/splash/square/" + snodes[i].dataset.splash + ".png";
    splash[i].onclick = function() {
        openModal(this);
    };
}

function openModal(cName) {
    document.getElementById("mbg").style.backgroundImage = "url('assets/splash/" + cName.dataset.splash + ".jpg')";
    document.getElementById("champ-name").innerHTML = cName.dataset.name;
    document.getElementById("champ-title").innerHTML = cName.dataset.title;
    document.getElementById("champ-desc").innerHTML = cName.dataset.desc;
    //document.getElementById("modal").style.display = "block";
    document.getElementById("dim").style.top = "0";
    setTimeout(function() {
        //document.getElementById("modal").style.opacity = 1;
    }, 1000);
}

function closeModal() {
    document.getElementById("dim").style.top = "100%";
}