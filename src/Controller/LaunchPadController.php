<?php
namespace App\Controller;
use App\Classes\Mission\ApiMission;
use App\Classes\Mission\Setting\ApiMissionSetting;
use App\Classes\Probe\ApiProbe;
use App\Classes\Probe\Setting\ApiProbeSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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

        $data = $request->request->get('data');
        if (null !== $data) {
            $probeSetting = new ProbeSetting($data['url'], $data['preparation'] ?? []);
            $probe = new ApiProbe($probeSetting);
            $missionSetting = new MissionSetting($data['resultType'], $data['evaluation'] ?? []);
            $mission = new ApiMission($missionSetting, $probe);
            $launchPad = new LaunchPad($mission);

            $missionResult = $launchPad->launch();

            $res = ['data' => $missionResult->getPayload()];

            return $this->json($res);
        }

    }
}