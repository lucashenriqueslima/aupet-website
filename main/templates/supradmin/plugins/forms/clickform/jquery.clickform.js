//v 2.0
(function ($) {
    $.fn.clickform = function (options, callback) {

        var ypos = 0;
        var $styles = {};
        $styles.blocker = {
            "position": "absolute",
            "top": 0,
            "width": "100%",
            "background": "#000000",
            "zIndex": 2090
        };
        $styles.blockerMessage = {
            "position": "absolute",
            "text-align": "center",
            "zIndex": 2090
        };
        $styles.loadingSpan = {
            "display": "inline-block",
            "padding": "15px 20px 15px 50px",
            "background": "url(" + main_template + "images/loaders/circular/010.gif) no-repeat 15px 14px #efefef",
            "border": "1px solid #dddddd",
            "-webkit-border-radius": "5px",
            "-moz-border-radius": "5px",
            "border-radius": "5px",
            "font-family": "Arial, Helvetica, sans-serif",
            "font-size": 14,
            "color": "#a7acae"
        };
        $styles.successSpan = {
            "display": "inline-block",
            "padding": "15px 20px 15px 35px",
            "background": "url(" + main_template + "images/icon_clickform_success.png) no-repeat 12px 17px #6f8450",
            "border": "1px solid #3f561d",
            "-webkit-border-radius": "5px",
            "-moz-border-radius": "5px",
            "border-radius": "5px",
            "font-family": "Arial, Helvetica, sans-serif",
            "font-size": 14,
            "color": "#ffffff"
        };
        $styles.errorSpan = {
            "display": "inline-block",
            "padding": "15px 20px 15px 35px",
            "background": "url(" + main_template + "images/icon_clickform_error.png) no-repeat 12px 17px #b9110d",
            "border": "1px solid #67110f",
            "-webkit-border-radius": "5px",
            "-moz-border-radius": "5px",
            "border-radius": "5px",
            "font-family": "Arial, Helvetica, sans-serif",
            "font-size": 14,
            "color": "#ffffff"
        };
        $styles.attentionSpan = {
            "display": "inline-block",
            "padding": "15px 20px 15px 35px",
            "background": "url(" + main_template + "images/icon_clickform_attention.png) no-repeat 12px 17px #f8d61a",
            "border": "1px solid #57481e",
            "-webkit-border-radius": "5px",
            "-moz-border-radius": "5px",
            "border-radius": "5px",
            "font-family": "Arial, Helvetica, sans-serif",
            "font-size": 14,
            "color": "#896112"
        };
        $styles.messageTipBox = {
            "display": "inline-block",
            "vertical-align": "middle",
            "padding": "5px 8px",
            "background-color": "#b9110d",
            "-webkit-border-radius": 5,
            "-moz-border-radius": 5,
            "border-radius": 5,
            "font-family": "Arial, Helvetica, sans-serif",
            "font-size": 14,
            "color": "#fff",
            "cursor": "pointer"
        };
        $styles.messageTipBoxInner = {
            "width": "100%",
            "margin-top": "-3px",
            "display": "inline-block",
            "vertical-align": "middle",
            "padding": "5px 8px",
            "background-color": "#b9110d",
            "-webkit-border-radius": 5,
            "-moz-border-radius": 5,
            "border-radius": 5,
            "-webkit-border-top-right-radius": 0,
            "-moz-border-radius-topright": 0,
            "border-top-right-radius": 0,
            "-webkit-border-top-left-radius": 0,
            "-moz-border-radius-topleft": 0,
            "border-top-left-radius": 0,
            "font-family": "Arial, Helvetica, sans-serif",
            "font-size": 14,
            "color": "#fff",
            "cursor": "pointer"
        };
        $styles.messageTipBoxArrow = {
            "width": 0,
            "height": 0,
            "margin-left": 20,
            "border": "7px solid",
            "border-color": "transparent",
            "border-top-color": "#b9110d"
        };

        var $messageBoxStruture = "<div class='cf_errorbox' style='display:none;z-index:1000'><div class='cf_message'></div><div class='cf_arrowtip'></div></div>";

        if (options == "update") {

            useTitles($(this));

        } else {

            this.each(function (i) {

                var defaults = {
                    validateUrl: null,
                    submitUrl: null,
                    useTitles: false,
                    activateEnterKey: false,
                    blockScroll: false,
                    clearFields: true,
                    opacity: .7,
                    submitButton: ".clickformsubmit",
                    waitTimeMessage: 2000
                }

                var config = $.extend(defaults, options);
                var $cf = {};
                $cf.config = config;
                $cf.obj = $(this);
                $cf.domFormObj = this;
                $cf.config.submitButton = $cf.obj.find($cf.config.submitButton);


                var $divs = {};
                $divs.blocker = "";
                $divs.blockerMessage = "";

                $cf.divs = $divs;

                if ($cf.config.useTitles)
                    useTitles($cf.obj);
                if (activateEnterKey)
                    activateEnterKey($cf);

                $cf.config.submitButton.on("click", function (e) {
                    e.preventDefault();
                    clearValidationMessage();
                    if ($cf.config.useTitles)
                        clearTitles($cf);

                    $.when(blockTheForm($cf)).then(function (response) {

                        if ($.isFunction($cf.config.validateUrl)) {

                            $.when($cf.config.validateUrl($cf.domFormObj)).then(function (response) {
                                $cf.response = response;
                                if ($cf.response.time != undefined)
                                    $cf.config.waitTimeMessage = $cf.response.time;
                                if (response.type == "validation") {
                                    $.when(unblockTheForm($cf)).then(validateMessage($cf));
                                } else {
                                    sucessErrorAttentionMessage($cf);
                                }
                            });

                        } else {
                            validateUrl($cf);
                        }

                    });

                });

            });
        }

        function clearValidationMessage() {
            $(".cf_errorbox").fadeOut(function () {
                $(this).remove();
            });
        }

        function validateMessage($cf) {

            /*
             * 
             $cf.obj.find("*[name=" + $cf.response.field + "]").before($messageBoxStruture);
             $(".cf_errorbox").css({"position": "absolute"});
             $(".cf_message").css($styles.messageTipBox);
             $(".cf_arrowtip").css($styles.messageTipBoxArrow);
             $(".cf_message").html($cf.response.message);
             $(".cf_errorbox").css({"margin-top": -($(".cf_errorbox").height() - 5)}).fadeIn(function () {
             if (!$cf.config.blockScroll) {
             var o = $(this);
             var offset = o.offset();
             $("html").scrollTop(offset.top - 50);
             }
             });
             if ($cf.config.useTitles)
             useTitles($cf.obj);
             */

            $("span.click-form").remove();
            $("input, textarea, select").css("border-color", "");
            if ($("body, #uniform-" + $cf.response.field).length) {
                $cf.obj.find($("#uniform-" + $cf.response.field)).after("<span class='click-form msg_" + $cf.response.field + "'></span>");
                $cf.obj.find($("#uniform-" + $cf.response.field)).css("border-color", "#b9110d");

                $cf.obj.find($("#" + $cf.response.field)).after("<span class='click-form msg_" + $cf.response.field + "'></span>");
                $cf.obj.find($("#" + $cf.response.field)).css("border-color", "#b9110d");
            } else {
                $cf.obj.find("*[name=" + $cf.response.field + "]").after("<span class='click-form msg_" + $cf.response.field + "'></span>");
                $cf.obj.find("*[name=" + $cf.response.field + "]").css("border-color", "#b9110d");
            }

            $("span.msg_" + $cf.response.field).css($styles.messageTipBoxInner);

            $("span.msg_" + $cf.response.field).html($cf.response.message);

            $("html, body, .main").animate({scrollTop: $("#" + $cf.response.field).offset().top - 120}, 'slow');

            $("span.click-form").css("text-align", "left");

            $("select").not(".select2").next("span.click-form").css("margin-top", "-21px");
            $(".box-header-forms span.click-form").css("margin-top", "-6px");


            $("#" + $cf.response.field).focus();

        }

        function sucessErrorAttentionMessage($cf) {
            $cf.divs.blockerMessage.fadeOut(function () {
                $(this).remove();

                if ($cf.response.type == "success") {

                    if ($cf.response.message == null || $cf.response.message == "" || typeof $cf.response.message === "undefined") {
                        unblockTheForm($cf);
                        if ($.isFunction(callback))
                            callback($cf.response);
                    } else {
                        $cf.divs.blockerMessage = $("<div style=\"display:none;\"><span></span></div>");
                        $cf.divs.blocker.after($cf.divs.blockerMessage);
                        $cf.divs.blockerMessage.find("span").css($styles.successSpan).html($cf.response.message);
                        $cf.divs.blockerMessage.css($styles.blockerMessage).css({"width": "100%"}).fadeTo(01, 0);
                        messageTopPosition($cf);
                        $cf.divs.blockerMessage.fadeTo("meddium", 1);
                        setTimeout(function () {
                            unblockTheForm($cf);
                            if ($.isFunction(callback))
                                callback($cf.response);
                        }, $cf.config.waitTimeMessage);
                        if ($cf.config.useTitles)
                            useTitles($cf.obj);
                    }

                    if ($cf.config.submitUrl != null) {
                        $cf.obj.attr("action", $cf.config.submitUrl);
                        $cf.obj.attr("target", "_top").submit();
                    }
                    if ($cf.config.clearFields) {
                        $cf.obj.find("input[type=text], textarea").val("");
                        $cf.obj.find("select").find("option:eq(0)").attr("selected", "selected");
                        $cf.obj.find("input[type=checkbox], input[type=radio]").removeAttr("checked");
                    }


                } else {
                    $("input[type=text]").css("border-color", "");
                    $("input[type=password]").css("border-color", "");
                    $("span.click-form").remove();


                    $cf.divs.blockerMessage = $("<div style=\"display:none;\"><span></span></div>");
                    $cf.divs.blocker.after($cf.divs.blockerMessage);
                    switch ($cf.response.type) {
                        case "error":
                            $cf.divs.blockerMessage.find("span").css($styles.errorSpan).html($cf.response.message);
                            break;
                        case "attention":
                            $cf.divs.blockerMessage.find("span").css($styles.attentionSpan).html($cf.response.message);
                            break;
                    }

                    $cf.divs.blockerMessage.css($styles.blockerMessage).css({"width": "100%"}).fadeTo(01, 0);
                    messageTopPosition($cf);
                    $cf.divs.blockerMessage.fadeTo("meddium", 1);

                    setTimeout(function () {
                        unblockTheForm($cf);
                        if ($.isFunction(callback))
                            callback($cf.response);
                    }, $cf.config.waitTimeMessage);

                    if ($cf.config.useTitles)
                        useTitles($cf.obj);
                }

            });


            $("html, body, .main").animate({scrollTop: $("#" + $cf.response.field).offset().top - 120}, 'slow');

        }

        function blockTheForm($cf) {
            $cf.divs.blockerMessage = $("<div id='div-msg' style=\"display:none;\"><span id='span-msg'><a style='color:#a7acae; text-decoration:none' href='" + document.URL + "'>aguarde carregando</a></span></div>").prependTo("body");
            $cf.divs.blockerMessage.find("span").css($styles.loadingSpan);
            $cf.divs.blockerMessage.css($styles.blockerMessage).css({"width": "100%"}).fadeTo(01, 0);
            messageTopPosition($cf);
            $cf.divs.blockerMessage.fadeTo("meddium", 1);

            $cf.divs.blocker = $("<div style=\"display:none;\"></div>").prependTo("body");
            $cf.divs.blocker.css($styles.blocker).css({"width": $(document).width(), "height": $(document).height()}).fadeTo(01, 0);
            //if(!$cf.config.blockScroll) $("html").scrollTop(parseInt($cf.divs.blockerMessage.css("top").replace("px",""))+($cf.divs.blocker.height()*.5)-$cf.divs.blockerMessage.height()-30);

            $(window).scroll(function () {
                messageTopPosition($cf);
            });

            return $cf.divs.blocker.fadeTo("meddium", $cf.config.opacity);

        }

        function unblockTheForm($cf) {

            $cf.divs.blockerMessage.fadeOut(function () {
                $(this).remove();
                $cf.divs.blocker.remove();
            });

            $("body").css({"overflow": "auto"});
            return $cf.divs.blocker.fadeOut();

        }

        function messageTopPosition($cf) {
            ypos = ($(window).height() * .5) - ($cf.divs.blockerMessage.outerHeight()) + $(document).scrollTop();
            if (ypos < 0)
                ypos = 0;
            $cf.divs.blockerMessage.css({"top": ypos});
        }

        function useTitles(form) {

            $(form).find("input[title], textarea[title]").each(function () {
                if ($(this).val() == "") {
                    $(this).val($(this).attr("title"));
                }
                $(this).unbind("focus");
                $(this).unbind("blur");
                $(this).focus(function () {
                    if ($(this).val() == $(this).attr("title")) {
                        $(this).val("");
                    }
                })
                        .blur(function () {
                            if ($(this).val() == "") {
                                $(this).val($(this).attr("title"));
                            }
                        });
            });

            $(form).find("input[type=password]").not("input.pwdclickformfocus").each(function () {
                $(this).after($(this).clone().attr("type", "text").addClass("pwdclickform"));
                $(this).remove();
                useTitles(form);
            });

            $(form).find("input.pwdclickform").each(function () {
                $(this).unbind("focus");
                $(this).unbind("blur");
                $(this).focus(function () {
                    if ($(this).val() == $(this).attr("title")) {
                        $(this).after($(this).clone().attr("type", "password").removeClass("pwdclickform").addClass("pwdclickformfocus"));
                        $(this).remove();
                        $("input.pwdclickformfocus").val("").trigger("focus");
                        $("input.pwdclickformfocus").unbind("blur");
                        $("input.pwdclickformfocus").blur(function () {
                            if ($(this).val() == "") {
                                $(this).removeClass("pwdclickformfocus");
                                useTitles(form);
                            }
                        });
                    }
                })
            });

        }

        function clearTitles($cf) {
            $cf.obj.find("input[title], textarea[title]").each(function () {
                if ($(this).val() == $(this).attr("title")) {
                    $(this).val("");
                }
            });
        }

        function activateEnterKey($cf) {
            /*
             $cf.obj.find("input[type=text]").keypress(function (event) {
             if (event.keyCode == 13 && $("input[name='" + $(this).attr('name') + "']").val() != "") {
             var index = $cf.obj.find("input").index(this) + 1;
             $cf.obj.find("input").eq(index).focus();
             event.preventDefault();
             }
             })
             */
            $cf.obj.find("input[type=text],input[type=password]").keypress(function (event) {
                if (event.keyCode == 13) {
                    $cf.config.submitButton.trigger("click");
                    event.preventDefault();
                }
            });
        }

        function validateUrl($cf) {
            $("<iframe src='javascript:;' name='clickformiframe' id='clickformiframe' style='display:none;'></iframe>").appendTo("body");
            $cf.obj.attr({"action": $cf.config.validateUrl, "target": "clickformiframe"}).submit();
            return loadingUrl($cf);
        }

        function loadingUrl($cf) {
            var iframe = document.getElementById("clickformiframe");
            /*
             if($.browser.msie){
             if(iframe.document.readyState != "complete"){
             return setTimeout(function(){loadingUrl($cf)}, 500);
             }else{
             return returnValidated($cf);
             }
             }else {
             return iframe.onload = function(){returnValidated($cf);};
             }
             */
            return iframe.onload = function () {
                returnValidated($cf);
            };
        }

        function returnValidated($cf) {
            $cf.response = $.parseJSON($("iframe[name='clickformiframe']").contents().children().find("body").text());
            if(!$cf.response) $cf.response = defaut_response;
            
            if ($cf.response.time != undefined)
                $cf.config.waitTimeMessage = $cf.response.time;
            if ($cf.response.type == "validation") {
                $.when(unblockTheForm($cf)).then(validateMessage($cf));
            } else {
                sucessErrorAttentionMessage($cf);
            }
            $("iframe[name='clickformiframe']").remove();
        }
    };
})(jQuery);