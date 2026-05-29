(function ($) {
    'use strict';



	$("body").on("click", ".updateUserBal", function () {
        var current_object = $(this);
        var id = current_object.attr('id');
        console.log('id=>'+id);
        $("#balanceModal").modal('show');
        $("#uid").val(id);
    });

    $("body").on("click", ".sendnoti", function () {
        var current_object = $(this);
        var id = current_object.attr('id');
        console.log('id=>'+id);
        $("#usernotimodal").modal('show');
        $("#userid").val(id);
    });
    
    $("body").on("click", ".rejectpromo", function () {
        var current_object = $(this);
        var id = current_object.attr('id');
        var type = current_object.attr('data-id');
        console.log('id=>'+id);
        $("#promorejmodal").modal('show');
        $("#promoid").val(id);
        $("#promotype").val(type);
    });
    

    


})(jQuery);