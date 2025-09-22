<?php

namespace NFePHP\NFSe\Models\Tecnos\Factories;

use NFePHP\NFSe\Models\Tecnos\Factories\Factory;

abstract class ConsultarNfsePorRps extends Factory
{
    protected $xmlns;
    protected $schemeFolder;
    protected $cmun;

    /**
     * Método usado para gerar o XML do Soap Request
     * @param $versao
     * @param $remetenteTipoDoc
     * @param $remetenteCNPJCPF
     * @param $inscricaoMunicipal
     * @param $numero
     * @param $serie
     * @param $tipo
     * @return mixed
     */
    abstract public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $inscricaoMunicipal,
        $numero,
        $serie,
        $tipo
    );

    /**
     * @param $xmlns
     */
    public function setXmlns($xmlns)
    {
        $this->xmlns = $xmlns;
    }

    /**
     * @param $schemeFolder
     */
    public function setSchemeFolder($schemeFolder)
    {
        $this->schemeFolder = $schemeFolder;
    }

    /**
     * @param $cmun
     */
    public function setCodMun($cmun)
    {
        $this->cmun = $cmun;
    }   
}
