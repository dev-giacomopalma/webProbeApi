<?php

namespace App\Controller;

use App\Classes\Exceptions\ExceptionMapper;
use App\Classes\LaunchPad\ApiLaunchPad;
use App\Classes\Mission\ApiMission;
use App\Classes\Probe\ApiProbe;
use App\Entity\RequestCount;
use App\Entity\RequestResponse;
use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Services\WebProbe\Missions\Settings\MissionSetting;
use App\Services\WebProbe\Probes\Settings\ProbeSetting;

class LaunchPadController extends AbstractController
{

    /** @var LoggerInterface */
    private $loggerInterface;

    /**
     * Launch a mission.
     * @Route("/api/missionRequest", name="api_post_mission_request")
     *
     * @return Response
     */
    public function missionRequest(Request $request, LoggerInterface $logger): Response
    {
        if (false === json_decode($request->getContent(), true)) {
            return $this->returnError(new InvalidArgumentException('Request format is invalid'));
        }

        $this->loggerInterface = $logger;
        if ($request->getMethod() !== 'POST') {
            return $this->redirectToRoute('default_request');
        }

        try {
            $this->denyAccessUnlessGranted('ROLE_USER', null, 'You have no access to this endpoint');
        } catch (AccessDeniedException $exception) {
            return $this->returnError($exception);
        }


        $data = $request->request->get('data');
        if (null !== $data) {
            $this->rateLimitRequest($request);
            $cachedResponse = $this->findCachedResponse($request);
            if (null !== $cachedResponse) {
                return $this->json(json_decode($cachedResponse));
            }
            $probeSetting = new ProbeSetting($data['url'], $data['preparation'] ?? []);
            $probe = new ApiProbe($probeSetting);
            $missionSetting = new MissionSetting($data['resultType'], $data['evaluation'] ?? []);
            $mission = new ApiMission($missionSetting, $probe);
            $launchPad = new ApiLaunchPad($mission);

            try {
                $missionResult = $launchPad->launch();
            } catch (Exception $exception) {
                return $this->returnError($exception);
            }

            $response = ['data' => $missionResult->getPayload()];

            $response = self::cleanResults($response);
            $this->persistRequestResponse($request, $response);
            return $this->json($response);
        }

    }

    /**
     * Return default response for invalid endpoint.
     * @Route("/", name="default_request")
     *
     * @return Response
     */
    public function defaultResponse(LoggerInterface $logger): Response {
        $response = ['data' => ['ERROR' => 'invalid route']];
        return $this->json($response);
    }

    private function persistRequestResponse(Request $request, array $response): void
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

    private function findCachedResponse(Request $request)
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

    private function rateLimitRequest(Request $request): void
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

    private function returnError(Exception $exception): Response
    {
        $response = [
            'errorCode' => ExceptionMapper::mapExceptionToErrorCode($exception),
            'errorMessage' => $exception->getMessage()
        ];
        return $this->json($response);
    }

    private static function cleanResults($data)
    {
        if (is_string($data)) {
            if (mb_detect_encoding($data, 'UTF-8', true) === false) {
                $data = mb_convert_encoding($data, 'UTF-8', 'iso-8859-1');
            }
            return $data;
        }

        if (is_array($data)) {
            $ret = [];
            foreach ($data as $i => $d) {
                $ret[$i] = self::cleanResults($d);
            }

            return $ret;
        }

        if (is_object($data)) {
            foreach ($data as $i => $d) {
                $data->$i = self::cleanResults($d);
            }

            return $data;
        }

        return $data;
    }
}
