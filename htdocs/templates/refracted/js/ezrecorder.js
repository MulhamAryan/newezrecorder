$(document).ready( function () {
    $("#forgot_password").click(function () {
        $("#passwordForm").fadeIn();
    });
    $("#closePassForm").click(function (){
        $("#passwordForm").fadeOut();
    });

    if($("#autostop").is(':checked'))
        advancedOptionsStatus(1);

    else
        advancedOptionsStatus(2);


    $("#autostop").change(function(){
        if(this.checked)
            advancedOptionsStatus(1);

        else
            advancedOptionsStatus(2);
    });

    $("#initRecorder").submit(function(){
        $("#loadingRecording").fadeIn();
        $("#initRecorder").hide();
    });

    $("#camposition").click(function (){
        if($("#campresets").is(":hidden")) {
            $("#campresets").fadeIn();
        }
        else{
            $("#campresets").fadeOut();
        }
    });
});

//Recording fonctions
function recordStatus(fnct){
    if(fnct == "play"){
        $("#play").css({display:'none'});
        $("#pause").fadeIn();
    }
    else if(fnct == "pause"){
        $("#pause").css({display:'none'});
        $("#play").fadeIn();
    }
    $.ajax({
        type: 'GET',
        url: "ajax.php?action=recording&status=" + fnct,
        cache: false,
        timeout: 10000,
        error: function(){
            alert("Warning: This action could not be executed.\n\nVerify that you are still connected to PODC and refresh the page in your web browser (ctrl+R / cmd+R) before retrying.");
        }
    });
}

//Publish function
function publishRecord(place){
    $.ajax({
        type: 'GET',
        url: "ajax.php?action=publish&publishin=" + place,
        cache: false,
        timeout: 10000,
        success: function(){
            $("#finalized").show();

            if(place == "public")
                $("#published_in_public").fadeIn();

            else if(place == "private")
                $("#published_in_private").fadeIn();

            else
                $("#deleted_record").fadeIn();

            $("#recordingPublish").hide();
            $.get("index.php?action=logout");
        },
        error: function(){
            alert("Warning: This action could not be executed.\n\nVerify that you are still connected to PODC and refresh the page in your web browser (ctrl+R / cmd+R) before retrying.");
        }
    });
}

function advancedOptionsStatus(status){
    if(status == 1){
        $("#recordoptions").css({opacity:1});
        $("#publicalbum").prop("disabled",false);
        $("#privatealbum").prop("disabled",false);
        $("#stoptime").prop("disabled",false);
    }
    else{
        $("#recordoptions").css({opacity:0.5});
        $("#publicalbum").prop("disabled",true);
        $("#privatealbum").prop("disabled",true);
        $("#stoptime").prop("disabled",true);
    }
}
//Camera position function
function changeCamPosition(position){
    $.ajax({
        type: 'GET',
        url: "ajax.php?action=cam_move&plan=" + position,
        cache: false,
        timeout: 10000,
        error: function(){
            alert("Error can't change cam position please verify wifi.");
        }
    });
}