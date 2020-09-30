<?php
namespace App\Controller;
use App\Classes\Mission\ApiMission;
use App\Classes\Mission\Setting\ApiMissionSetting;
use App\Classes\Probe\ApiProbe;
use App\Classes\Probe\Setting\ApiProbeSetting;
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
        $requestResponse = new RequestResponse();
        $requestResponse->setMd5Request(md5(json_encode($request)));
        $requestResponse->setRequest(json_encode($request));
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

        $cacheDate = new DateTime('now');
        $cacheDate->sub(new DateInterval('PT1H'));
        /** @var RequestResponse $requestResponse */
        $requestResponse = $repository->findOneBy(['md5Request' => md5(json_encode($request))]);

        if ((null !== $requestResponse) && $requestResponse->getCreationDate() > $cacheDate) {
            return $requestResponse->getResponse();
        }

        return null;
    }
}