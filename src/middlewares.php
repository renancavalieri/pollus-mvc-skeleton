<?php

/**
 * Defina os middlewares de sua aplicação neste arquivo
 * 
 * @link https://www.slimframework.com/docs/v3/concepts/middleware.html Middlewares
 */

use Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware;

/**
 * Adiciona o Whoops para gerenciar excessões
 */
if ($config["debug"] ?? false === true)
{
    if ($config["debug_allow_from_ip_only"] ?? false !== false)
    {
        /**
         * Esta é uma implementação simples para verificar se o IP da requisição 
         * atual poderá visualizar a tela do Whoops.
         * 
         * O cliente pode enviar QUALQUER IP, portanto é altamente recomendável
         * que não utilize o Whoops em produção para evitar o vazamento de 
         * informações confidenciais.
         * 
         * Em caso da utilização de proxies, é necessário uma implementação
         * melhor.
        */
        $ip_address = $_SERVER["REMOTE_ADDR"] ?? null;
        
        if (in_array($ip_address, $config["debug_allow_from_ip_only"]))
        {
            $app->add(new WhoopsMiddleware($app));   
        }
    }
    else
    {
        $app->add(new WhoopsMiddleware($app));   
    }
}