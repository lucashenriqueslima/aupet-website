<?php
namespace app\classes;
class Variaveis {
    private $planovariaveis = [
        ['{[dt_proposta]}', 'Data da proposta'],
        ['{[nome]}', 'Nome do cliente'],
        ['{[email]}', 'Email do cliente'],
        ['{[telefone]}', 'Telefone do cliente'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_sexo]}','Sexo do pet'],
        ['{[pet_porte]}','Porte do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
        ['{[indicador_nome]}', 'Nome do indicador'],
        ['{[indicador_telefone]}', 'Telefone do indicador'],
    ];
    private $contratoPet = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[assos_cpfcnpj]}', 'CPF ou CNPJ do associado'],
        ['{[assos_rg]}', 'RG do associado'],
        ['{[assos_orgao_exp]}', 'Órgão Expedidor do associado'],
        ['{[assos_CEP]}', 'CEP do endereço do associado'],
        ['{[assos_complemento]}', 'Complemento endereço do associado'],
        ['{[assos_numero]}', 'Numero endereço do associado'],
        ['{[assos_bairro]}', 'Bairro do associado'],
        // ['{[assos_endereco]}', 'Endereço associado'],
        ['{[assos_cidade]}', 'Cidade do associado'],
        ['{[assos_estado]}', 'Estado do associado'],
        ['{[assos_whatsapp]}', 'Whatsapp do associado'],
        ['{[assos_dtnasc]}', 'Data de nascimento do associado'],
        ['{[assos_email]}', 'Email do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_sexo]}','Sexo do pet'],
        ['{[pet_porte]}','Porte do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
        ['{[indicador_nome]}', 'Nome do indicador'],
        ['{[indicador_telefone]}', 'Telefone do indicador'],
        ['{[dt_contrato]}', 'Data do contrato'],
        ['{[dt_contrato_ext]}', 'Data do contrato por extenso'],
        ['{[img_assinatura]}', 'Imagem da assinatura do associado'],
        ['{[pagebreak]}', 'Inicia uma nova página no PDF'],
        ['{[num_indicacao]}', 'Número da Indicação'],
    ];
    private $assinouPlanoAssociado = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
    ];
    private $alterouPlanoAssociado = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
    ];

    private $excluiPlanoAssociado = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
    ];

    private $excluiPetComPlanoAssociado = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
    ];

    private $assinouPlanoConsultor = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[nome_consultor]}', 'Nome do consultor'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
    ];

    private $finalizouLeadAssociado = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[assos_password]}', 'Senha de acesso do associado'],
        ['{[assos_email]}', 'Email de acesso do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula']
    ];

    private $aprovouTermoPet = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
        ['{[indicador_nome]}', 'Nome do indicador'],
        ['{[indicador_telefone]}', 'Telefone do indicador'],
    ];

    private $aprovouVistoriaPet = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
        ['{[indicador_nome]}', 'Nome do indicador'],
        ['{[indicador_telefone]}', 'Telefone do indicador'],
    ];

    private $reprovouTermoFiliacao = [
        ['{[assos_nome]}', 'Nome do associado'],
        ['{[assos_telefone]}', 'Telefone do associado'],
        ['{[pet_nome]}','Nome do pet'],
        ['{[pet_especie]}','Especie do pet'],
        ['{[pet_raca]}','Raça do pet'],
        ['{[pet_plano_nome]}','Plano selecionado'],
        ['{[pet_plano_valor]}','Valor do plano'],
        ['{[beneficios]}', 'Lista de benefícios separado por quebra de linha'],
        ['{[beneficios_virgula]}', 'Lista de benefícios separados por virgula'],
        ['{[indicador_nome]}', 'Nome do indicador'],
        ['{[indicador_telefone]}', 'Telefone do indicador'],
    ];

    public function __get($prop) {
        if ($prop == 'planovariaveis') return $this->format($this->planovariaveis);
        else if ($prop == 'contratoPet') return $this->format($this->contratoPet);

        else if ($prop == 'notf5') return $this->format($this->aprovouVistoriaPet);

        else if ($prop == 'notf6') return $this->format($this->ativouLead);
        else if ($prop == 'notf7') return $this->format($this->ativouLead);
        else if ($prop == 'notf8') return $this->format($this->ativouLead);
        else if ($prop == 'notf9') return $this->format($this->ativouLead);

        else if ($prop == 'notf10') return $this->format($this->reprovouTermoFiliacao);

        else if ($prop == 'notf11') return $this->format($this->ativouLead);
        else if ($prop == 'notf13') return $this->format($this->aprovouTermoPet);
        
        else if ($prop == 'notf14') return $this->format($this->finalizouLeadAssociado);
        
        else if ($prop == 'notf15') return $this->format($this->assinouPlanoAssociado);
        else if ($prop == 'notf16') return $this->format($this->alterouPlanoAssociado);
        else if ($prop == 'notf17') return $this->format($this->excluiPlanoAssociado);
        else if ($prop == 'notf18') return $this->format($this->excluiPetComPlanoAssociado);
        else if ($prop == 'notf19') return $this->format($this->assinouPlanoConsultor);
    }
    private function format($variables) {
        return array_map(function ($x) {return ['variavel' => $x[0], 'descricao' => $x[1]];}, $variables);
    }
}