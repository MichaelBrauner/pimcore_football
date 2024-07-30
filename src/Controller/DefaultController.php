<?php

namespace App\Controller;

use Pimcore\Bundle\AdminBundle\Controller\Admin\LoginController;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\Team;
use Pimcore\Model\DataObject\Team\Listing as TeamListing;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends FrontendController
{
    #[Route('/', name: 'app:index')]
    public function defaultAction(): Response
    {
        $teams = (new TeamListing());

        return $this->render('default/default.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/team/{id}', name: 'app:team_detail')]
    public function detail(int $id): Response
    {
        $team = Team::getById($id);

        return $this->render('football/team-detail.html.twig', [
            'team' => $team,
        ]);
    }

    /**
     * Forwards the request to admin login
     */
    public function loginAction(): Response
    {
        return $this->forward(LoginController::class . '::loginCheckAction');
    }
}
