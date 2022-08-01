var toastElList = [].slice.call(document.querySelectorAll('.toast'))
console.log(toastElList)
var toastList = toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl, option)
})
