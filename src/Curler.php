<?php


namespace UWDOEM\CRM\Curler;


class Curler
{
    protected $apibase;
    protected $apiusername;
    protected $apipassword;
    protected $defaultOptions;

    public function __construct($apibase, $apiusername, $apipassword)
    {
        $this->apibase = $apibase;
        $this->apiusername = $apiusername;
        $this->apipassword = $apipassword;

        $this->defaultOptions = [
            CURLOPT_USERPWD => "{$this->apiusername}:{$this->apipassword}",
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ];
    }

    protected static function processHeader($header, &$responseHeaders)
    {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) // ignore invalid headers
            return $len;

        $name = strtolower(trim($header[0]));
        if (!array_key_exists($name, $responseHeaders))
            $responseHeaders[$name] = [trim($header[1])];
        else
            $responseHeaders[$name][] = trim($header[1]);

        return $len;

    }

    public function get($location, $query = [], $requestHeaders = [], &$responseHeaders = [], &$responseCode = null)
    {
        $thisClass = static::class;

        $options = [
            CURLOPT_URL => rtrim($this->apibase, '/') . '/' . $location . '?' . http_build_query($query),
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$responseHeaders, $thisClass)
            {
                return $thisClass::processHeader($header, $responseHeaders);
            },
        ];

        $ch = curl_init();
        curl_setopt_array($ch, ($this->defaultOptions + $options));

        $result = curl_exec($ch);
        $responseCode = curl_getinfo($ch)['http_code'];
        curl_close($ch);

        return $result;
    }

    public function post($location, $body, $requestHeaders = [], &$responseHeaders = [], &$responseCode = null)
    {
        $thisClass = static::class;

        $options = [
            CURLOPT_URL => rtrim($this->apibase, '/') . '/' . $location,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$responseHeaders, $thisClass)
            {
                return $thisClass::processHeader($header, $responseHeaders);
            },
        ];

        $ch = curl_init();
        curl_setopt_array($ch, ($this->defaultOptions + $options));

        $result = curl_exec($ch);
        $responseCode = curl_getinfo($ch)['http_code'];
        curl_close($ch);

        return $result;
    }

    public function delete($location, $requestHeaders = [], &$responseHeaders = [], &$responseCode = null)
    {
        /** @var $this $thisClass */
        $thisClass = static::class;

        $options = [
            CURLOPT_URL => rtrim($this->apibase, '/') . '/' . $location,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$responseHeaders, $thisClass)
            {
                return $thisClass::processHeader($header, $responseHeaders);
            },
        ];

        $ch = curl_init();
        curl_setopt_array($ch, ($this->defaultOptions + $options));

        $result = curl_exec($ch);
        $responseCode = curl_getinfo($ch)['http_code'];
        curl_close($ch);

        return $result;
    }
}
