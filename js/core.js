$("#file").on('change', function () {
    $("#form-first").submit();
});

$("#file2").on('change', function () {
    $("#form-second").submit();
});

$("#button3").on('click', function () {
    $.ajax({
        url: "/zadanie3.php",
        type: "POST",
        dataType: "json",
        data: {
            input: $('#input3').val()
        },
        success: function (data) {
            $("#result3").text(data.text);
        }
    });
}); 