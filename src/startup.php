<?php

/**
 * Vetor de configurações para inicialização
 */
$app_settings["settings"] = $config;

/**
 * Ajuste de timezone
 */
if ($config["timezone"] ?? null !== null)
{
    date_default_timezone_set($config["timezone"]);
}

/**
 * Container da Slim Framework
 */
$container = new Slim\Container($app_settings);

/**
 * Inicialização da Slim Framework
 */
$app = new Slim\App($container);