<?php


namespace UWDOEM\CRM\Curler;


class Curler
{
    /** @var string $apibase */
    protected $apibase;

    /** @var string $apiusername */
    protected $apiusername;

    /** @var string $apipassword */
    protected $apipassword;

    /** @var array $defaultOptions */
    protected $defaultOptions;

    /**
     * Create a curler object that can issue requests to a Microsoft Dynamics CRM server.
     *
     * @param string $apibase     Eg: 'https://rec-test1.s.uw.edu/Seattle/api/data/v8.0/' for the admissions test CRM server
     * @param string $apiusername Eg: 'someuser'
     * @param string $apipassword Eg: 'somepassword'
     * @param bool   $verbose     Set to `true` to print verbose cURL data to the console, ala `CURLOPT_VERBOSE`.
     */
    public function __construct($apibase, $apiusername, $apipassword, $verbose = false)
    {
        $this->apibase = $apibase;
        $this->apiusername = $apiusername;
        $this->apipassword = $apipassword;

        $this->defaultOptions = [
            CURLOPT_USERPWD => "{$this->apiusername}:{$this->apipassword}",
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE => $verbose,
        ];
    }

    /**
     * Helper function to retrieve headers from an HTTP response.
     *
     * See CURLOPT_HEADERFUNCTION in the (curl_setopt documention)[http://php.net/manual/en/function.curl-setopt.php].
     *
     * @param string $header
     * @param array  $responseHeaders
     * @return int
     */
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

    /**
     * Helper function to execute a cURL request.
     *
     * For $location, specify only the part of the URL location that follows the `$apibase` provided to the
     * constructor. Eg: if your `$apibase` is `'https://rec-test1.s.uw.edu/Seattle/api/data/v8.0/'` and you
     * give a location of `'resources/1/'`, then your request will be issued to the URL:
     * `'https://rec-test1.s.uw.edu/Seattle/api/data/v8.0/resources/1/'`.
     *
     * @param string   $location        The URL location, but just the part that follows $this->apibase.
     * @param array    $query           An array of URl query variables. Eg: ['key1' => 'value1', 'key2' => 'value2']
     * @param string   $body            The body of the HTTP request.
     * @param array    $requestHeaders  Headers to be used in the request, ala `CURLOPT_HTTPHEADER`.
     * @param array    $options         Options to be used in the request, ala `curl_setopt_array`.
     * @param array    $responseHeaders Return array for the headers from the response.
     * @param int      $responseCode    Return integer for the HTTP code of the response.
     * @return mixed
     */
    protected function doRequest($location, $query = [], $body = '', $requestHeaders = [], $options = [], &$responseHeaders = [], &$responseCode = 0)
    {
        $thisClass = static::class;

        $options = $options + [
            CURLOPT_URL => rtrim($this->apibase, '/') . '/' . $location . '?' . http_build_query($query),
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$responseHeaders, $thisClass)
            {
                return $thisClass::processHeader($header, $responseHeaders);
            },
        ];

        if ($body != '') {
            $options = $options + [
                CURLOPT_POSTFIELDS => $body,
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, ($this->defaultOptions + $options));

        $result = curl_exec($ch);
        $responseCode = curl_getinfo($ch)['http_code'];
        curl_close($ch);


        return $result;

    }

    /**
     * Execute a GET request.
     *
     * See the documentation for `doRequest` for more explanation of the `$location` argument.
     *
     * @param string   $location        The URL location, but just the part that follows $this->apibase.
     * @param array    $query           An array of URl query variables. Eg: ['key1' => 'value1', 'key2' => 'value2']
     * @param string   $body            The body of the HTTP request.
     * @param array    $requestHeaders  Headers to be used in the request, ala `CURLOPT_HTTPHEADER`.
     * @param array    $options         Options to be used in the request, ala `curl_setopt_array`.
     * @param array    $responseHeaders Return array for the headers from the response.
     * @param int      $responseCode    Return integer for the HTTP code of the response.
     * @return mixed
     */
    public function get($location, $query = [], $body = '', $requestHeaders = [], $options = [], &$responseHeaders = [], &$responseCode = 0)
    {
        $options = $options + [];

        return $this->doRequest($location, $query, $body, $requestHeaders, $options, $responseHeaders, $responseCode);
    }

    /**
     * Execute a POST request.
     *
     * See the documentation for `doRequest` for more explanation of the `$location` argument.
     *
     * @param string   $location        The URL location, but just the part that follows $this->apibase.
     * @param array    $query           An array of URl query variables. Eg: ['key1' => 'value1', 'key2' => 'value2']
     * @param string   $body            The body of the HTTP request.
     * @param array    $requestHeaders  Headers to be used in the request, ala `CURLOPT_HTTPHEADER`.
     * @param array    $options         Options to be used in the request, ala `curl_setopt_array`.
     * @param array    $responseHeaders Return array for the headers from the response.
     * @param int      $responseCode    Return integer for the HTTP code of the response.
     * @return mixed
     */
    public function post($location, $query = [], $body = '', $requestHeaders = [], $options = [], &$responseHeaders = [], &$responseCode = 0)
    {
        $options = $options + [
            CURLOPT_POST => true,
        ];

        return $this->doRequest($location, $query, $body, $requestHeaders, $options, $responseHeaders, $responseCode);
    }

    /**
     * Execute a DELETE request.
     *
     * See the documentation for `doRequest` for more explanation of the `$location` argument.
     *
     * @param string   $location        The URL location, but just the part that follows $this->apibase.
     * @param array    $query           An array of URl query variables. Eg: ['key1' => 'value1', 'key2' => 'value2']
     * @param string   $body            The body of the HTTP request.
     * @param array    $requestHeaders  Headers to be used in the request, ala `CURLOPT_HTTPHEADER`.
     * @param array    $options         Options to be used in the request, ala `curl_setopt_array`.
     * @param array    $responseHeaders Return array for the headers from the response.
     * @param int      $responseCode    Return integer for the HTTP code of the response.
     * @return mixed
     */
    public function delete($location, $query = [], $body = '', $requestHeaders = [], $options = [], &$responseHeaders = [], &$responseCode = 0)
    {
        $options = $options + [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        return $this->doRequest($location, $query, $body, $requestHeaders, $options, $responseHeaders, $responseCode);
    }
}
