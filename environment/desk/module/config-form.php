<div class="panel-body">
    <div class="form-group">
        <label class="col-lg-2 control-label" for="mensagem_sucesso">Mensagem de Sucesso:</label>
        <div class="col-lg-10">
            <input class="form-control" name="mensagem" id="mensagem" type="text" value="<?php echo $this->registro['mensagem'] ?>" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-2 control-label" for="script_conversao">Scripts de Conversão:</label>
        <div class="col-lg-10">
            <textarea id="script" name="script" class="form-control" rows="8"><?php echo stripslashes($this->registro['script']) ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-2 control-label" for="ws_hypnobox_id">Hypnobox ID:</label>
        <div class="col-lg-10">
            <input class="form-control" name="ws_hypnobox_id" id="ws_hypnobox_id" type="text" value="<?php echo $this->registro['ws_hypnobox_id'] ?>" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-2 control-label" for="ws_rdstation_token">RDStation identificador:</label>
        <div class="col-lg-10">
            <input class="form-control" name="ws_rdstation_token" id="ws_rdstation_token" type="text" value="<?php echo $this->registro['ws_rdstation_token'] ?>" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-2 control-label" for="bitrix_titulo">Bitrix identificador:</label>
        <div class="col-lg-10">
            <input class="form-control" name="bitrix_titulo" id="bitrix_titulo" type="text" value="<?php echo $this->registro['bitrix_titulo'] ?>" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-2 control-label" for="link_bitrix">Bitrix24 url: <i class="fa fa-question-circle cursor" data-container="body" data-toggle="popover" data-placement="top" data-content="Para obter acesse <a href='https://santrisistemas.bitrix24.com/devops/section/external/'>Bitrix</a> -> Add leads -> Webhook to call REST API. <br>Com um usuário com permissão para criar leads." title="" data-original-title="" ></i></label>
        <div class="col-lg-10">
            <input class="form-control" name="link_bitrix" id="link_bitrix" type="text" value="<?php echo $this->registro['link_bitrix'] ?>" />
        </div>
    </div>
    <!-- <div class="form-group">
        <div class="col-md-4">
            <label class="col-lg-6 control-label" for="ckbox_1_stats">Mostrar checkbox 1?</label>
            <div class="col-lg-6">
                <div class="toggle-custom">
                    <label class="toggle" data-on="SIM" data-off="NÃO">
                        <input type="checkbox" id="chbox2" name="ckbox_1_stats" value="1" <?php if ((bool)$this->registro['ckbox_1_stats']) : ?> checked <?php endif ?>>
                        <span class="button-checkbox"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <label class="col-lg-2 control-label" for="ckbox_1_texto">Texto checkbox 1:</label>
            <div class="col-lg-10">
                <textarea name="ckbox_1_texto" class="form-control summernote" rows="1"><?php echo stripslashes($this->registro['ckbox_1_texto']) ?></textarea>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-4">
            <label class="col-lg-6 control-label" for="ckbox_2_stats">Mostrar checkbox 2?</label>
            <div class="col-lg-6">
                <div class="toggle-custom">
                    <label class="toggle" data-on="SIM" data-off="NÃO">
                        <input type="checkbox" id="chbox2" name="ckbox_2_stats" value="1" <?php if ((bool)$this->registro['ckbox_2_stats']) : ?> checked <?php endif ?>>
                        <span class="button-checkbox"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <label class="col-lg-2 control-label" for="ckbox_2_texto">Texto checkbox 2:</label>
            <div class="col-lg-10">
            <textarea name="ckbox_2_texto" class="form-control summernote" rows="1"><?php echo stripslashes($this->registro['ckbox_2_texto']) ?></textarea>
            </div>
        </div>
    </div> -->
    <?php if (!$this->permissions[$this->permissao_ref]['editar']): ?>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <span class="label label-warning"><span class="icon24 icomoon-icon-warning "></span>Você não tem permissão para editar esta função.</span>
            </div>
        </div>
    <?php else: ?>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10" style="text-align:right;">
                <button type="button" class="btn btn-info clickformsubmit btn-lg">Salvar alterações</button>
            </div>
        </div>
    <?php endif ?>
</div>