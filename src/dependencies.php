<?php

/**
 * Defina as dependências de sua aplicação neste arquivo
 * 
 * Dependências que podem estar indisponíveis ou gerar erros, devem ser adicionadas
 * em forma de middlewares, para que o tratamento de erros possa ser ativado
 * caso uma dependência esteja indisponível.
 * 
 * @link https://www.slimframework.com/docs/v3/concepts/di.html Dependency Container
 */

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Pollus\Mvc\Views\TwigExtensions;
use Pollus\Mvc\Views\TwigView;

/**
 * VIEW
 * 
 * A view é uma dependência do sistema. O Twig é a implementação padrão, no 
 * entanto você pode utilizar qualquer motor de templates, desde que implemente 
 * a interface {@see Pollus\Mvc\Views\ViewInterface}
 */
$twig = new Environment
(
    new FilesystemLoader($config["templates"]),
    [
        'cache' => $config["templates_cache"]
    ]
);

$twig->addExtension(new TwigExtensions());

$container["view"] = new TwigView($twig, $config["default_vars"]);