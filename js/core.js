$("#file").on('change', function () {
    $("#form-first").submit();
});

$("#file2").on('change', function () {
    $("#form-second").submit();
});

$("#button3").on('click', function () {
//    $("#form-third").submit();
    $.ajax({
        url: "/zadanie3.php",
        data: $('#input3')
    }).done(function () {
        console.log('super');
    });
}); 