<?php

namespace NFePHP\NFSe\Models\Tecnos\Factories\v100;

/**
 * Classe para a renderização dos RPS em XML
 * conforme o modelo Tecnos
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Models\Tecnos\RenderRPS
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author Jose Alcides    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse for the canonical source repository
 */

use DateTimeZone;
use NFePHP\Common\Certificate;
use NFePHP\NFSe\Models\Tecnos\Factories\Signer;
use NFePHP\NFSe\Models\Tecnos\RenderRps as RenderRPSBase;
use NFePHP\NFSe\Models\Tecnos\Rps;

class RenderRps extends RenderRPSBase
{
    public static function toXml(
        $data,
        DateTimeZone $timezone,
        Certificate $certificate,
        $algorithm = OPENSSL_ALGO_SHA1
    ) {
        self::$certificate = $certificate;
        self::$algorithm = $algorithm;
        self::$timezone = $timezone;
        $xml = '';
        if (is_object($data)) {
            $xml = self::render($data);
        } elseif (is_array($data)) {
            foreach ($data as $rps) {
                $xml .= self::render($rps);
            }
        }

        $xmlSigned = Signer::sign(
            self::$certificate,
            $xml,
            'Rps',
            'Id',
            self::$algorithm,
            [true, false, null, null],
            '',
            false,
            1,
            false

        );

        $xmlSigned = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xmlSigned);

