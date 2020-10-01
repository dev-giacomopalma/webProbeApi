<?php
namespace App\Controller;
use App\Classes\Mission\ApiMission;
use App\Classes\Mission\Setting\ApiMissionSetting;
use App\Classes\Probe\ApiProbe;
use App\Classes\Probe\Setting\ApiProbeSetting;
use App\Entity\RequestCount;
use App\Entity\RequestResponse;
use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Constraints\Date;
use twittingeek\webProbe\LaunchPad\LaunchPad;
use twittingeek\webProbe\Missions\Settings\MissionSetting;
use twittingeek\webProbe\Probes\Settings\ProbeSetting;

/**
 * Movie controller.
 * @Route("/api", name="api_")
 */
class LaunchPadController extends AbstractFOSRestController
{

    /**
     * Launch a mission.
     * @Rest\Post("/missionRequest")
     *
     * @return Response
     */
    public function missionRequest(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
        $this->rateLimitRequest($request);
        $cachedResponse = $this->findCachedResponse($request);

        if (null !== $cachedResponse) {
            return $this->json(json_decode($cachedResponse));
        }

        $data = $request->request->get('data');
        if (null !== $data) {
            $probeSetting = new ProbeSetting($data['url'], $data['preparation'] ?? []);
            $probe = new ApiProbe($probeSetting);
            $missionSetting = new MissionSetting($data['resultType'], $data['evaluation'] ?? []);
            $mission = new ApiMission($missionSetting, $probe);
            $launchPad = new LaunchPad($mission);

            $missionResult = $launchPad->launch();

            $response = ['data' => $missionResult->getPayload()];

            $this->persistRequestResponse($request, $response);
            return $this->json($response);
        }

    }

    private function persistRequestResponse(Request $request, array $response)
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
        $repository = $this->getDoctrine()->getRepository(RequestResponse::class);
        $data = $request->request->get('data');
        $cacheDate = new DateTime('now');
        $cacheDate->sub(new DateInterval('PT1H')); // will be fixed with timezones
        /** @var RequestResponse $requestResponse */
        $requestResponse = $repository->findOneBy(['md5Request' => md5(json_encode($data))]);

        if ((null !== $requestResponse) && $requestResponse->getCreationDate() > $cacheDate) {
            return $requestResponse->getResponse();
        }

        return null;
    }

    private function rateLimitRequest(Request $request)
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