function relPerson(options) {
    //função de relacionamento para pessoas
    $(document).on("click", ".btn-person", function () {
        var $object = '';
        bootbox.prompt({
            title: "Verifique se existe cadastro!",
            inputType: 'text',
            classe: 'search-person',
            placeholder: "Busque por nome, documento, telefone, e-mail...",
            className: "modal-person",
            callback: function (result) {
                if (result && $idPessoa) {
                    if (typeof store.get('warning-rel-person') == "undefined" || store.get('warning-rel-person') == 0) {
                        swal({
                            title: 'Atenção',
                            text: "Lembre-se de salvar para concretizar o relaciomento !!!",
                            type: "warning",
                            inputType: 'checkbox',
                            closeOnConfirm: false,
                            confirmButtonText: "Ok, entendi!",
                            customClass: "swal-person"
                        },
                            function (isConfirm) {
                                if (isConfirm) {
                                    post('', { 'id_pessoa': $idPessoa }, 'post', 0);
                                }
                            });
                        $(".swal-person").find('button:last').before("<input type='checkbox' id='warning-rel-person' name='warning-rel-person' class='control-label' value='1'><label class='control-label pointer' for='warning-rel-person' style='margin:0;padding:0'>Não me lembrar mais!</label><br>");
                    } else {
                        post('', { 'id_pessoa': $idPessoa }, 'post', 0);
                    }
                    uniform();
                } else {
                    $.gritter.add({
                        title: 'Atenção !!!',
                        text: 'Nenhuma pessoa foi selecionada !!!',
                        time: '',
                        close_icon: 'entypo-icon-cancel s12',
                        icon: 'cut-icon-alert  ',
                        class_name: 'warning-notice'
                    });
                }
            }
        });
        $searchAction = '';
        $(document).on("keyup", ".modal-person .search-person", function (e) {
            $(".loading-action").remove();
            $("#pessoaId").select2('destroy');
            $("#pessoaId").remove();
            var $search = $(this).val();
            var $this = $(this);
            $this.after('<img class="loading-action" style="margin-top:20px" src="' + main_template + 'images/loaders/horizontal/006.gif">');
            clearTimeout($searchAction);
            $searchAction = setTimeout(function () {
                jQuery.ajax({
                    url: 'main/general/getPeople',
                    type: "POST",
                    cache: false,
                    dataType: "json",
                    data: { search: $search, tabela: options.tabela },
                    success: function (data) {
                        $(".loading-action").remove();
                        if (data.status) {
                            $("#pessoaId").remove();
                            $this.after('<select id="pessoaId" class="form-control" style="margin-top:10px"></select></div></div>');
                            $("#pessoaId").hide();
                            var $options = '<option value="">Selecione...</option>';
                            for (var i = 0; i < data.rows.info.length; i++) {
                                $options += '<option value="' + data.rows['id'][i] + '">' + data.rows['info'][i] + '</option>'
                            }
                            $("#pessoaId").fadeTo("normal", 1, function () {
                                var select2 = $("#pessoaId").html($options).select2();
                                select2.select2('search');
                            });
                        } else {
                            $this.after('<div id="pessoaId" style="margin-top:20px">Nenhum resultado foi encontrado!</div>');
                        }
                    }, complete: function () {
                        uniform();
                    }
                });
            }, 700);
        });
    });
    var $idPessoa = '';
    $(document).on("change", "#pessoaId", function (e) {
        $idPessoa = $(this).val();
    });
    $(document).on("click", "#warning-rel-person", function (e) {
        var $val = 0;
        if ($(this).is(":checked")) $val = 1;
        store.set('warning-rel-person', $val);
    });
    $(document).on("change", "input[type=radio][name=tipo]", function () {
        if (this.value == "#000000018") {
            $("#nome, #sobrenome, #nascimento").closest(".form-group").show();
            $("#razao_social, #nome_fantasia").closest(".form-group").hide();
        } else if (this.value == "#000000019") {
            $("#razao_social, #nome_fantasia").closest(".form-group").show();
            $("#nome, #sobrenome, #nascimento").closest(".form-group").hide();
        }
    });
    if ($("input[type=radio][name=tipo]:checked").length > 0)
        $("input[type=radio][name=tipo]:checked").trigger("change");
}
function post(path, params, method, time) {
    //função para simular um post via javascript
    method = method || "post";
    time = time ? time : 0;
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    for (var key in params) {
        if (params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
        }
    }
    document.body.appendChild(form);
    setTimeout(function () {
        form.submit();
    }, time);
}
function getHour(min) {
    //retorna hh:mm à partir de uma qtd total de minutos
    if (!min)
        return "00:00";
    var hours = Math.floor(min / 60).toString();
    if (hours.length == 1)
        hours = "0" + hours;
    var minutes = (min % 60).toString();
    if (minutes.length == 1)
        minutes = "0" + minutes;
    return hours + ":" + minutes;
}
//formata valor para Real
function formatReal(numero) {
    var numeroStr = '';
    numeroStr = numero.toString();
    if (numero - (Math.round(numero)) == 0) {
        numeroStr = numeroStr + '00';
        numeroStr = numeroStr.replace(/(\d{1})(\d{8})$/, "$1.$2");  //coloca ponto antes dos últimos 8 digitos
        numeroStr = numeroStr.replace(/(\d{1})(\d{5})$/, "$1.$2");  //coloca ponto antes dos últimos 5 digitos        
        numeroStr = numeroStr.replace(/(\d{1})(\d{1,2})$/, "$1,$2");	//coloca virgula antes dos últimos 2 digitos
        return numeroStr;
    }
    var parteDecial = numeroStr.slice(numeroStr.indexOf('.'), numeroStr.length);
    if (parteDecial.length == 2) {
        parteDecial = parteDecial + '0';
    }
    parteDecial = parteDecial.replace('.', ',');
    var parteInteira = numeroStr.slice(0, numeroStr.indexOf('.'));
    var vetorParteInteira = [];
    for (var i = 0; i < parteInteira.length; i++) {
        vetorParteInteira.push(parteInteira.slice(i, i + 1));
    }
    console.log(vetorParteInteira);
    var parteInteiraFinal = '';
    var comprimento = vetorParteInteira.length - 1;
    for (var i = 0; i < vetorParteInteira.length; i++) {
        if (((((comprimento - i) + 1) / 3) - (Math.floor((((comprimento - i) + 1) / 3)))) == 0 && (((comprimento - i) + 1) != vetorParteInteira.length)) {
            parteInteiraFinal = parteInteiraFinal + '.' + vetorParteInteira[i];
        } else {
            parteInteiraFinal = parteInteiraFinal + vetorParteInteira[i];
        }
    }
    var valorFinalCorrigido = parteInteiraFinal + parteDecial;
    return valorFinalCorrigido;
}
var lista_del = []; //array com ids a serem deletados
$(document).ready(function (e) {
    bootbox.setDefaults({ locale: "br" });
    if (pagina == "main/group" || pagina == "main/user") {
        //##########  PAGINA GRUPOS DE USUÁRIOS
        //###################################### ############################### ##########################
        $("#ler_todas").change(function () {
            if ($(this).is(":checked")) {
                $("input.leitura").attr("checked", "checked");
            } else {
                $("input.leitura").removeAttr("checked");
            }
        });
        $("#gravar_todas").change(function () {
            if ($(this).is(":checked")) {
                $("input.gravacao").attr("checked", "checked");
            } else {
                $("input.gravacao").removeAttr("checked");
            }
        });
        $("#editar_todas").change(function () {
            if ($(this).is(":checked")) {
                $("input.edicao").attr("checked", "checked");
            } else {
                $("input.edicao").removeAttr("checked");
            }
        });
        $("#excluir_todas").change(function () {
            if ($(this).is(":checked")) {
                $("input.exclusao").attr("checked", "checked");
            } else {
                $("input.exclusao").removeAttr("checked");
            }
        });
        //permissões individuais
        $("#pi").change(function () {
            if ($(this).is(":checked")) {
                $(".pi").slideToggle("fast");
                $("html, body, .main").animate({ scrollTop: $("#pi").offset().top - 130 }, 1000);
            } else {
                $(".pi").slideToggle("fast");
                $("html, body, .main").animate({ scrollTop: $("#pi").offset().top - 130 }, 1000);
            }
        });
        //permissões individuais de serviços
        $("#pi2").change(function () {
            if ($(this).is(":checked")) {
                $(".pi2").slideToggle("fast");
                $("html, body, .main").animate({ scrollTop: $("#pi2").offset().top - 130 }, 1000);
            } else {
                $(".pi2").slideToggle("fast");
                $("html, body, .main").animate({ scrollTop: $("#pi2").offset().top - 130 }, 1000);
            }
        });
        $("body").on("click", ".esta_funcao", function () {
            var base = $(this);
            if (base.is(":checked")) {
                base.parents("tr").find("input").prop("checked", true);
            } else {
                base.parents("tr").find("input").prop("checked", false);
            }
        });
    }
    $('.select-all').attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);
    // reset panel position for page
    $('.admin-logout').click(function (e) {
        e.preventDefault();
        bootbox.setDefaults({ locale: "sim" });
        bootbox.confirm({
            message: "Deseja realmente sair do sistema ?",
            title: "Aviso!!!",
            className: "modal-style2",
            callback: function (result) {
                if (result) {
                    window.location.href = "main/login/sair";
                }
            }
        });
        plugin.centerModal();
    });
    //OCULTAR MENSAGEM DE VALIDAÇÃO DO FORMULÁRIO
    $('body').on('click', 'span.click-form', function () {
        $("input, textarea, select").css("border-color", "#ccc");
        $("span.click-form").hide();
    });
    /* verifica se informação existe em um array, retorna true se existir, caso contrário retorna false */
    Array.prototype.inArray = function (value) {
        var i;
        for (i = 0; i < this.length; i++) {
            if (this[i] == value) {
                return true;
            }
        }
        return false;
    };
    $(".google_analytics").clickform({ 'validateUrl': "controllers/" + $("#controller_ga").val() + '.php', 'submitButton': '.bt_google_analytics', 'blockScroll': true }, function (data) {
        if (data.type == "success") {
            window.location.reload();
        }
    });
    /*
     * INÍCIO DA ROTINA DELL ALL
     */
    //CRIA LISTA COM TODOS REGISTROS A SEREM APAGADOS
    //oculta por padrão o botão "Apagar registros selecionados"
    $(".fixed_options_footer, .fixed_option_space,  .del-all").hide();
    //marca/desmarca todos registros apresentados na página, para serem apagados
    $("#apagar_todos").change(function () {
        if ($(this).is(":checked")) {
            $("#apagar_geral").removeAttr("checked");
            $(".registros-geral").val(0);
            $("input.del-this").attr("checked", "checked");
        } else {
            $("input.del-this").removeAttr("checked");
        }
        percorreDelThis();
    });
    function percorreDelThis() {
        //percorre todos inputs da classe del-this para ler seus valores e lançá-los na input del-registros do form que solicitará apagá-los
        $("input.del-this").each(function () {
            var id = $(this).attr("id");
            var valor = $(this).val();
            //caso a input esteja selecionado, seu valor é gravado no array lista_del
            //caso contrário, remove o valor do array lista_del
            if ($("#" + id).is(':checked')) {
                if (!lista_del.inArray(valor)) {
                    lista_del.push(valor);
                }
            } else {
                var index = lista_del.indexOf(valor);
                lista_del.splice(index, 1);
            }
            //caso o array lista_del tenha algum id preechido, mostra o botão "Apagar registros selecionados"
            //caso contrário, ele fica oculto
            if (lista_del != "") {
                $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeIn();
                if ($(".registros-geral").val() != 1) {
                    $("#apagar_todos").attr("checked", "checked");
                }
                //caso o controller seja diferente do controller padrão da página, o valor de data-controller é 
                //lançado no input data-controller do form que solicita apagar os registros
                $(".data-controller").val($(this).attr("data-controller"));
                $(".data-id-lista").val($(this).attr("data-id-lista"));
                $(".data-id-segmentacao").val($(this).attr("data-id-segmentacao"));
            } else {
                if ($(".registros-geral").val() != 1) {
                    $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
                    $("#apagar_todos").removeAttr("checked");
                    $(".data-controller").val("");
                    $(".data-id-lista").val("");
                    $(".data-id-segmentacao").val("");
                }
            }
            //com o array formado, seu valor é lançado na input do form que solicitará apagar os registros
            $(".del-registros").val(lista_del);
        });
    }
    $("#apagar_geral").change(function () {
        if ($(this).is(":checked")) {
            $("#apagar_todos").removeAttr("checked");
            $(".registros-geral").val(1);
            $(".del-registros").val("");
            $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeIn();
            $(".data-controller").val($(this).attr("data-controller"));
            $(".data-id-lista").val($(this).attr("data-id-lista"));
            $(".data-id-segmentacao").val($(this).attr("data-id-segmentacao"));
            $("input.del-this").attr("checked", "checked");
            percorreDelThis();
        } else {
            $("#apagar_todos").removeAttr("checked");
            $(".registros-geral").val(0);
            $(".del-registros").val("");
            $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
            $(".data-controller").val("");
            $(".data-id-lista").val("");
            $(".data-id-segmentacao").val("");
            $("input.del-this").removeAttr("checked");
        }
        uniform();
    });
    //CRIA LISTA PARA APAGAR REGISTROS SELECIONADOS MANUALMENTE                               
    $('body').on('change', '.del-this', function (e) {
        e.preventDefault();
        if ($(".registros-geral").val() == 1) {
            $(".registros-geral").val(0);
            $("#apagar_geral").removeAttr("checked");
            uniform();
        }
        var id = this.id;
        var valor = this.value;
        //caso o registro seja marcado, a id desse registro é lançado no array lista_del
        //caso contrário, seu id é removido do array lista_del
        if ($("#" + id).is(':checked')) {
            lista_del.push(valor);
        } else {
            var index = lista_del.indexOf(valor);
            lista_del.splice(index, 1);
            $("#apagar_todos").removeAttr("checked");
            uniform();
        }
        //caso o array lista_del esteja preenchido, o botão "Apagar registros selecionados" aparece
        //caso contrário, ele se oculta
        if (lista_del != "") {
            $(".fixed_options_footer, .fixed_option_space, .del-all").fadeIn();
            //caso o controller seja diferente do controller padrão da página, o valor de data-controller é 
            //lançado no input data-controller do form que solicita apagar os registros
            $(".data-controller").val($(this).attr("data-controller"));
            $(".data-id-lista").val($(this).attr("data-id-lista"));
            $(".data-id-segmentacao").val($(this).attr("data-id-segmentacao"));
        } else {
            $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
            $(".data-controller").val("");
            $(".data-id-lista").val("");
            $(".data-id-segmentacao").val("");
            $("#apagar_todos").removeAttr("checked");
            uniform();
        }
        //com o array formado, seu valor é lançado na input do form que solicitará apagar os registros
        $(".del-registros").val(lista_del);
    });
    //AÇÃO DO FORM QUE SOLICITA APAGAR TODOS REGISTROS SELECIONADOS PELO USUÁRIO
    $('body').on('click', '.bt_system_delete_all', function (e) {
        e.preventDefault();
        bootbox.setDefaults({ locale: "sim" });
        bootbox.confirm({
            message: "Deseja realmente apagar todos registros selecionados ?",
            title: "Aviso!!!",
            className: "modal-style2",
            callback: function (result) {
                if (result) {
                    var registros = $(".del-registros").val().split(','); //valor vindo da input del-registros, array do lista_del
                    var ids = []; //aqui será guardado as ids dos registros que não foram possíveis de serem apagados
                    var mensagem = null; //mensagem, caso exita, a ser apresentado como alert
                    //caso exista um controller alternativo especificado, ele substituirá o controller padrão da página
                    var controle = $(".data-controller").val();
                    var lista = $(".data-id-lista").val();
                    var segmentacao = $(".data-id-segmentacao").val();
                    var filtro_segmentacoes = $("#segmentacoes").val();
                    var filtro_segmentacoes_agrupar = $("#segmentacoes_agrupar").val();
                    var filtro_listas = $("#listas").val();
                    var filtro_pesquisa = $(".dataTables_filter input").val();
                    if (controle)
                        controller = controle;
                    else
                        controller = pagina + "/del";
                    var apagar_geral = $(".registros-geral").val();
                    if (apagar_geral != 1) {
                        //laço com método post ajax que enviará ao controller os registros que deverão ser apagados
                        for (var i = 0; i < registros.length; i++) {
                            jQuery.ajax({
                                url: system_path + controller + '/id/' + registros[i] + '/lista/' + lista,
                                type: 'POST',
                                data: { segmentacao: segmentacao, apagar_geral: apagar_geral, filtro_segmentacoes: filtro_segmentacoes, filtro_segmentacoes_agrupar: filtro_segmentacoes_agrupar, filtro_listas: filtro_listas, filtro_pesquisa: filtro_pesquisa },
                                async: false,
                                jsonp: false,
                                success: function (dt) {
                                    //caso o controller emita a mensagem "error", significa que o registro não pôde ser apagado 
                                    //por ter vinculo com outro registro, nesse caso sua id é adicionada ao array ids
                                    if (dt.indexOf("error") != -1) {
                                        ids.push(registros[i]);
                                    }
                                },
                                complete: function () {
                                }
                            });
                        }
                        //caso todos registros tenha dado erro ao apagar
                        if (ids.length == i)
                            mensagem = "Nenhum registro selecionado foi apagado. Verifique se não estão vinculados a outros registros";
                        //caso tenha dado erro ao apagar 1 registro e a quantidade de erro é menor que todos itens solicitados para ser apagados
                        else if (ids.length == 1 && ids.length < i)
                            mensagem = "Não foi possivel apagar o registro de id (" + ids + ").\n Verifique se ele não está vinculado a outro registro.";
                        //caso tenha dado erro ao apagar 2 ou mais registros e a quantidade de erro é menor que todos itens solicitados para ser apagados    
                        else if (ids.length > 1 && ids.length < i)
                            mensagem = "Não foi possivel apagar os registros de ids (" + ids + ").\n Verifique se eles não estão vinculados a outro registro.";
                        //caso uma mensagem tenha sido criada
                        if (mensagem != null)
                            alert(mensagem);
                        //a página só será atualizada caso o número de erros seja diferente do número de solicitações
                        if (ids.length != i)
                            window.location.reload();
                    } else {
                        jQuery.ajax({
                            url: system_path + controller + '/del/id/' + registros[i] + '/lista/' + lista,
                            type: 'POST',
                            data: { segmentacao: segmentacao, apagar_geral: apagar_geral, filtro_segmentacoes: filtro_segmentacoes, filtro_segmentacoes_agrupar: filtro_segmentacoes_agrupar, filtro_listas: filtro_listas, filtro_pesquisa: filtro_pesquisa },
                            async: true,
                            jsonp: true,
                            success: function (dt) {
                                //caso o controller emita a mensagem "error", significa que o registro não pôde ser apagado 
                                //por ter vinculo com outro registro, nesse caso sua id é adicionada ao array ids
                                if (dt.indexOf("error") != -1) {
                                    alert("Não foi possivel apagar os registros.\n Verifique se eles não estão vinculados a outro registro.");
                                }
                            },
                            complete: function () {
                                window.location.reload();
                            }
                        });
                    }
                }
            }
        });
    });
    /*
     * TERMINA AQUI A ROTINA DELL ALL
     */
    // STATUS
    //------------------------------------------------------------------
    $('body').on('click', '.bt_system_stats', function (e) {
        var $this = $(this);
        var db = ($this.data("db")) ? $this.data("db") : "";
        $.post("main/general/status", { "t": $(this).attr('data-table'), "a": $(this).attr("data-action"), "i": $(this).attr("data-id"), "p": $(this).attr("data-permit"), "db": db }, function (data) {
            if (data == "ativar") {
                $this.html('<img src="' + main_template + 'images/status_vermelho.png" alt="Ativar">');
                $this.attr("data-action", "ativar");
            } else if (data == "desativar") {
                $this.html('<img src="' + main_template + 'images/status_verde.png" alt="Ativar">');
                $this.attr("data-action", "desativar");
            } else {
                alert(data);
            }
        });
    });
    // APAGAR
    //------------------------------------------------------------------
    $('body').on('click', '.bt_system_delete', function (e) {
        var del = $(this).attr('data-del');
        var controle = $(this).attr("data-controller");
        var lista = $(this).attr("data-id-lista");
        var id = $(this).attr("data-id");
        var retorno = $(this).attr("data-retorno");
        var segmentacao = $(this).attr("data-id-segmentacao");
        bootbox.setDefaults({ locale: "sim" });
        bootbox.confirm({
            message: "Deseja realmente apagar esse registro ?",
            title: "Aviso!!!",
            className: "modal-style2",
            callback: function (result) {
                if (result) {
                    if (typeof del == 'undefined')
                        del = 'del';
                    $.post(system_path + controle + '/' + del + '/id/' + id + '/lista/' + lista, { 'retorno': retorno, "segmentacao": segmentacao }, function (response) {
                        if (response.indexOf("error") != -1) {
                            alert("Não foi possivel apagar este registro.\n Verifique se ele não está vinculado a outro registro.");
                        } else {
                            window.location.href = response;
                        }
                    });
                }
            }
        });
    });
    if ($("form[name=main]").length > 0) {
        $("form[name=main]").clickform({ "validateUrl": controller }, function (data) {
            if (data.type == "success") {
                if (typeof data.retorno != 'undefined')
                    window.location.href = data.retorno;
                else
                    window.location.href = retorno;
            } else if (typeof data.login != 'undefined') {
                var popup = document.createElement("div");
                popup.setAttribute("id", "popup_login");
                popup.innerHTML = data.content;
                $(document.body).append(popup);
            }
        });
    }
    //REORDENAR 
    //------------------------------------------------------------------
    if ($("table.sortable").length > 0) {
        var order = '';
        var sortable_array = [];
        $(".sortable_save .sortablesubmit").live("click", function () {
            var id = this.id;
            if (id != "")
                controller = id;
            if (typeof order != "undefined" && order != "") {
                order = order;
            } else {
                order = controller + "/order";
            }
            $("table.sortable").fadeTo(500, 0.5, function () {
                $.post(system_path + order, { "array": sortable_array }, function (data) {
                    window.location.reload();
                });
            });
        });
        $("table.sortable").sortable({
            opacity: 0.7, cursor: 'move', axis: 'y', items: "tbody tr", update: function (e, ui) {
                sortable_array = $(this).sortable("toArray");
                order = $(this).find(".order").attr("data-order");
                $(".fixed_options_footer, .fixed_option_space, .sortable_save").show();
            }
        });
    }
    // BUSCA CIDADES
    //------------------------------------------------------------------
    if ($(".getcidades").length) {
        $(".getcidades").change(function () {
            if ($(this).val() === parseInt($(this).val())) {
                var id = $(this).val();
            } else {
                var id = $(this).find("option:selected").attr("data-id");
            }
            $("select[name=cidade]").load("views_ajax/utils-cidades.php?id=" + id, function () {
                $("select[name=cidade]").select2();
            });
        });
    }
    //###################################### ############################### ##########################
    // ALTERAÇÃO DE SERVIÇOS
    $(".services a").on("click", function (e) {
        e.preventDefault();
        $.post("main/general/setCurrentService", { id: $(this).attr("data-id") }, function () {
            location.replace("");
        });
    });
    $("[type=file]").change(function () {
        readImage(this);
    });

    function maskTelefone(campo) {
        $(campo).mask("(##) ##########");
        var $telefone = $(campo).val();
        var $total = $telefone.length;
        if ($total >= 13) {
            var $last_telefone = "-" + $telefone.substr(-4);
            var $new_telefone = $telefone.substring(0, ($telefone.length - 4));
            $(campo).val($new_telefone + $last_telefone);
        }
    }
    $("body").on("keypres keyup blur", "[mask-telefone]", function () {
        maskTelefone($(this));
    });
    $('[mask-cep]').mask('#####-###');
    $('[mask-cpf]').mask('000.000.000-00');
    $("[date-picker]").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'
    }).mask('##/##/####');
    $("[mask-hora]").mask('##:##', { placeholder: "__:__" });
    $("[mask-hora]").attr("placeholder", "__:__");
    $("[date-picker]").attr("placeholder", "__/__/____");
});
function readImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $(input.parentElement.children[0].children[0]).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
//plugin pra uniformizar checkbox e radios pra v2
function uniform() {
    $("[data-toggle=tooltip]").tooltip({ container: 'body' });
    $(".tip").tooltip({ placement: 'top', container: 'body' });
    $(".tipR").tooltip({ placement: 'right', container: 'body' });
    $(".tipB").tooltip({ placement: 'bottom', container: 'body' });
    $(".tipL").tooltip({ placement: 'left', container: 'body' });
    var chboxes = $('input[type=checkbox]');
    var radios = $('input[type=radio]');
    chboxes.each(function (index) {
        if (typeof $(this).data('class') == "undefined") {
            chboxClass = "checkbox-custom";
        } else {
            chboxClass = $(this).data('class');
        }
        if (typeof $(this).attr('id') == "undefined") {
            chboxId = "chbox" + index;
            $(this).attr('id', chboxId);
        } else {
            chboxId = $(this).attr('id');
        }
        if (typeof $(this).data('label') == "undefined") {
            chboxLabeltxt = "";
        } else {
            chboxLabeltxt = $(this).data('label');
        }
        if (!$(this).parent().hasClass(chboxClass) && !$(this).parent().hasClass('toggle')) {
            $(this).wrap('<div class="' + chboxClass + ' noStyleInputCheckbox">');
            $(this).parent().append('<label for="' + chboxId + '">' + chboxLabeltxt + '</label>');
        }
    });
    radios.each(function (index) {
        if (typeof $(this).data('class') == "undefined") {
            radioClass = "radio-custom";
        } else {
            radioClass = $(this).data('class');
        }
        if (typeof $(this).attr('id') == "undefined") {
            radioId = "radio" + index;
            $(this).attr('id', radioId);
        } else {
            radioId = $(this).attr('id');
        }
        if (typeof $(this).data('label') == "undefined") {
            radioLabeltxt = "";
        } else {
            radioLabeltxt = $(this).data('label');
        }
        if (!$(this).parent().hasClass(radioClass) && !$(this).parent().hasClass('toggle')) {
            $(this).wrap('<div class="' + radioClass + ' noStyleInputCheckbox">');
            $(this).parent().append('<label for="' + radioId + '">' + radioLabeltxt + '</label>');
        }
    });
}
function tinymceInit(config = {}) {
    var defaults = {
        upload: true,
        css: 'tinymce.css',
        galeria: false,
        notificacao: false,
        cadastrante: false,
        selector: "textarea.tinymce",
        mode: "textareas",
        theme: "advanced",
        force_br_newlines: false,
        force_p_newlines: false,
        forced_root_block: false,
    }
    var config = $.extend(defaults, config);
    var cadastrantes = '';
    var galerias = "";
    var notificacao_nome = "";
    var notificacao_assunto = "";
    cssFiles = [root_path + "main/templates/supradmin/css/tinymce.css"];
    if (config.checkitem) {
        cssFiles.push(root_path + "main/templates/supradmin/css/checkitem.css")
    }
    init_object = {
        entity_encoding: "raw",
        selector: config.selector,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste",
            "textcolor"
        ],
        valid_elements: '*[*]',
        toolbar: "forecolor backcolor",
        textcolor_map: [
            "000000", "Black",
            "993300", "Burnt orange",
            "333300", "Dark olive",
            "003300", "Dark green",
            "003366", "Dark azure",
            "000080", "Navy Blue",
            "333399", "Indigo",
            "333333", "Very dark gray",
            "800000", "Maroon",
            "FF6600", "Orange",
            "808000", "Olive",
            "008000", "Green",
            "008080", "Teal",
            "666699", "Grayish blue",
            "808080", "Gray",
            "FF0000", "Red",
            "FF9900", "Amber",
            "99CC00", "Yellow green",
            "339966", "Sea green",
            "33CCCC", "Turquoise",
            "3366FF", "Royal blue",
            "800080", "Purple",
            "999999", "Medium gray",
            "FF00FF", "Magenta",
            "FFCC00", "Gold",
            "FFFF00", "Yellow",
            "00FF00", "Lime",
            "00FFFF", "Aqua",
            "00CCFF", "Sky blue",
            "993366", "Red violet",
            "FFFFFF", "White",
            "FF99CC", "Pink",
            "FFCC99", "Peach",
            "FFFF99", "Light yellow",
            "CCFFCC", "Pale green",
            "CCFFFF", "Pale cyan",
            "99CCFF", "Light sky blue",
            "CC99FF", "Plum",
            "28589e", "Azul Site",
            "ede5bb", "Cinza Claro Site"
        ],
        setup: function (editor) {
            if (config.galeria) {
                galerias = {
                    text: 'Galeria de Fotos', onclick: function () {
                        editor.insertContent('[({GALERIA})]');
                    }
                }
            }
            if (config.cadastrante) {
                cadastrantes = {
                    text: 'Cadastrante', onclick: function () {
                        editor.insertContent('[({CADASTRANTE})]');
                    }
                }
            }
            if (config.notificacao) {
                notificacao_nome = {
                    text: 'Nome', onclick: function () {
                        editor.insertContent('[({NOME})]');
                    }
                };
                notificacao_assunto = {
                    text: 'Assunto', onclick: function () {
                        editor.insertContent('[({ASSUNTO})]');
                    }
                };
            }
            editor.addButton('iserir', {
                type: 'menuButton',
                text: 'Inserir',
                icon: false,
                menu: [
                    cadastrantes,
                    galerias,
                    notificacao_nome,
                    notificacao_assunto
                ]
            });
            editor.addButton('customiza', {
                type: 'menuButton',
                text: 'Customização',
                icon: false,
                menu: [
                    {
                        text: 'Bloco Separador', onclick: function () {
                            editor.insertContent('<div class="clear"></div>');
                        }
                    }
                ]
            });
            editor.addButton('imgAlign', {
                type: 'menuButton',
                text: 'Imagem',
                icon: false,
                menu: [
                    {
                        text: 'Esquerda', onclick: function () {
                            if (editor.selection.getContent().startsWith('<img ')) {
                                str = $('<div></div>').html(editor.selection.getContent()).find('img').removeClass('imgFull').end().html();
                                str = $('<div></div>').html(str).find('img').removeClass('imgRight').end().html();
                                if ($('<div></div>').html(str).find('img').hasClass("imgLeft"))
                                    str = $('<div></div>').html(str).find('img').removeClass('imgLeft').end().html();
                                else
                                    str = $('<div></div>').html(str).find('img').addClass('imgLeft').end().html();
                                editor.selection.setContent(str);
                                console.log(str);
                            }
                        }
                    },
                    {
                        text: 'Direita', onclick: function () {
                            if (editor.selection.getContent().startsWith('<img ')) {
                                str = $('<div></div>').html(editor.selection.getContent()).find('img').removeClass('imgFull').end().html();
                                str = $('<div></div>').html(str).find('img').removeClass('imgLeft').end().html();
                                if ($('<div></div>').html(str).find('img').hasClass("imgRight"))
                                    str = $('<div></div>').html(str).find('img').removeClass('imgRight').end().html();
                                else
                                    str = $('<div></div>').html(str).find('img').addClass('imgRight').end().html();
                                editor.selection.setContent(str);
                                console.log(str);
                            }
                        }
                    },
                    {
                        text: 'Full', onclick: function () {
                            if (editor.selection.getContent().startsWith('<img ')) {
                                str = $('<div></div>').html(editor.selection.getContent()).find('img').removeClass('imgRight').end().html();
                                str = $('<div></div>').html(str).find('img').removeClass('imgLeft').end().html();
                                if ($('<div></div>').html(str).find('img').hasClass("imgFull"))
                                    str = $('<div></div>').html(str).find('img').removeClass('imgFull').end().html();
                                else
                                    str = $('<div></div>').html(str).find('img').addClass('imgFull').end().html();
                                editor.selection.setContent(str);
                                console.log(str);
                            }
                        }
                    }
                ]
            });
        },
        style_formats: [
            {
                title: 'Tópico',
                block: 'span',
                classes: 'ec_topico'
            },
            {
                title: 'Título 1',
                block: 'h1'
            }
        ],
        content_css: cssFiles,
        document_base_url: system_path
    }
    if (config.upload) {
        init_object.file_browser_callback = elFinderBrowser;
        init_object.toolbar = "insertfile undo redo | iserir | customiza | imgAlign | tipografia | styleselect | fontsizeselect | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor";
    } else {
        init_object.toolbar = "insertfile undo redo | iserir | customiza | imgAlign | styleselect | tipografia | fontsizeselect | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | forecolor backcolor";
    }
    tinymce.init(init_object);
}
function elFinderBrowser(field_name, url, type, win) {
    tinymce.activeEditor.windowManager.open({
        file: '../main/System/includes/elfinder_tinymce_popup.php?root_path=' + system_path, // use an absolute path!
        title: 'elFinder 2.0',
        width: 900,
        height: 600,
        resizable: 'noe'
    }, {
        setUrl: function (url) {
            win.document.getElementById(field_name).value = url;
        }
    });
    return false;
}
//EVENTO COM TECLA DE ATALHOS
function saveKey(active) {
    if (active === true) {
        $(document).on("keydown", function (e) {
            if (e.altKey && e.keyCode == 83) {
                $(".clickformsubmit").trigger('click');
            }
        });
    }
}
function othersKey(active) {
    if (active === true) {
        $(document).on("keydown", function (e) {
            if (e.altKey && e.keyCode == 38) {
                $("#back-to-top").trigger('click');
            }
            else if (e.altKey && e.keyCode == 40) {
                var n = $(document).height();
                $('html, body').animate({ scrollTop: n }, 'slow');
            }
        });
    }
}
othersKey(true);
function clean_registros() {
    lista_del = [];
    $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
    $("#apagar_todos").removeAttr("checked");
    $("#apagar_geral").removeAttr("checked");
    $(".del-registros").val("");
    $(".registros-geral").val("0");
    $(".data-controller").val("");
    $(".data-id-lista").val("");
    $(".data-id-segmentacao").val("");
    uniform();
}
(function ($) {
    $.fn.getContentHtml = function (content) {
        return $.trim($(this).html()
            .split(/\>[\n\t\s]*\</g).join('><')
            .split(/[\n\t]*/gm).join(''));
    }
})(jQuery);
String.prototype.extenso = function (c) {
    var ex = [
        ["zero", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove", "dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove"],
        ["dez", "vinte", "trinta", "quarenta", "cinqüenta", "sessenta", "setenta", "oitenta", "noventa"],
        ["cem", "cento", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"],
        ["mil", "milhão", "bilhão", "trilhão", "quadrilhão", "quintilhão", "sextilhão", "setilhão", "octilhão", "nonilhão", "decilhão", "undecilhão", "dodecilhão", "tredecilhão", "quatrodecilhão", "quindecilhão", "sedecilhão", "septendecilhão", "octencilhão", "nonencilhão"]
    ];
    var a, n, v, i, n = this.replace(c ? /[^,\d]/g : /\D/g, "").split(","), e = " e ", $ = "real", d = "centavo", sl;
    for (var f = n.length - 1, l, j = -1, r = [], s = [], t = ""; ++j <= f; s = []) {
        j && (n[j] = (("." + n[j]) * 1).toFixed(2).slice(2));
        if (!(a = (v = n[j]).slice((l = v.length) % 3).match(/\d{3}/g), v = l % 3 ? [v.slice(0, l % 3)] : [], v = a ? v.concat(a) : v).length) continue;
        for (a = -1, l = v.length; ++a < l; t = "") {
            if (!(i = v[a] * 1)) continue;
            i % 100 < 20 && (t += ex[0][i % 100]) ||
                i % 100 + 1 && (t += ex[1][(i % 100 / 10 >> 0) - 1] + (i % 10 ? e + ex[0][i % 10] : ""));
            s.push((i < 100 ? t : !(i % 100) ? ex[2][i == 100 ? 0 : i / 100 >> 0] : (ex[2][i / 100 >> 0] + e + t)) +
                ((t = l - a - 2) > -1 ? " " + (i > 1 && t > 0 ? ex[3][t].replace("ão", "ões") : ex[3][t]) : ""));
        }
        a = ((sl = s.length) > 1 ? (a = s.pop(), s.join(" ") + e + a) : s.join("") || ((!j && (n[j + 1] * 1 > 0) || r.length) ? "" : ex[0][0]));
        a && r.push(a + (c ? (" " + (v.join("") * 1 > 1 ? j ? d + "s" : (/0{6,}$/.test(n[0]) ? "de " : "") + $.replace("l", "is") : j ? d : $)) : ""));
    }
    return r.join(e);
}
function summernote() {
    $('.summernote').summernote({ disableDragAndDrop: true, toolbar: [['style', ['bold', 'italic', 'underline', 'clear']], ['color', ['color']],], height: 100 });
}
function tinymceInit2(config) {
	var defaults = {
		upload: true,
		css: 'tinymce.css',
		galeria: false,
		notificacao: false
	}
	var config = $.extend(defaults, config);
	var galerias = "";
	var notificacao_nome = "";
	var notificacao_assunto = "";
	init_object = {
		valid_children: '+body[style]',
		inline_styles: true,
		relative_urls: false,
		remove_script_host: true,
		convert_urls: false,
		document_base_url: '',
		entity_encoding: "raw",
		selector: "[tinymce]",
		valid_elements: '*[*]',
		paste_data_images: true,
		plugins: [
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code fullscreen",
			"insertdatetime media table contextmenu paste",
			"textcolor "
		],
		setup: function (editor) {
			if (config.galeria) {
				galerias = {
					text: 'Galeria de Fotos', onclick: function () {
						editor.insertContent('[({GALERIA})]');
					}
				}
			}
			if (config.notificacao) {
				notificacao_nome = {
					text: 'Nome', onclick: function () {
						editor.insertContent('[({NOME})]');
					}
				};
				notificacao_assunto = {
					text: 'Assunto', onclick: function () {
						editor.insertContent('[({ASSUNTO})]');
					}
				};
			}
			editor.addButton('iserir', {
				type: 'menuButton',
				text: 'Inserir',
				icon: false,
				menu: [
					galerias,
					notificacao_nome,
					notificacao_assunto
				]
			});
			editor.addButton('customiza', {
				type: 'menuButton',
				text: 'Customização',
				icon: false,
				menu: [
					{
						text: 'Bloco Separador', onclick: function () {
							editor.insertContent('<div class="clear"></div>');
						}
					}
				]
			});
			editor.addButton('imgAlign', {
				type: 'menuButton',
				text: 'Imagem',
				icon: false,
				menu: [
					{
						text: 'Esquerda', onclick: function () {
							if (editor.selection.getContent().startsWith('<img ')) {
								let elem = $.parseHTML(editor.selection.getContent());
								$(elem).removeClass('imgFull');
								$(elem).removeClass('imgRight');
								$(elem).removeClass('imgLeft');
								$(elem).addClass('imgLeft');
								$(elem).removeAttr('height');
								$(elem).removeAttr('width');
								let src = $(elem)[0].outerHTML;
								editor.selection.setContent(src);
								console.log(src);
							}
						}
					},
					{
						text: 'Direita', onclick: function () {
							if (editor.selection.getContent().startsWith('<img ')) {
								let elem = $.parseHTML(editor.selection.getContent());
								$(elem).removeClass('imgFull');
								$(elem).removeClass('imgRight');
								$(elem).removeClass('imgLeft');
								$(elem).addClass('imgRight');
								$(elem).removeAttr('height');
								$(elem).removeAttr('width');
								let src = $(elem)[0].outerHTML;
								editor.selection.setContent(src);
								console.log(src);
							}
						}
					},
					{
						text: 'Full', onclick: function () {
							if (editor.selection.getContent().startsWith('<img ')) {
								let elem = $.parseHTML(editor.selection.getContent());
								$(elem).removeClass('imgFull');
								$(elem).removeClass('imgRight');
								$(elem).removeClass('imgLeft');
								$(elem).addClass('imgFull');
								$(elem).removeAttr('height');
								$(elem).removeAttr('width');
								let src = $(elem)[0].outerHTML;
								editor.selection.setContent(src);
								console.log(src);
							}
						}
					}
				]
			});
		},
		style_formats: [
			{
				title: 'Tópico',
				block: 'span',
				classes: 'ec_topico'
			}
		],
		content_css: [system_path + "../main/templates/supradmin/css/" + config.css]
	}
	if (config.upload) {
		init_object.file_browser_callback = elFinderBrowser;
		init_object.toolbar = "insertfile undo redo | customiza | imgAlign | tipografia | styleselect | fontsizeselect | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor";
	} else {
		init_object.toolbar = "insertfile undo redo | customiza | imgAlign | styleselect | tipografia | fontsizeselect | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | forecolor backcolor";
	}
	tinymce.init(init_object);
}