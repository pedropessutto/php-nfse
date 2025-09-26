<?php

namespace NFePHP\NFSe\Counties\M4308201;

/**
 * Classe para a comunicação com os webservices da
 * para a Cidade de Flores da Cunha RS
 * conforme o modelo Tecnos
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Counties\M4320909\Tools
 */

use DOMDocument;
use NFePHP\NFSe\Models\Tecnos\Tools as ToolsModel;

class Tools extends ToolsModel
{
    /**
     * Webservices URL
     * @var array
     */
    protected $url = [
        1 => [
            'ConsultaNFSePorRPS' => 'http://flores.nfse-tecnos.com.br:9095/ConsultaNFSePorRPS.asmx',
            'EnvioLoteRPSSincrono' => 'http://flores.nfse-tecnos.com.br:9091/EnvioLoteRPSSincrono.asmx',
            'EnvioLoteRPSSincronoComRetornoLista' => 'http://flores.nfse-tecnos.com.br:9091/EnvioLoteRPSSincrono.asmx',
        ],
        2 => [
            'ConsultaSequenciaLoteNotaRPS' => 'http://homologaflo.nfse-tecnos.com.br:9084/ConsultaSequenciaLoteNotaRPS.asmx',
            'EnvioLoteRPSSincrono' => 'http://homologaflo.nfse-tecnos.com.br:9091/EnvioLoteRPSSincrono.asmx',
            'EnvioLoteRPSSincronoComRetornoLista' => 'http://homologaflo.nfse-tecnos.com.br:9091/EnvioLoteRPSSincrono.asmx',
            'ConsultaNFSePorRPS' => 'http://homologaflo.nfse-tecnos.com.br:9095/ConsultaNFSePorRPS.asmx',
            'CancelarNfse' => 'http://homologaflo.nfse-tecnos.com.br:9098/CancelamentoNFSe.asmx',
        ]
    ];

    /**
     * Soap Version
     * @var int
     */
    protected $soapversion = SOAP_1_2;
    /**
     * SIAFI County Cod
     * @var int
     */
    protected $codcidade = '';
    /**
     * Indicates when use CDATA string on message
     * @var boolean
     */
    protected $withcdata = true;
    /**
     * Encription signature algorithm
     * @var string
     */
    protected $algorithm = OPENSSL_ALGO_SHA1;
    /**
     * Version of schemas
     * @var int
     */
    protected $versao = 100;
    /**
     * namespaces for soap envelope
     * @var array
     */
    protected $namespaces = [
        1 => [
//            'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
//            'xmlns:xsd' => "http://www.w3.org/2001/XMLSchema",
            'xmlns:soap' => "http://schemas.xmlsoap.org/soap/envelope/",
//            'xmlns:soapenv' => "http://schemas.xmlsoap.org/soap/envelope/",
//            'xmlns' => "http://tempuri.org/"
        ],
        2 => [
//            'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
//            'xmlns:xsd' => "http://www.w3.org/2001/XMLSchema",
            'xmlns:soap' => "http://schemas.xmlsoap.org/soap/envelope/",

        ]
    ];

    /**
     * @param $lote
     * @param $rpss
     * @return string
     */
    public function recepcionarLoteRpsSincrono($lote, $rpss, $certificado = null)
    {
        $class = "NFePHP\\NFSe\\Models\\Tecnos\\Factories\\v{$this->versao}\\RecepcionarLoteRps";
        $fact = new $class($this->certificate);
        $this->soapAction = 'http://tempuri.org/mEnvioLoteRPSSincronoComRetornoLista';
        $this->xmlns = 'http://www.abrasf.org.br/nfse.xsd';
        return $this->recepcionarLoteRpsSincronoCommon($fact, $lote, $rpss, '', $certificado);
    }


    /**
     * Os métodos que realizar operações no webservice precisam ser sobrescritos (Override)
     * somente para setar o soapAction espefico de cada operação (INFSEGeracao, INFSEConsultas, etc.)
     * @param $protocolo
     * @return string
     */
    public function consultarLoteRps($protocolo)
    {
        $this->soapAction = 'http://tempuri.org/ConsultarLoteRpsEnvio/';
        return parent::consultarLoteRps($protocolo);
    }

    protected function sendRequest($url, $message)
    {
        $this->xmlRequest = $message;

        if (!$url) {
            $url = $this->url[$this->config->tpAmb][$this->method];
        }

//        if (!is_object($this->soap)) {
        $this->soap = new SoapCurl($this->certificate);
//        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($message);

        $request = $this->makeRequest($message);
        $this->params = [
            "Content-Type: text/xml; charset=utf-8;",
            "SOAPAction: \"{$this->soapAction}\""
        ];
        $action = '';
        return $this->soap->send(
            $url,
            $this->method,
            $action,
            $this->soapversion,
            $this->params,
            $this->namespaces[$this->soapversion],
            $request
        );
    }
}
