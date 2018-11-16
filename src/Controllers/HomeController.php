<?php

namespace App\Controllers;

use Pollus\Mvc\Controller\MvcController;

/**
 * Todos os controllers devem estender a classe {@see MvcController}
 */
class HomeController extends MvcController
{
    /**
     * @method GET
     */
    public function index()
    {
        return $this->view->Render('Paginas/Index.twig');
    }
}
