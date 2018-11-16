<?php

/**
 * Este arquivo é responsável pela inicialização do sistema
 * 
 * Você pode definir o namespace pelo composer ou descomentar o código abaixo
 * do loader para utilizar seu próprio namespace.
 */

$loader = require(__DIR__.'/../vendor/autoload.php');

// $loader->addPsr4("App\\", __DIR__);

// Arquivo de configurações
require_once(__DIR__."/config.php");

// Inicialização da Slim Framework
require_once __DIR__."/startup.php";

// Dependências do sistema
require_once __DIR__."/dependencies.php";

// Tratamento de erros
require_once __DIR__."/errors.php";

// Middlewares
require_once __DIR__."/middlewares.php";

// Rotas
require_once __DIR__."/routes.php";

// Executa a aplicação
$app->run();