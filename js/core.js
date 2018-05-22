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

$("#file4").on('change', function () {
    var file_data = $('#file4').prop("files")[0];   
    var form_data = new FormData(); 
    form_data.append('file', file_data);
    
    console.log(file_data);
    console.log(form_data);
    
    $.ajax({
        url: "/zadanie4.php",
        type: 'POST',
        dataType: 'JSON',  
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        success: function (data) {
            $("#result4").html(data.out);
        }
    });
});