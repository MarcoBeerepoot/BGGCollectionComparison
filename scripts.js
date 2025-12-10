function ShowLoading() {
    let div = document.createElement("div");
    div.className = "loader";
    let img = document.createElement("img");
    img.src = "loading.gif";
    img.alt = "";
    div.innerHTML = "<p>Loading... This might take up to 1  minute!</p>";
    div.appendChild(img);
    document.body.appendChild(div);
    return true;
}

/*
onLoad(){
var formElements = document.getElementsByTagName("form");
for (i=0;i<formElements.length;i++){
  addEventListener(formElements, "onsubmit", return showLoadingOverlay());
}
}
*/