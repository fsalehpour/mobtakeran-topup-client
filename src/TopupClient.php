<?php
/**
 * Author: Faramarz Salehpour
 * E-mail: subs@marz.co
 * Date: 21/10/17
 * Time: 12:21
 */

namespace FSalehpour\Mobtakeran;


use SoapClient;
use stdClass;

class TopupClient
{
    const CHARGE_DIRECT = 0;
    const CHARGE_IRANCELL_SPECIAL = 1;
    const CHARGE_RIGHTEL_SPECIAL = 2;

    const DEVICE_ATM = 2;
    const DEVICE_BRANCH = 3;
    const DEVICE_IVR = 7;
    const DEVICE_KIOSK = 13;
    const DEVICE_POS = 14;
    const DEVICE_INTERNET = 59;
    const DEVICE_MOBILE_APP = 5;
    const DEVICE_USSD = 6;

    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;

    protected $soapClient;
    protected $username;
    protected $password;

    /**
     * TopupClient constructor.
     * @param SoapClient|null $client
     * @internal param $soapClient
     */
    public function __construct(SoapClient $client = null, string $username = null, string $password = null)
    {
        $this->soapClient = $client ?? $this->soapClientFactory();
        $this->username = $username ?? getenv('MOBTAKERAN_USERNAME');
        $this->password = $password ?? getenv('MOBTAKERAN_PASSWORD');
    }

    /**
     * @return SoapClient
     * @internal param $wsdl
     * @internal param $options
     */
    protected function soapClientFactory(): SoapClient
    {
        $wsdl = getenv('MOBTAKERAN_WSDL');

        $options = [
            'trace' => 1,
            'soap_version' => getenv('MOBTAKERAN_SOAP_VERSION'),
            'uri' => getenv('MOBTAKERAN_URI'),
            'location' => getenv('MOBTAKERAN_LOCATION'),
        ];

        return new SoapClient($wsdl, $options);
    }

    /**
     * @param array $params
     * @return stdClass
     * @internal param $localDateTime
     */
    protected function prepareParams(array $params = []): stdClass
    {
        $allParams = array_merge([
            'Username' => $this->username,
            'Password' => $this->password,
        ], $params);

        $allParams['LocalDateTime'] = $allParams['LocalDateTime'] ?? date('c');
        return (object)['req' => $allParams];
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function callMethod($method, $params)
    {
        return $this->soapClient->{$method}($params);
    }

    public function reserveCharge($reserveNumber, $totalAmount, $cellNumber, $chargeType, $deviceType, $localDateTime = null)
    {
        $params = $this->prepareParams([
            'ReserveNumber' => $reserveNumber,
            'TotalAmount' => $totalAmount,
            'CellNumber' => $cellNumber,
            'ChargeType' => $chargeType,
            'DeviceType' => $deviceType,
            'LocalDateTime' => $localDateTime,
        ]);
        return $this->callMethod('ReserveCharge', $params);
    }

    public function approve($reserveNumber, $referenceNumber, $localDateTime = null)
    {
        $params = $this->prepareParams([
            'ReserveNumber' => $reserveNumber,
            'ReferenceNumber' => $referenceNumber,
            'LocalDateTime' => $localDateTime,
        ]);
        return $this->callMethod('Approve', $params);
    }

    public function checkRequest($reserveNumber, $referenceNumber, $localDateTime = null)
    {
        $params = $this->prepareParams([
            'ReserveNumber' => $reserveNumber,
            'ReferenceNumber' => $referenceNumber,
            'LocalDateTime' => $localDateTime,
        ]);
        return $this->callMethod('CheckRequest', $params);
    }

    public function checkBalance($chargeType, $localDateTime = null)
    {
        $params = $this->prepareParams([
            'ChargeType' => $chargeType,
            'LocalDateTime' => $localDateTime,
        ]);
        return $this->callMethod('CheckBalance', $params);
    }

    public function reverseRequest($reserveNumber, $referenceNumber, $localDateTime = null)
    {
        $params = $this->prepareParams([
            'ReserveNumber' => $reserveNumber,
            'ReferenceNumber' => $referenceNumber,
            'LocalDateTime' => $localDateTime,
        ]);
        return $this->callMethod('ReverseRequest', $params);
    }

    public function checkServer($localDateTime = null)
    {
        $params = $this->prepareParams(['LocalDateTime' => $localDateTime]);
        return $this->callMethod('CheckServer', $params);
    }

}
