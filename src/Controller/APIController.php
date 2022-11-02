<?php

namespace App\Controller;

use App\Controller\Helper\ControllerHelper;
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
            $cachedResponse = $this->requestResponsePersistenceLayer->findCachedResponse($request);
            if (null !== $cachedResponse) {
                return $this->json(json_decode($cachedResponse));
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
            $this->requestResponsePersistenceLayer->persistRequestResponse($request, $response);
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
}
