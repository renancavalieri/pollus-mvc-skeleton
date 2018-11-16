<?php

/**
 * Defina as rotas de sua aplicação neste arquivo
 * 
 * @link https://www.slimframework.com/docs/v3/objects/router.html Router
 */

use Pollus\Slim\Dispatcher\SlimDispatcher;

/**
 * Mvc Dispatcher
 */
$mvc = new SlimDispatcher($config["controllers_namespace"], $container);

/**
 * Index
 */
$app->any('/', function ($request, $response, $args) use ($mvc)
{
    return $mvc
        ->setController("home")
        ->setMethod("index")
        ->prepare($request, $response, $args)
        ->validateMethodType()
        ->run();
});

/**
 * ROTA GENÉRICA
 * 
 * O pacote Pollus/Mvc trabalha por padrão com uma rota genérica, onde a classe
 * {@see MvcDispatcher} recebe o controller, método e argumentos
 * e instancia um {@see ControllerReflection}, no entanto
 * você pode personalizar suas rotas como na Slim Framework, e informar os 
 * argumentos manualmente para o Dispatcher ou instanciar diretamente o ControllerReflection
*/
$app->any('/{controller}/{method}[/{params:.*}]', function ($request, $response, $args) use ($mvc)
{
    return $mvc
        ->prepare($request, $response, $args)
        ->validateMethodType()
        ->run();
});