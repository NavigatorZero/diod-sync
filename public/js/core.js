window.onload = function () {
    var main = new Vue({
        el: '#app',
    });
}

var toastElList = [].slice.call(document.querySelectorAll('.toast'));
var toastList = toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl, option)
})
