function mostrarToast(id_ticket, boton) {
    var toastElement = document.getElementById('toast' + id_ticket);
    var toast = new bootstrap.Toast(toastElement);

    var rect = boton.getBoundingClientRect();
    var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    var top = rect.top + scrollTop - toastElement.offsetHeight - 10;
    var left = rect.left + (rect.width / 2) - (toastElement.offsetWidth / 2);

    if (top < 0) top = 10;
    if (left < 0) left = 10;
    if (left + toastElement.offsetWidth > window.innerWidth) left = window.innerWidth - toastElement.offsetWidth - 10;

    toastElement.style.top = top + "px";
    toastElement.style.left = left + "px";

    toast.show();
    makeToastDraggable('toast' + id_ticket);
}

function makeToastDraggable(toastId) {
    var toastElement = document.getElementById(toastId);
    toastElement.style.position = "fixed";
    toastElement.style.cursor = "move";

    toastElement.onmousedown = function(e) {
        e.preventDefault();
        var startX = e.clientX - toastElement.offsetLeft;
        var startY = e.clientY - toastElement.offsetTop;

        document.onmousemove = function(e) {
            var x = e.clientX - startX;
            var y = e.clientY - startY;
            if (x < 0) x = 0;
            if (y < 0) y = 0;
            if (x + toastElement.offsetWidth > window.innerWidth) x = window.innerWidth - toastElement.offsetWidth;
            if (y + toastElement.offsetHeight > window.innerHeight) y = window.innerHeight - toastElement.offsetHeight;

            toastElement.style.left = x + "px";
            toastElement.style.top = y + "px";
        };

        document.onmouseup = function() {
            document.onmousemove = null;
            document.onmouseup = null;
        };
    };
}
