<?php

namespace App\PersistenceLayer;

use App\PersistenceLayer\Entity\RequestCount;
use App\PersistenceLayer\Entity\RequestResponse;
use DateInterval;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RequestResponsePersistance extends AbstractController
{
    /** @var LoggerInterface */
    private $loggerInterface;

    public function __construct(LoggerInterface $logger) {
        $this->loggerInterface = $logger;
    }

    public function persistRequestResponse(Request $request, array $response): void
    {
        $data = $request->request->get('data');
        $requestResponse = new RequestResponse();
        $requestResponse->setMd5Request(md5(json_encode($data)));
        $requestResponse->setRequest(json_encode($data));
        $requestResponse->setResponse(json_encode($response));
        $requestResponse->setUser($request->getUser());
        $requestResponse->setClientIp($request->getClientIp());
        $requestResponse->setCreationDate(new DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($requestResponse);
        $em->flush();
    }

    public function findCachedResponse(Request $request)
    {
        if($this->isGranted('ROLE_SUPER_USER')
            && isset($data["noCache"])
            && $data["noCache"] === true) {
            return null;
        }
        $repository = $this->getDoctrine()->getRepository(RequestResponse::class);
        $data = $request->request->get('data');
        $cacheDate = new DateTime('now');
        $cacheDate->sub(new DateInterval('PT1H')); // will be fixed with timezones

        /** @var RequestResponse $requestResponse */
        $requestResponse = $repository->findBy(
            ['md5Request' => md5(json_encode($data))],
            ['id' => 'DESC'],
            1
        );
        if (null === $requestResponse
            || empty($requestResponse)
            || !array_key_exists(0, $requestResponse))
        {
            return null;
        }
        $requestResponse = $requestResponse[0];

        $this->loggerInterface->info(__METHOD__." creation date ".json_encode($requestResponse->getCreationDate()));
        if ((null !== $requestResponse) && $requestResponse->getCreationDate() > $cacheDate) {
            return $requestResponse->getResponse();
        }

        return null;
    }

    public function rateLimitRequest(Request $request): void
    {
        $repository = $this->getDoctrine()->getRepository(RequestCount::class);

        /** @var RequestCount $requestCount */
        $requestCount = $repository->findOneBy(['username' => $request->getUser()]);

        if (null === $requestCount) {
            $requestCount = new RequestCount();
            $requestCount->setCount(1);
            $requestCount->setUsername($request->getUser());
        } else {
            $requestCount->setCount($requestCount->getCount() + 1);
        }

        $requestCount->setLastRequestDate(new DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($requestCount);
        $em->flush();
    }
}