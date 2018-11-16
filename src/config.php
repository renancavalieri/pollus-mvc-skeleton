<?php

/**
 * Configurações do sistema
 */

$config = array
(
    // Exibição de erros da Slim
    'displayErrorDetails' => true,

    // Configura se o Whoops será exibido
    'debug' => true,

    // Configura se o Whoops será restrito a determinados IPs [false|array]
    'debug_allow_from_ip_only' => ['127.0.0.1'],

    // Namespace
    'namespace' => 'App',

    //Namespace de seus controllers
    'controllers_namespace' => 'App\\Controllers',   

    // Locais dos arquivos de template
    'templates' =>
    [
        __DIR__ . "/Views",
    ],

    // Caminho para a pasta de templates, utilize "false" para desabilitar
    "templates_cache" => false,

    // Variáveis padrão que serão enviadas para a aplicação
    'default_vars' => 
    [
        'app_title' => "Pollus Hello World"
    ],

    // Ajuste do timezone
    'timezone' => 'America/Sao_Paulo'
);