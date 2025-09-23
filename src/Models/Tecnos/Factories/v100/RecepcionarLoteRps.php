<?php

namespace NFePHP\NFSe\Models\Tecnos\Factories\v100;

use NFePHP\Common\DOMImproved as Dom;
use NFePHP\NFSe\Models\Tecnos\Factories\RecepcionarLoteRps as RecepcionarLoteRpsBase;
use NFePHP\NFSe\Models\Tecnos\Factories\Signer;

class RecepcionarLoteRps extends RecepcionarLoteRpsBase
{
    protected $xmlns = "http://www.abrasf.org.br/nfse.xsd";

    /**
     * Método usado para gerar o XML do Soap Request
     * @param $versao
     * @param $remetenteTipoDoc
     * @param $remetenteCNPJCPF
     * @param $inscricaoMunicipal
     * @param $lote
     * @param $rpss
     * @return string
     */
    public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $inscricaoMunicipal,
        $lote,
        $rpss,
        $certificado = null
    )
    {
        $method = 'EnviarLoteRpsSincrono';
        $xsd = "EnviarLoteRpsSincronoEnvio";
        $qtdRps = count($rpss);


        $dom = new Dom('1.0', 'utf-8');
        $dom->formatOutput = false;
        //Cria o elemento pai
        $root = $dom->createElement('EnviarLoteRpsSincronoEnvio');
        $root->setAttribute('xmlns', $this->xmlns);

        //Adiciona as tags ao DOM
        $dom->appendChild($root);

        $loteRps = $dom->createElement('LoteRps');
        $ano = date('Y');
        $documentoFormatado = str_pad($remetenteCNPJCPF, 14, '0', STR_PAD_LEFT);
        $loteFormatado = str_pad($lote, 16, '0', STR_PAD_LEFT);
        $loteRps->setAttribute('Id', "1{$ano}{$documentoFormatado}{$loteFormatado}");
        $loteRps->setAttribute('versao', '20.01');

        $dom->appChild($root, $loteRps, 'Adicionando tag LoteRps a EnviarLoteRpsEnvio');


        $dom->addChild(
            $loteRps,
            'NumeroLote',
            $lote,
            true,
            "Numero do lote RPS",
            true
        );

        /* CPF CNPJ */
        $cpfCnpj = $dom->createElement('CpfCnpj');

        if ($remetenteTipoDoc == '2') {
            $tag = 'Cnpj';
        } else {
            $tag = 'Cpf';
        }
        //Adiciona o Cpf/Cnpj na tag CpfCnpj
        $dom->addChild(
            $cpfCnpj,
            $tag,
            $remetenteCNPJCPF,
            true,
            "Cpf / Cnpj",
            true
        );
        $dom->appChild($loteRps, $cpfCnpj, 'Adicionando tag CpfCnpj ao Prestador');

        /* Inscrição Municipal */
        $dom->addChild(
            $loteRps,
            'InscricaoMunicipal',
            $inscricaoMunicipal,
            false,
            "Inscricao Municipal",
            false
        );

        /* Quantidade de RPSs */
        $dom->addChild(
            $loteRps,
            'QuantidadeRps',
            $qtdRps,
            true,
            "Quantidade de Rps",
            true
        );

        /* Lista de RPS */
        $listaRps = $dom->createElement('ListaRps');
        $dom->appChild($loteRps, $listaRps, 'Adicionando tag ListaRps a LoteRps');
        foreach ($rpss as $rps) {
            RenderRps::appendRps($rps, $this->timezone, $certificado ?: $this->certificate, $this->algorithm, $dom, $listaRps);
        }

        if ($qtdRps == 1) {
            //Parse para XML
            $xml = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $dom->saveXML());

            $body = Signer::sign(
                $certificado ?: $this->certificate,
                $xml,
                'InfDeclaracaoPrestacaoServico', // assina esta tag
                'Id',
                $this->algorithm,
                [false, false, null, null],
                '',
                true
            );

            $domSigned = new \DOMDocument('1.0', 'utf-8');
            $domSigned->loadXML($body);

            // pega o nó <Signature> gerado
            $signature = $domSigned->getElementsByTagName('Signature')->item(0);

            if ($signature) {
                // pega o nó pai (tcDeclaracaoPrestacaoServico)
                $inf = $domSigned->getElementsByTagName('InfDeclaracaoPrestacaoServico')->item(0);
                $parent = $inf->parentNode;

                // insere a assinatura logo após o InfDeclaracaoPrestacaoServico
                if ($inf->nextSibling) {
                    $parent->insertBefore($signature, $inf->nextSibling);
                } else {
                    $parent->appendChild($signature);
                }
            }

            // limpa o xml final
            $body = $this->clear($domSigned->saveXML());
        }
        else {
            //Parse para XML
            $xml = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $dom->saveXML());

            $body = Signer::sign(
                $certificado ?: $this->certificate,
                $xml,
                'LoteRps',
                'Id',
                $this->algorithm,
                [false, false, null, null],
                '',
                true
            );

            $body = $this->clear($body);
        }

        return $body;
    }
}
