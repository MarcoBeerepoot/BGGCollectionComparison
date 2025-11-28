function ShowLoading() {
    let div = document.createElement('div');
    div.className = "loader";
    let img = document.createElement('img');
    img.src = 'loading.gif';
    div.innerHTML = "Loading... This might take up to 1  minute!<br>";
    div.appendChild(img);
    document.body.appendChild(div);
    return true;
}