        return $xmlSigned;
    }

    /**
     * Monta o xml com base no objeto Rps
     * @param Rps $rps
     * @return string
     */
    protected static function render(Rps $rps, &$dom, &$parent)
    {
        self::$dom = $dom;
        $root = self::$dom->createElement('Rps');

        $numeroFormatado = str_pad($rps->infNumero, 16, '0', STR_PAD_LEFT);
        $tcdeclaracaoRPS = self::$dom->createElement("tcDeclaracaoPrestacaoServico");
        $infRPS = self::$dom->createElement("InfDeclaracaoPrestacaoServico");
        $infRPS->setAttribute('Id', "R{$rps->infPrestador['cnpjcpf']}{$numeroFormatado}");

        /** RPS Filha **/
        $rpsInf = self::$dom->createElement('Rps');

        //Identificação RPS
        $identificacaoRps = self::$dom->createElement('IdentificacaoRps');


        $rps->infDataEmissao->setTimezone(self::$timezone);

        self::$dom->addChild(
            $identificacaoRps,
            'Numero',
            $rps->infNumero,
            true,
            "Numero do RPS",
            false
        );
        self::$dom->addChild(
            $identificacaoRps,
            'Serie',
            $rps->infSerie,
            true,
            "Serie do RPS",
            false
        );
        self::$dom->addChild(
            $identificacaoRps,
            'Tipo',
            $rps->infTipo,
            true,
            "Tipo do RPS",
            false
        );
        self::$dom->appChild($rpsInf, $identificacaoRps, 'Adicionando tag IdentificacaoRPS');
        //FIM Identificação RPS
        self::$dom->addChild(
            $rpsInf,
            'DataEmissao',
            $rps->infDataEmissao->format('Y-m-d\TH:i:s'),
            true,
            'Data de Emissão do RPS',
            false
        );

        self::$dom->addChild(
            $rpsInf,
            'Status',
            $rps->infStatus,
            true,
            'Status',
            false
        );

        //RPS Substituido
        if (!empty($rps->infRpsSubstituido['numero'])) {
            $rpssubs = self::$dom->createElement('RpsSubstituido');
            self::$dom->addChild(
                $rpssubs,
                'Numero',
                $rps->infRpsSubstituido['numero'],
                true,
                'Numero',
                false
            );
            self::$dom->addChild(
                $rpssubs,
                'Serie',
                $rps->infRpsSubstituido['serie'],
                true,
                'Serie',
                false
            );
            self::$dom->addChild(
                $rpssubs,
                'Tipo',
                $rps->infRpsSubstituido['tipo'] == '' ?? 1,
                true,
                'tipo',
                false
            );
            self::$dom->appChild($rpsInf, $rpssubs, 'Adicionando tag RpsSubstituido em infRps');
        }

        self::$dom->appChild($infRPS, $rpsInf, 'Adicionando tag Rps');
        /** FIM RPS Filha **/

        self::$dom->addChild(
            $infRPS,
            'Competencia',
            $rps->infDataEmissao->format('Y-m-d\TH:i:s'),
            true,
            'Competencia Emissão do RPS',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'IdCidade',
            $rps->infMunicipioPrestacaoServico,
            true,
            'IdCidade',
            false
        );

        /** Serviços **/
        $servico = self::$dom->createElement('Servico');

        $tcDadosServico = self::$dom->createElement('tcDadosServico');

        //Valores
        $valores = self::$dom->createElement('Valores');
        self::$dom->addChild(
            $valores,
            'BaseCalculoCRS',
            $rps->infBaseCalculoCRS,
            true,
            'BaseCalculoCRS',
            false
        );
        self::$dom->addChild(
            $valores,
            'IrrfIndenizacao',
            $rps->infIrrfIndenizacao,
            true,
            'IrrfIndenizacao',
            false
        );
        self::$dom->addChild(
            $valores,
            'ValorServicos',
            $rps->infValorServicos,
            true,
            'ValorServicos',
            false
        );
        self::$dom->addChild(
            $valores,
            'ValorDeducoes',
            $rps->infValorDeducoes,
            false,
            'ValorDeducoes',
            false
        );
        self::$dom->addChild(
            $valores,
            'ValorPis',
            $rps->infValorPis,
            false,
            'ValorPis',
            false
        );
        self::$dom->addChild(
            $valores,
            'ValorCofins',
            $rps->infValorCofins,
            false,
            'ValorCofins',
            false
        );
        self::$dom->addChild(
            $valores,
            'ValorInss',
            $rps->infValorInss,
            false,
            'ValorInss',
            false
        );
        self::$dom->addChild(
            $valores,
            'ValorIr',
            $rps->infValorIr,
            false,
            'ValorIr',
            false
        );
        self::$dom->addChild(
            $valores,
            'ValorCsll',
            $rps->infValorCsll,
            false,
            'ValorCsll',
            false
        );
        self::$dom->addChild(
            $valores,
            'OutrasRetencoes',
            $rps->infOutrasRetencoes,
            false,
            'OutrasRetencoes',
            false
        );

        /*self::$dom->addChild(
            $valores,
            'ValTotTributos',
            $rps->infTotTributacao,
            false,
            'ValTotTributos',
            false
        );*/

        self::$dom->addChild(
            $valores,
            'ValorIss',
            $rps->infValorIss,
            false,
            'ValorIss',
            false
        );
        self::$dom->addChild(
            $valores,
            'Aliquota',
            number_format($rps->infAliquota, 2, '.', ''),
            false,
            'Aliquota',
            false
        );
        self::$dom->addChild(
            $valores,
            'DescontoIncondicionado',
            $rps->infDescontoIncondicionado,
            false,
            'DescontoIncondicionado',
            false
        );
        self::$dom->addChild(
            $valores,
            'DescontoCondicionado',
            $rps->infDescontoCondicionado,
            false,
            'DescontoCondicionado',
            false
        );
        self::$dom->appChild($tcDadosServico, $valores, 'Adicionando tag Valores em Servico');
        self::$dom->addChild(
            $tcDadosServico,
            'IssRetido',
            $rps->infIssRetido,
            true,
            'IssRetido',
            false
        );

        self::$dom->addChild(
            $tcDadosServico,
            'ResponsavelRetencao',
            $rps->infResponsavelRetencao,
            true,
            'ResponsavelRetencao',
            false
        );
        //FIM Valores

        self::$dom->addChild(
            $tcDadosServico,
            'ItemListaServico',
            $rps->infItemListaServico,
            true,
            'ItemListaServico',
            false
        );
        self::$dom->addChild(
            $tcDadosServico,
            'CodigoCnae',
            $rps->infCodigoCnae,
            false,
            'CodigoCnae',
            false
        );
        self::$dom->addChild(
            $tcDadosServico,
            'CodigoTributacaoMunicipio',
            $rps->infCodigoTributacaoMunicipio,
            false,
            'CodigoTributacaoMunicipio',
            false
        );
        self::$dom->addChild(
            $tcDadosServico,
            'Discriminacao',
            $rps->infDiscriminacao,
            true,
            'Discriminacao',
            false
        );
        self::$dom->addChild(
            $tcDadosServico,
            'CodigoMunicipio',
            $rps->infMunicipioPrestacaoServico,
            true,
            'CodigoMunicipio',
            false
        );
        self::$dom->addChild(
            $tcDadosServico,
             'CodigoPais',
             $rps->infCodigoPais,
             false,
             'CodigoPais',
             false
        );
         
        self::$dom->addChild(
            $tcDadosServico,
            'ExigibilidadeISS',
            1,
            true,
            'ExigibilidadeISS',
            false
        );
        self::$dom->addChild(
            $tcDadosServico,
            'MunicipioIncidencia',
            $rps->infMunicipioPrestacaoServico,
            false,
            'MunicipioIncidencia',
            false
        );
        self::$dom->addChild(
            $tcDadosServico,
            'NumeroProcesso',
            '',
            true,
            'NumeroProcesso',
            false
        );
        self::$dom->appChild($servico, $tcDadosServico, 'Adicionando tag Valores em Servico');
        self::$dom->appChild($infRPS, $servico, 'Adicionando tag Servico');
        /** FIM Serviços **/

        /** Prestador **/
        $prestador = self::$dom->createElement('Prestador');

        //Cpf/Cnpj
        if (!empty($rps->infPrestador['cnpjcpf'])) {
            $cpfCnpj = self::$dom->createElement('CpfCnpj');
            if ($rps->infPrestador['tipo'] == 2) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cnpj',
                    $rps->infPrestador['cnpjcpf'],
                    true,
                    'Prestador CNPJ',
                    false
                );
            } else {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cpf',
                    $rps->infPrestador['cnpjcpf'],
                    true,
                    'Prestador CPF',
                    false
                );
            }
            self::$dom->appChild($prestador, $cpfCnpj, 'Adicionando tag CpfCnpj em Prestador');
        }

        self::$dom->addChild(
            $prestador,
            'RazaoSocial',
            $rps->infPrestador['razaosocial'],
            false,
            'RazaoSocial',
            false
        );

        self::$dom->addChild(
            $prestador,
            'InscricaoMunicipal',
            $rps->infPrestador['im'],
            true,
            'InscricaoMunicipal',
            false
        );
        self::$dom->appChild($infRPS, $prestador, 'Adicionando tag Prestador em infRPS');
        /** FIM Prestador **/

        /** Tomador **/
        if (!empty($rps->infTomador['razao'])) {
            $tomador = self::$dom->createElement('Tomador');

            //Identificação Tomador
            if (!empty($rps->infTomador['cnpjcpf'])) {
                $identificacaoTomador = self::$dom->createElement('IdentificacaoTomador');
                $cpfCnpjTomador = self::$dom->createElement('CpfCnpj');
                if ($rps->infTomador['tipo'] == 2) {
                    self::$dom->addChild(
                        $cpfCnpjTomador,
                        'Cnpj',
                        $rps->infTomador['cnpjcpf'],
                        true,
                        'Tomador CNPJ',
                        false
                    );
                } else {
                    self::$dom->addChild(
                        $cpfCnpjTomador,
                        'Cpf',
                        $rps->infTomador['cnpjcpf'],
                        true,
                        'Tomador CPF',
                        false
                    );
                }
                self::$dom->appChild($identificacaoTomador, $cpfCnpjTomador,
                    'Adicionando tag CpfCnpj em IdentificacaTomador');


                //Inscrição Municipal
                self::$dom->addChild(
                    $identificacaoTomador,
                    'InscricaoMunicipal',
                    $rps->infTomador['im'],
                    false,
                    'InscricaoMunicipal',
                    false
                );

                // Inscrição Estadual
                self::$dom->addChild(
                    $identificacaoTomador,
                    'InscricaoEstadual',
                    $rps->infTomador['ie'],
                    false,
                    'InscricaoEstadual',
                    false
                );

                self::$dom->appChild($tomador, $identificacaoTomador,
                    'Adicionando tag IdentificacaoTomador em Tomador');

            }

            //Razao Social
            self::$dom->addChild(
                $tomador,
                'RazaoSocial',
                $rps->infTomador['razao'],
                true,
                'RazaoSocial',
                false
            );

            //Endereço
            if (!empty($rps->infTomadorEndereco['end'])) {
                $endereco = self::$dom->createElement('Endereco');
                self::$dom->addChild(
                    $endereco,
                    'Endereco',
                    $rps->infTomadorEndereco['end'],
                    true,
                    'Endereco',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Numero',
                    $rps->infTomadorEndereco['numero'],
                    false,
                    'Numero',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Complemento',
                    $rps->infTomadorEndereco['complemento'],
                    false,
                    'Complemento',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Bairro',
                    $rps->infTomadorEndereco['bairro'],
                    false,
                    'Bairro',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'CodigoMunicipio',
                    $rps->infTomadorEndereco['cmun'],
                    false,
                    'CodigoMunicipio',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Uf',
                    $rps->infTomadorEndereco['uf'],
                    false,
                    'Uf',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'CodigoPais',
                    $rps->infTomadorEndereco['codigopais'],
                    false,
                    'CodigoPais',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Cep',
                    $rps->infTomadorEndereco['cep'],
                    false,
                    'Cep',
                    false
                );
                
                /*self::$dom->addChild(
                    $endereco,
                    'CodigoPais',
                    $rps->infTomadorEndereco['codigoPais'],
                    false,
                    'CodigoPais',
                    false
                );*/
                
                self::$dom->appChild($tomador, $endereco, 'Adicionando tag Endereco em Tomador');

            }

            //Contato
            if ($rps->infTomador['tel'] != '' || $rps->infTomador['email'] != '') {
                $contato = self::$dom->createElement('Contato');
                self::$dom->addChild(
                    $contato,
                    'Telefone',
                    $rps->infTomador['tel'],
                    false,
                    'Telefone Tomador',
                    false
                );
                self::$dom->addChild(
                    $contato,
                    'Email',
                    $rps->infTomador['email'],
                    false,
                    'Email Tomador',
                    false
                );
                self::$dom->appChild($tomador, $contato, 'Adicionando tag Contato em Tomador');
            }
            self::$dom->appChild($infRPS, $tomador, 'Adicionando tag Tomador em infRPS');
        }

        /** FIM Tomador **/

        /** Intermediario **/
        if (!empty($rps->infIntermediario['razao'])) {
            $intermediario = self::$dom->createElement('Intermediario');
            $cpfCnpj = self::$dom->createElement('CpfCnpj');
            if ($rps->infIntermediario['tipo'] == 2) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cnpj',
                    $rps->infIntermediario['cnpjcpf'],
                    true,
                    'CNPJ Intermediario',
                    false
                );
            } elseif ($rps->infIntermediario['tipo'] == 1) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cpf',
                    $rps->infIntermediario['cnpjcpf'],
                    true,
                    'CPF Intermediario',
                    false
                );
            }
            self::$dom->appChild($intermediario, $cpfCnpj, 'Adicionando tag CpfCnpj em Intermediario');
            self::$dom->addChild(
                $intermediario,
                'InscricaoMunicipal',
                $rps->infIntermediario['im'],
                false,
                'IM Intermediario',
                false
            );

            //Razao Social
            self::$dom->addChild(
                $intermediario,
                'RazaoSocial',
                $rps->infIntermediario['razao'],
                true,
                'Razao Intermediario',
                false
            );
            self::$dom->appChild($infRPS, $intermediario, 'Adicionando tag Intermediario em infRPS');
        }
        /** FIM Intermediario **/

        /** Construção Civil **/
        if (!empty($rps->infConstrucaoCivil['obra'])) {
            $construcao = self::$dom->createElement('ContrucaoCivil');
            self::$dom->addChild(
                $construcao,
                'CodigoObra',
                $rps->infConstrucaoCivil['obra'],
                false,
                'Codigo da Obra',
                false
            );
            self::$dom->addChild(
                $construcao,
                'Art',
                $rps->infConstrucaoCivil['art'],
                true,
                'Art da Obra',
                false
            );
            self::$dom->appChild($infRPS, $construcao, 'Adicionando tag Construcao em infRPS');
        }
        /** FIM Construção Civil **/

        self::$dom->addChild(
            $infRPS,
            'RegimeEspecialTributacao',
            $rps->infRegimeEspecialTributacao,
            true,
            'RegimeEspecialTributacao',
            false
        );
        self::$dom->addChild(
            $infRPS,
            'NaturezaOperacao',
            $rps->infNaturezaOperacao,
            false,
            'NaturezaOperacao',
            false
        );
        self::$dom->addChild(
            $infRPS,
            'OptanteSimplesNacional',
            $rps->infOptanteSimplesNacional,
            true,
            'OptanteSimplesNacional',
            false
        );
        self::$dom->addChild(
            $infRPS,
            'IncentivoFiscal',
            $rps->infIncentivadorCultural,
            true,
            'IncentivoFiscal',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'PercentualCargaTributaria',
            0,
            true,
            'PercentualCargaTributaria',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'ValorCargaTributaria',
            0,
            true,
            'ValorCargaTributaria',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'PercentualCargaTributariaEstadual',
            0,
            true,
            'PercentualCargaTributariaEstadual',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'ValorCargaTributariaEstadual',
            0,
            true,
            'ValorCargaTributariaEstadual',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'PercentualCargaTributariaMunicipal',
            0,
            true,
            'PercentualCargaTributariaMunicipal',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'ValorCargaTributariaMunicipal',
            0,
            true,
            'ValorCargaTributariaMunicipal',
            false
        );

