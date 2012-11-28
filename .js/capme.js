$(document).ready(function(){

    function d2h(d) {
        return d.toString(16);
    }

    function h2d (h) {
        return parseInt(h, 16);
    }

    function s2h (tmp) {
        var str = '', i = 0, tmp_len = tmp.length, c;

        for (; i < tmp_len; i += 1) {
            c = tmp.charCodeAt(i);
            str += d2h(c);
        }
        return str;
    }

    function h2s (tmp) {
        var arr = tmp.split(' '), str = '', i = 0, arr_len = arr.length, c;
        for (; i < arr_len; i += 1) {
            c = String.fromCharCode( h2d( arr[i] ) );
            str += c;
        }
        return str;
    }

    // Go

    $(".capme_body").fadeIn(500);


    $(".capme_reset").click(function() {
        $("#capme_form")[0].reset();
    });

    // We will fire if we have enough arguments, otherwise wait for input

    numArgs = $("#numargs").val();

    if (numArgs == 7) {
        reqCap("posted");
    }

    $(".capme_submit").click(function() {
        reqCap("submit");
    });

    function reqCap(caller) {
       
        // Wait until we return before calling again

        theMsg("Sending request..");

        switch (caller) {
            case "posted": sensor  = s2h("00");
                           sid     = s2h("00");
                           break;
            case "submit": sensor  = s2h($("#capme_sid option:selected").data('sensorname'));
                           sid     = s2h($("#capme_sid").val());
                           break;
        }

        sip     = s2h($("#sip").val());
        spt     = s2h($("#spt").val());
        dip     = s2h($("#dip").val());
        dpt     = s2h($("#dpt").val());
        ts      = s2h($("#timestamp").val());
        usr	= s2h($("#username").val());
        pwd     = s2h($("#password").val());

        var urArgs = "d=" + sensor + "-" + sid + "-" + sip + "-" + spt + "-" + dip + "-" + dpt + "-" + ts + "-" + usr + "-" + pwd;

        if (urArgs.indexOf("--") != -1) {
     
            theMsg("Please complete all form fields");

        } else { 

            $(function(){
                $.get(".inc/callback.php?" + urArgs, function(data){cbtx(data)});
            });
                        
            function cbtx(data){
                eval("txRaw=" + data);
                txCMD    = txRaw.cmd;
                txResult = txRaw.tx;

                if (txResult.length > 0) {
                    var txt = '';
                    txt += "<table class=capme_result align=center width=940 cellpadding=0 cellspacing=0>";
                    txt += "<tr>";
                    txt += "<td class=capme_close>";
                    txt += "<span class=capme_close>close</span>";
                    txt += "</td></tr>";
                    txt += "<tr>";
                    txt += "<td class=capme_text>";
                    txt += txResult;
                    txt += "</td></tr></table>";
                    $(".capme_div").after(txt);
                    theMsg("Request was successful");
                    $(".capme_div").hide();
                    $(".capme_result").show();
                } else {
                    theMsg("No result.");
                }

            }
        }
    }

    $(document).on("click", ".capme_close", function() {
        $(".capme_result").remove();
        $(".capme_div").show();
    });

    function theMsg(msg) {
        $(".capme_msg").hide()
        $(".capme_msg").text(msg);
        $(".capme_msg").fadeIn('slow', function() {
            setTimeout(function(){
                $(".capme_msg").fadeOut('slow', function() {
            });
            }, 1500);
        });
    }
});
