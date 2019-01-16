<?php declare(strict_types=1);

namespace App\Controllers;

use Pollus\Mvc\Controller\MvcController;

class HomeController extends MvcController
{
    /**
     * @method GET
     */
    public function index()
    {
        return $this->view->Render('Pages/Index.twig');
    }
}
