document.addEventListener("DOMContentLoaded", function() {
    let va = _authsafe("getRequestString");
    let b = document.getElementById('custom_field');
    b.value = va;

});