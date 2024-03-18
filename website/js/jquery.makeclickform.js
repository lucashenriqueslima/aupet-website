/**********
************/
(function ($) {
    $.fn.makeclickform = function (options) {
        $form = $(this);
        // CRIA UM ELEMENTO QUE TERÁ OS OPTIONS DO FORMULARIO
        var content_options = $('<div></div>').addClass("config_options");
        $form.clickform({
            // FUNÇÃO QUE SERÁ CHAMADA ANTES DO FORMULARIO SER SUBMITADO PELO CLICKFORM
            beforeCall: function($form){
                // CRIA CAMPOS PARA ARMAZENAR OS OPTIONS DO FORM E ENVIAR AO SCRIPT
                $.map(options, function(v,i){
                    var data = makeinput("options["+i+"]", v);
                    content_options.append(data);
                });
                // TODOS CAMPOS POR DEFAULT SENDO OBRIGATÓRIOS, CASO NAO QUEIRA ALGUM VALIDAR UM if(this.id != 'campo_nao_obrigatorio')
                $form.find("input:not([type=hidden]), textarea, select, input.get").each(function(){
                    // ARMAZENA LABEL DO CAMPO
                    var label = makeinput("validate["+this.name+"][label]", typeof this.placeholder !== 'undefined' ? this.placeholder : this.name.charAt(0).toUpperCase() + this.name.slice(1));
                    content_options.append(label);
                    // ARMAZENA OBRIGATORIOS
                    if(typeof options.not_required === 'undefined' || !( this.name in options.not_required ) ){
                        var validate = makeinput("validate["+this.name+"][required]", 1);
                        content_options.append(validate);
                    }
                    // ARMAZENA FUNÇOES DE VALIDAÇOES SETADAS PARA O CAMPO
                    if(typeof options.custom_verify !== 'undefined'){
                        if(this.name in options.custom_verify){
                            if("type" in options.custom_verify[this.name]){
                                var type = makeinput("validate["+this.name+"][type]", options.custom_verify[this.name]["type"]);
                                content_options.append(type); 
                            }else{
                                var type = makeinput("validate["+this.name+"][type]", "attention");
                                content_options.append(type); 
                            }
                            if("call" in options.custom_verify[this.name]){
                                var validate_custom = makeinput("validate["+this.name+"][custom]", options.custom_verify[this.name]["call"]);
                                content_options.append(validate_custom); 
                            }
                            if("message" in options.custom_verify[this.name]){
                                var validate_custom_msg = makeinput("validate["+this.name+"][custom_msg]", options.custom_verify[this.name]["message"]);
                                content_options.append(validate_custom_msg); 
                            }
                            if("data" in options.custom_verify[this.name]){
                                var validate_data = makeinput("validate["+this.name+"][data]", 1);
                                content_options.append(validate_data); 
                            }
                            if("upload" in options.custom_verify[this.name]){
                                var validate_upload = makeinput("validate["+this.name+"][upload]", options.custom_verify[this.name]["upload"]);
                                content_options.append(validate_upload); 
                            }
                        }else{
                            var type = makeinput("validate["+this.name+"][type]", "attention");
                            content_options.append(type); 
                        }
                    }
                    else{
                        var type = makeinput("validate["+this.name+"][type]", "attention");
                        content_options.append(type); 
                    }
                });
                // INSERE OS CAMPOS NO FORMULARIO
                $form.append(content_options);
            }, 
            validateUrl: scripts_path + 'form-atendimentos.php', 
            submitButton: '.clickformsubmit', 
            blockScroll: true, 
            conversionUrl: scripts_path+'_load_form_conversion_script.php?id='+options.conversion_script
        }, function (data) {
            if (data.type == "success") {
                if (data.conversion_script) {
                    var script = stripScripts(data.conversion_script);
                    var f = new Function(script);
                    f();
                    console.log(script);
                }
                $form.each(function () {
                    this.reset();
                });
                $('#popupoverlay_container').popup('hide');
                $("body .cf_errorbox").remove();
                location.reload();
            }
        });
        function makeinput(name, value){
            var input = document.createElement('input');
            input.type = "hidden";
            input.name = name;
            input.value = value;
            return input;
        }
        function stripScripts(s) {
            var div = document.createElement('div');
            div.innerHTML = s;
            var scripts = div.getElementsByTagName('script');
            var i = scripts.length;
            while (i--) {
              scripts[i].parentNode.removeChild(scripts[i]);
            }
            return div.innerHTML;
        }
    }
})(jQuery);