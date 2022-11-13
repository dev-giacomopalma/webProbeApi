<?php

namespace App\Controller;

use App\Controller\Helper\ControllerHelper;
use App\Entity\RequestResponse;
use App\PersistenceLayer\RequestResponsePersistance;
use App\Services\WebProbe\LaunchPad\LaunchPad;
use App\Services\WebProbe\Missions\Mission;
use App\Services\WebProbe\Probes\Probe;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class APIController extends AbstractController
{

    /** @var RequestResponsePersistance */
    private $requestResponsePersistenceLayer;

    public function __construct(RequestResponsePersistance $requestResponsePersistenceLayer) {
        $this->requestResponsePersistenceLayer = $requestResponsePersistenceLayer;
    }

    /**
     * Launch a mission.
     * @Route("/api/missionRequest", name="api_post_mission_request")
     *
     * @return Response
     */
    public function missionRequest(Request $request, LoggerInterface $logger): Response
    {
        if (false === json_decode($request->getContent(), true)) {
            return $this->json(
                ControllerHelper::returnError(new InvalidArgumentException('Request format is invalid'))
            );
        }

        if ($request->getMethod() !== 'POST') {
            return $this->redirectToRoute('default_request');
        }

        try {
            $this->denyAccessUnlessGranted('ROLE_USER', null, 'You have no access to this endpoint');
        } catch (AccessDeniedException $exception) {
            return $this->json(ControllerHelper::returnError($exception));
        }


        $data = $request->request->get('data');
        if (null !== $data) {
            $this->requestResponsePersistenceLayer->rateLimitRequest($request);

            /** @var RequestResponse $cachedResponse */
            $cachedResponse = $this->requestResponsePersistenceLayer->findCachedResponse($request);
            $logger->info($cachedResponse !== null ? 'cache used' : 'no cache used');
            if (null !== $cachedResponse && null != $cachedResponse->getResponse()) {
                $response = json_decode($cachedResponse->getResponse());
                $response->uuid = $cachedResponse->getUuid();
                return $this->json($response);
            }
            $probe = new Probe($data['url'], $data['preparation'] ?? []);
            $mission = new Mission($probe, $data['resultType'], $data['evaluation'] ?? []);
            $launchPad = new LaunchPad($mission);

            try {
                $missionResult = $launchPad->launch();
            } catch (Exception $exception) {
                return $this->json(ControllerHelper::returnError($exception));
            }

            $response = ['data' => $missionResult->getPayload()];

            $response = ControllerHelper::cleanResults($response);

            $missionId = $this->requestResponsePersistenceLayer->persistRequestResponse($request, $response);
            $response['missionId'] = $missionId;

            return $this->json($response);
        }

    }

    /**
     * Launch a mission.
     * @Route("/api/mission/{uuid}", name="api_get_mission")
     * @param string $uuid
     * @param Request $request
     *
     * @return Response
     */
    public function mission(string $uuid, Request $request): Response
    {

        if ($request->getMethod() !== 'GET') {
            return $this->redirectToRoute('default_request');
        }

        try {
            $this->denyAccessUnlessGranted('ROLE_USER', null, 'You have no access to this endpoint');
        } catch (AccessDeniedException $exception) {
            return $this->json(ControllerHelper::returnError($exception));
        }

        if (!empty($uuid)) {
            $this->requestResponsePersistenceLayer->rateLimitRequest($request);

            /** @var RequestResponse $response */
            $response = $this->requestResponsePersistenceLayer->findByUuidAndUser($uuid, $request->getUser());
            if (null !== $response && null != $response->getResponse()) {
                return $this->json(json_decode($response->getResponse()));
            } else {
                return $this->json(['data' => 'not found'], 404);
            }
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
}