//        self::$dom->addChild(
//            $infRPS,
//            'OutrasInformacoes',
//            '',
//            false,
//            'OutrasInformacoes',
//            false
//        );

        self::$dom->addChild(
            $infRPS,
            'SiglaUF',
            $rps->infUFPrestacaoServico,
            true,
            'SiglaUF',
            false
        );

        self::$dom->addChild(
            $infRPS,
            'IdCidade',
            $rps->infMunicipioPrestacaoServico,
            true,
            'IdCidade',
            false
        );

//        self::$dom->addChild(
//            $infRPS,
//            'EspecieDocumento',
//            $rps->,
//            false,
//            'EspecieDocumento',
//            false
//        );

//        self::$dom->addChild(
//            $infRPS,
//            'SerieTalonario',
//            $rps->,
//            false,
//            'SerieTalonario',
//            false
//        );

//        self::$dom->addChild(
//            $infRPS,
//            'FormaPagamento',
//            $rps->,
//            false,
//            'FormaPagamento',
//            false
//        );

//        self::$dom->addChild(
//            $infRPS,
//            'NumeroParcelas',
//            $rps->,
//            false,
//            'NumeroParcelas',
//            false
//        );

        self::$dom->appChild($tcdeclaracaoRPS, $infRPS, 'Adicionando tag infRPS em RPS');
        self::$dom->appChild($root, $tcdeclaracaoRPS, 'Adicionando tag infRPS em RPS');
        self::$dom->appChild($parent, $root, 'Adicionando tag RPS na ListaRps');

        return $root;
    }

    public static function appendRps(
        $data,
        DateTimeZone $timezone,
        Certificate $certificate,
        $algorithm = OPENSSL_ALGO_SHA1,
        &$dom,
        &$parent
    ) {

        self::$certificate = $certificate;
        self::$algorithm = $algorithm;
        self::$timezone = $timezone;

        if (is_object($data)) {
            $rootNode = self::render($data, $dom, $parent);
        }
    }
}
