<?php

class ElectrumServiceClass
{
    /**
    * $debugTrigger: BOOL
    */
    public $debugTrigger;

    /**
    * $electrumHost: CHAR
    */
    public $electrumHost;

    /**
    * $electrumPort: INT
    */
    public $electrumPort;

    /**
    * $electrumUserName: CHAR
    */
    public $electrumUserName;

    /**
    * $electrumPassword: CHAR
    */
    public $electrumPassword;

    /**
    * $electrumStatus: BOOL
    */
    public $electrumStatus = false;

    /**
    * $electrumCallMethod: STRING
    */
    // var $electrumCallMethod;

    /**
    * $electrumCallParameters: ARRAY
    */
    // var $electrumCallParameters;

    /**
    * $address: STRING
    */
    public $address;

    /**
    * $cURLData: array
    */
    public $cURLData = array( "id" => "php cURL", "method" => "", "params" => [] );

    /**
     * Construct
     */
    public function __construct($conectionParams, $electrumCallParams, $debugTrigger = false)
    {
        if (!$conectionParams['electrtumHost'] or !$conectionParams['electrumPort'] or !$conectionParams['electrumUserName'] or !$conectionParams['electrumPassword']) {
            return false;
        }
        
        $this->electrumHost             = $conectionParams['electrtumHost'];
        $this->electrumPort             = $conectionParams['electrumPort'];
        $this->electrumUserName         = $conectionParams['electrumUserName'];
        $this->electrumPassword         = $conectionParams['electrumPassword'];
        // $this->electrumCallMethod       = $electrumCallParams['callMethod'];
        // $this->electrumCallParameters   = $electrumCallParams['callParameters'];

        if ($this->callCURL($this->cURLData) == null) {
            return false;
        }

        $this->electrumStatus = true;

        return true;
    }

    public function setcURLData($electrumCallParams)
    {

    // $this->electrumCallMethod      = $electrumCallParams['callMethod'];
        // $this->electrumCallParameters  = $electrumCallParams['callParameters'];
        $this->cURLData['method']      = $electrumCallParams['callMethod'];
        $this->cURLData['params']      = $electrumCallParams['callParameters'];
    }

    public function listRequests()
    {
        $this->cURLData['method']      = 'listrequests';

        return print_r($this->callCURL(), true);
        return json_decode($this->callCURL());
    }

    public function listAllRequests()
    {
        $this->cURLData['params']     = [];
        return $this->listRequests();
    }
  
    public function listExpiredRequests()
    {
        $this->cURLData['params']      = 'expired';
        return $this->listRequests();
    }

    public function listPendingRequests()
    {
        $this->cURLData['params']      = 'pending';
        return $this->listRequests();
    }

    public function listPaidRequests()
    {
        $this->cURLData['params']      = 'paid';
        return $this->listRequests();
    }

    public function addRequest($parameters)
    {
        if (!$parameters['amount'] or !is_numeric($parameters['amount'])) {
            return false;
        }

        $this->cURLData['method']      = 'addrequest';
        $this->cURLData['params']      = ['amount' => $parameters['amount'], 'expiration' => $parameters['expiration'], 'memo' => $parameters['memo'], 'force' => true ];

        return $this->callCURL();
    }

    private function callCURL()
    {
        $data_string = json_encode($this->cURLData);
    
        $curl = curl_init($this->electrumHost);
        curl_setopt($curl, CURLOPT_PORT, $this->electrumPort);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->electrumUserName}:{$this->electrumPassword}");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 5000); // 5 seconds timeout
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
        $curlResponse = curl_exec($curl);
    
        if (curl_errno($curl)) {
            error_log("cURL Error: " . curl_error($curl));
            curl_close($curl);
            return ['error' => 'cURL Error: ' . curl_error($curl)];
        }
    
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    
        if ($httpCode !== 200) {
            return ['error' => "HTTP Error Code: $httpCode"];
        }
    
        $decodedResponse = json_decode($curlResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON response from Electrum server'];
        }
    
        return $decodedResponse;
    }
    

    public function listSpecificRequest($address)
    {
        $response = json_decode($this->listRequests(), true);
        if (isset($response['result']) && is_array($response['result'])) {
            foreach ($response['result'] as $key => $value) {
                if ($value['address'] === $address) {
                    return $value;
                }
            }
        }
        return false;
    }
    

    public function getRequest($address)
    {
        /*
         * {"result": {"time": 1555190060, "amount": 8728674, "exp": 60, "address": "19sZvJZQNaD7RxEDPA1NP5wTiqghTRyYY4", "memo": "Licenses App", "id": "1a63338ff9", "URI": "bitcoin:19sZvJZQNaD7RxEDPA1NP5wTiqghTRyYY4?amount=0.08728674", "status": "Expired", "amount (BTC)": "0.08728674"}, "id": "curltext", "error": null}
         */
        $this->cURLData['params']      = ['key' => $address];
        $this->cURLData['method']      = 'getrequest';
        return ($this->callCURL());
    }

    public function getAddressBalance($address)
    {
        /*
         * {"result": {"confirmed": "0", "unconfirmed": "0"}, "id": "curltext", "error": null}[
         */
        $this->cURLData['params']      = ['address' => $address];
        $this->cURLData['method']      = 'getaddressbalance';
        return ($this->callCURL());
    }

    public function setAddress($address)
    {
        if ($address) {
            $this->address = $address;
            return $this->address;
        }
        return false;
    }

    public function rmRequest()
    {
        if (!$this->address) {
            return false;
        }

        // if ( !$parameters['amount'] or !is_numeric ( $parameters['amount'] ) ) return false;

        // $this->electrumCallMethod      = 'rmrequest';
        // $this->electrumCallParameters  = $this->address;
        $this->cURLData['method']      = 'rmrequest';
        $this->cURLData['params']      = ['address' => $this->address ];

        return $this->callCURL();
    }

    public static function getRequestStatus($status) {

        if ($status == 0) return '0 (Pending)';
        if ($status == 1) return '1 (Expired)';
        if ($status == 2) return '2 (Unknown)';
        if ($status == 3) return '3 (Paid)';
        if ($status == 4) return '4 (Inflight)';
        if ($status == 5) return '5 (Failed)';
        if ($status == 6) return '6 (Routing Update)';
        if ($status == 7) return '7 (Unconfirmed)';
        return 'Status not recognized';
    }
}
