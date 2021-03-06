<?php
 /**
 * This file is part of the Certificationy web platform.
 * (c) Johann Saunier (johann_27@hotmail.fr)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Gundam\Component\Github;

use Gundam\Component\Github\Common\RequestableInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;

class Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzleClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    const BASE_URL_API = 'https://api.github.com';

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->guzzleClient = new \GuzzleHttp\Client([
            'base_url' => static::BASE_URL_API,
            'defaults' => [
                'auth' => [$username, $password]
            ]
        ]);
    }

    /**
     * @param RequestableInterface $apiRequest
     *
     * @return bool|\GuzzleHttp\Message\ResponseInterface
     */
    public function send(RequestableInterface $apiRequest)
    {
        $request = $this->guzzleClient->createRequest(
            $apiRequest->getMethod(),
            $apiRequest->getUrl(),
            $apiRequest->getOptions()
        );

        try {
            return $this->guzzleClient->send($request);
        } catch (ClientException $e) {

            if (null !== $this->logger) {
                $this->logger->alert($e->getMessage());
            }

            return false;
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }
}
