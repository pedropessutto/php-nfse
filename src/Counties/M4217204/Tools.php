<?php

namespace NFePHP\NFSe\Counties\M4217204;

/**
 * Classe para a comunicação com os webservices da
 * Cidade de São Miguel do Oeste SC
 * conforme o modelo ABRASF Publica
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Counties\M4203006\Tools
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Maykon da S. de Siqueira <maykon at multilig dot com dot br>
 * @link      http://github.com/nfephp-org/sped-nfse for the canonical source repository
 */

use NFePHP\NFSe\Models\Publica\Tools as ToolsPublica;

class Tools extends ToolsPublica
{
    /**
     * Webservices URL
     * @var array
     */
    protected $url = [
        1 => 'http://e-gov.betha.com.br/e-nota-contribuinte-ws/nfseWS?wsdl',
        2 => 'http://e-gov.betha.com.br/e-nota-contribuinte-test-ws/nfseWS?wsdl'
    ];
    /**
     * County Namespace
     * @var string
     */
    protected $xmlns = '';
    /**
     * Soap Version
     * @var int
     */
    protected $soapversion = SOAP_1_2;
    /**
     * SIAFI County Cod
     * @var int
     */
    protected $codcidade = 8339;
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
    protected $versao = 300;
    /**
     * namespaces for soap envelope
     * @var array
     */
    protected $namespaces = [
        'xmlns:S'   => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xmlns:ns2' => 'http://service.nfse.integracao.ws.publica/'
    ];
}
