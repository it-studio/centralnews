<?php

namespace CentralNews\Service;

use CentralNews\Exception\Exception;

class SoapClient
{
    const TOKEN = 'api_key';
    const USER = 'api_name';
    const PASSWORD = 'api_pass';

    /** @var array */
    protected $headers;

    /** @var string */
    protected $url = 'http://localhost/';

    /** @var string */
    protected $encoding = 'UTF-8';

    protected function create()
    {
        $nuSoap = new \nusoap_client($this->getUrl() . '?wsdl', 'wsdl', false, false, false, false, 0, 10);
        $nuSoap->soap_defencoding = $this->getEncoding();

        if ($nuSoap->getError()) {
            throw new Exception('SoapClient');
        }

        return $nuSoap;
    }

    public function sendRequest(Request $request)
    {
        $rawResponse = $this->create()->call($request->getOperation(), $request->getParams(), $request->getNamespace(), $request->getAction(), $this->getHeaders());
        return new Response($rawResponse);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

}