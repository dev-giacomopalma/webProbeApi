<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
/**
 * Movie controller.
 * @Route("/api", name="api_")
 */
class LaunchPadController extends AbstractFOSRestController
{
    /**
     * Lists all Movies.
     * @Rest\Get("/mission")
     *
     * @return Response
     */
    public function getMovieAction()
    {
        return $this->handleView($this->view(['ok get']));
    }
    /**
     * Create Movie.
     * @Rest\Post("/mission")
     *
     * @return Response
     */
    public function postMovieAction(Request $request)
    {
        return $this->handleView($this->view([$request->request->get('mission')]));
    }
}