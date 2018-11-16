<?php

/**
 * Defina o tratamento de erros de sua aplicação neste arquivo
 * 
 * @link https://www.slimframework.com/docs/v3/handlers/error.html Error Handling
 */

/**
 * 404 - Não encontrado
 */
$container['notFoundHandler'] = function ($container) 
{
    return function ($request, $response) use ($container) 
    {
        $view = $container["view"]->setResponse($response);
        
        if ($request->isXhr() === true)
        {
            return $view->asJson(["success" => false, "message" => "Não encontrado"])->withStatus(404);
        }
        else
        {
            return $view->render('Errors/NotFound.twig')->withStatus(404);
        }
    };
};

/**
 * 405 - Método não permitido
 */
$container['notAllowedHandler'] = function ($container) 
{
    return function ($request, $response, $methods) use ($container) 
    {
        $view = $container["view"]->setResponse($response);
        
        if ($request->isXhr() === true)
        {
            $msg = [
                "success" => false, 
                "message" => "Método não permitido, deve ser um dos seguintes métodos: " . implode(', ', $methods),
                "allowed" => $methods
            ];
            
            return $view->asJson($msg)
                    ->withStatus(405)
                    ->withHeader('Allow', implode(', ', $methods));
        }
        else
        {
            $view["methods"] = implode(", ", $methods);
            return $view->render('Errors/MethodNotAllowed.twig')
                    ->withStatus(405)
                    ->withHeader('Allow', implode(', ', $methods));
        }
    };
};

/**
 * Tratamento de erros gerais
 */
$ehandler = function ($container) 
{
    return function ($request, $response, $exception) use ($container) 
    {
        $view = $container["view"]->setResponse($response);
                
        $show_errors = $container["settings"]["displayErrorDetails"];
        
        if ($request->isXhr() === true)
        {
            $msg = [
                "success" => false, 
                "message" => "O servidor não pode responder sua solicitação neste momento"
            ];
            
            if ($show_errors)
            {
                $msg = array_merge($msg, ["exception" => $exception]);
            }
            
            return $view->asJson($msg)
                    ->withStatus(500);
        }
        else
        {
            if ($show_errors)
            {
                $view["exception"] = $exception;
            }
            
            return $view->render('Errors/InternalServerError.twig')
                    ->withStatus(500);
        }
    };
};
$container["phpErrorHandler"] = $ehandler;
$container['errorHandler'] = $ehandler;