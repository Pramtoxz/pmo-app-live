(function ($) {
    "use strict";
    var allVals = [];

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("body").on("click", ".delete", function () {
        var current_object = $(this);
        var id = current_object.attr("id");
        var type = current_object.attr("data-id");
        var link = window.location.origin;

        if (type == "spin") {
            link = link + "/luckywheel/delete/" + id;
        } else if (type == "user") {
            link = link + "/users/delete/" + id;
        } else if (type == "usertrack") {
            link = link + "/users/delete/" + id;
        } else if (type == "payreq_") {
            link = link + "/withdrawal/delete/" + id;
        } else if (type == "appoffer") {
            link = link + "/offers/delete/" + id;
        } else if (type == "video") {
            link = link + "/videozone/delete/" + id;
        } else if (type == "web") {
            link = link + "/article/delete/" + id;
        } else if (type == "game") {
            link = link + "/games/delete/" + id;
        } else if (type == "banner") {
            link = link + "/banner/delete/" + id;
        } else if (type == "redeem") {
            link = link + "/withdrawal/deleteCat/" + id;
        } else if (type == "redeemMethod") {
            link = link + "/withdrawal/method/delete/" + id;
        } else if (type == "faq") {
            link = link + "/faq/delete/" + id;
        } else if (type == "coinstore") {
            link = link + "/coinstore/delete/" + id;
        } else if (type == "dailyoffer") {
            link = link + "/dailyoffer/delete/" + id;
        } else if (type == "offerwall") {
            link = link + "/offerwall/delete/" + id;
        } else if (type == "onboarding") {
            link = link + "/setting/onboarding/delete/" + id;
        } else if (type == "subscriptions") {
            link = link + "/subscriptions/delete/" + id;
        } else if (type == "admin") {
            link = link + "/admin/delete/" + id;
        } else if (type == "nativead") {
            link = link + "/ads/native/delete/" + id;
        } else if (type == "referTask") {
            link = link + "/contest/delete/" + id;
        } else if (type == "social") {
            link = link + "/setting/social/delete/" + id;
        } else if (type == "homepagecat") {
            link = link + "/app-setting/homepage/category/delete/" + id;
        } else if (type == "homebtn") {
            link = link + "/app-setting/homepage/delete/" + id;
        } else if (type == "brand") {
            link = link + "/merchant/brand/delete/" + id;
        } else if (type == "voucher") {
            link = link + "/merchant/voucher/delete/" + id;
        } else if (type == "product") {
            link = link + "/product/delete/" + id;
        } else if (type == "quiz") {
            link = link + "/quiz/delete/" + id;
        } else if (type == "daily_mission") {
            link = link + "/app-setting/front/delete/" + id;
        }

        console.log(link);
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: link,
                    type: "GET",
                    success: function (data) {
                        console.log(data);
                        if (data == 1) {
                            if (type == "usertrack") {
                                $(location).attr(
                                    "href",
                                    window.location.origin + "/users"
                                );
                            } else {
                                $("#" + id).remove();
                                Swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "Data has been deleted.",
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                            }
                        } else {
                            Swal.fire({
                                position: "top-end",
                                icon: "info",
                                title: data,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                        }
                    },
                });
            }
        });
    });

    $("body").on("click", ".sub_chk", function () {
        allVals = [];
        allVals = $(".table input:checkbox:checked")
            .map(function () {
                return $(this).val();
            })
            .toArray();

        console.log("button clicked" + allVals);
    });

    $("body").on("click", ".sub_chk_all", function () {
        allVals = [];
        $("#data-list input:checkbox").attr("checked", true);

        $("#data-list .sub_chk:checked").each(function () {
            allVals.push($(this).attr("data-id"));
        });

        console.log("select id is " + allVals);
    });

    $("body").on("click", ".dropdown-item", function () {
        var current_object = $(this);
        var action = current_object.attr("id");
        var type = current_object.attr("data-id");
        console.log(action + type + allVals);
        var url;
        var msg;

        if (type == "appoffer") {
            url = "/offers/action";
        } else if (type == "payreq") {
            url = "/withdrawal/action";
        } else if (type == "video") {
            url = "/videozone/action";
        } else if (type == "web") {
            url = "/article/action";
        } else if (type == "game") {
            url = "/games/action";
        } else if (type == "banner") {
            url = "/banner/action";
        } else if (type == "redeemMethod") {
            url = "/withdrawal/method/action";
        } else if (type == "redeem") {
            url = "/withdrawal/actionCat";
        } else if (type == "coinstore") {
            url = "/coinstore/action";
        } else if (type == "faq") {
            url = "/faq/action";
        } else if (type == "dailyoffer") {
            url = "/dailyoffer/action";
        } else if (type == "offerwall") {
            url = "/offerwall/action";
        } else if (type == "videowall") {
            url = "/offerwall/videowall/action";
        } else if (type == "support") {
            url = "/support/action";
        } else if (type == "onboarding") {
            url = "/onboarding/action";
        } else if (type == "subscriptions") {
            url = "/subscriptions/action";
        } else if (type == "nativeAd") {
            url = "/ads/native/action";
        } else if (type == "referTask") {
            url = "/contest/action";
        } else if (type == "social" || type == "lang" || type == "gateway") {
            url = "/setting/action";
        } else if (type == "homepage") {
            url = "/app-setting/homepage/action";
        } else if (type == "homepagecat") {
            url = "/app-setting/homepage/category/action";
        }

        if (action == "enable") {
            msg = "Update Succesfully !!";
        } else if (action == "disable") {
            msg = "Update Succesfully !!";
        } else if (action == "delete") {
            msg = "Delete Succesfully !!";
        } else if (action == "approve") {
            msg = "Approved Succesfully !!";
        } else if (action == "reject") {
            msg = "Reject Succesfully !!";
        } else if (action == "closed") {
            msg = "Update Succesfully !!";
        } else if (action == "onprocess") {
            msg = "Update Succesfully !!";
        }

        var join_selected_values = allVals.join(",");
        if (allVals == "") {
            Swal.fire("Please Select at Least one Row !!");
        } else {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            id: join_selected_values,
                            type: type,
                            status: action,
                        },

                        success: function (data) {
                            console.log("resp=>" + data);
                            if (data == 1) {
                                location.reload();
                                Swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: msg,
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                            } else {
                                Swal.fire({
                                    position: "top-end",
                                    icon: "danger",
                                    title: data,
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                            }
                        },
                    });
                } else {
                    Swal.fire("Something went wrong!!");
                }
            });
        }
    });

    // function del($type,$id){
    //   Swal.fire({
    //           title: 'Are you sure?',
    //           text: "You won't be able to revert this!",
    //           icon: 'warning',
    //           showCancelButton: true,
    //           confirmButtonColor: '#3085d6',
    //           cancelButtonColor: '#d33',
    //           confirmButtonText: 'Yes, delete it!'
    //         }).then((result) => {
    //           if (result.isConfirmed) {
    //             $("#"+$id).remove();
    //              Swal.fire(
    //               'Deleted!',
    //               'Item has been deleted.',
    //               'success'
    //             )
    //           }
    //         })
    // };
})(jQuery);
