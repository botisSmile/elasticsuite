<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBeacon
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBeacon\Model\BeaconBeep\Exporter;

use GuzzleHttp\Ring\Client\CurlHandler;
use GuzzleHttp\Ring\Exception\ConnectException;
use GuzzleHttp\Ring\Exception\RingException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Transport
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Transport
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Transport constructor.
     * @param ConfigInterface   $config     Beacon config.
     * @param TimezoneInterface $dateTime   Datetime.
     * @param Json              $serializer Json serializer.
     * @param LoggerInterface   $logger     Logger.
     */
    public function __construct(
        ConfigInterface $config,
        TimezoneInterface $dateTime,
        Json $serializer,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    /**
     * Send a batch of raw beeps.
     *
     * @param array $items Raw beeps to send
     *
     * @return bool
     * @throws ExporterException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function send($items):bool
    {
        $handler = new CurlHandler([]);

        $request = [
            'headers'     => [
                'Content-Type' => ['application/json'],
                'Accept' => ['application/json'],
            ],
            'http_method' => 'POST',
            'url'         => $this->config->getEndpointUrl(),
            // 'client'      => ['curl' => [CURLOPT_LOW_SPEED_LIMIT => 10]],
        ];


        $body = $this->getBody($items);
        if (empty($body)) {
            return false;
        }

        $request['body'] = $body;

        // The response can be used directly as an array or using as a promise (that has already fulfilled).
        $response = $handler($request);

        if (isset($response['error']) === true) {
            if ($response['error'] instanceof ConnectException || $response['error'] instanceof RingException) {
                $exception = $response['error'];
                $this->logger->critical($exception->getMessage());
                throw new TransportException(
                    $exception->getMessage(),
                    0,
                    $response['error']
                );
            } else {
                $this->logger->critical($response['error']);
                throw new TransportException($response['error']);
            }
        }

        if (isset($response['body']) === true) {
            $response['body'] = stream_get_contents($response['body']);
        }

        if ($response['status'] >= 400 && $response['status'] < 500) {
            throw new GenericErrorException($response['body'], $response['status']);
        } elseif ($response['status'] >= 500) {
            throw new ServerErrorException($response['body'], $response['status']);
        }

        return true;
    }

    /**
     * Construct the request body according to existing items.
     *
     * @param array $items Beeps to export.
     *
     * @return string
     */
    private function getBody($items):string
    {
        $data = [];

        foreach ($items as $item) {
            $createdAtDate = $this->dateTime->date($item[BeaconBeepInterface::CREATED_AT_DATE]);
            $data[] = [
                BeaconBeepInterface::CLIENT_ID  => $item[BeaconBeepInterface::CLIENT_ID],
                BeaconBeepInterface::HOST_ID    => $item[BeaconBeepInterface::HOST_ID],
                BeaconBeepInterface::HOSTNAME   => $item[BeaconBeepInterface::HOSTNAME],
                BeaconBeepInterface::STORE_URL  => $item[BeaconBeepInterface::STORE_URL],
                BeaconBeepInterface::MAGENTO_EDITION    => $item[BeaconBeepInterface::MAGENTO_EDITION],
                BeaconBeepInterface::MAGENTO_VERSION    => $item[BeaconBeepInterface::MAGENTO_VERSION],
                BeaconBeepInterface::CREATED_AT_DATE    => $createdAtDate->format(\DateTimeInterface::ISO8601),
                BeaconBeepInterface::MODULE_DATA        => $this->serializer->unserialize($item[BeaconBeepInterface::MODULE_DATA]),
            ];
        }

        try {
            $result = $this->serializer->serialize($data);
        } catch (\InvalidArgumentException $exception) {
            $this->logger->critical('Could not prepare beep upload request body');
            $result = '';
        }

        return $result;
    }
}
