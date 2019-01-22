<?php

namespace Pollus\Core;

use Pollus\Mvc\ApplicationInterfaces\ConsoleAppInterface;
use Pollus\Mvc\ApplicationInterfaces\WebAppInterface;
use Slim\App;
use Symfony\Component\Console\Application;
use Psr\Container\ContainerInterface;
use Pollus\Slim\Dispatcher\SlimDispatcher;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Pollus\TwigView\TwigView;
use Pollus\TwigPublicPath\PublicPathComponent;
use Pollus\TwigPublicPath\PublicPathExtension;
use Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware;
use Pollus\Mvc\MvcApplication;
use Pollus\HttpClientFingerprint\HttpClientFingerprint;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class Module extends MvcApplication implements WebAppInterface, ConsoleAppInterface
{
    /**
     * Default Config
     * 
     * @return array
     */
    public function getConfigArray(): array 
    {
        return 
        [
            // Exibição de erros da Slim
            'displayErrorDetails' => true,

            // Configura se o Whoops será exibido
            'debug' => false,

            // Configura se o Whoops será restrito a determinados IPs [false|array]
            'debug_allow_from_ip_only' => ['127.0.0.1'],
            
            'base_url' => "",
            
            // Namespace
            'namespace' => $this->getNamespace(),

            //Namespace de seus controllers
            'controllers_namespace' => $this->getNamespace() .  '\\Controllers',   

            // Locais dos arquivos de template
            'templates' => [ 'core'  => __DIR__ . "/Views"],

            // Caminho para a pasta de templates, utilize "false" para desabilitar
            "templates_cache" => false,

            // Variáveis padrão que serão enviadas para a aplicação
            'default_vars' => [],
            
            // Caminho para o log
            'log_path' => __DIR__ . "/../../logs/" . basename(__DIR__) . "-" . date('Y-m-d') . ".log",
            
            // Nível do log
            'log_level' => Logger::DEBUG
        ];
    }
    
    /**
     * Default error handlers
     * 
     * @param ContainerInterface $container
     */
    public function setupErrorHandler(ContainerInterface $container) 
    {
        // 404
        $container['notFoundHandler'] = function ($container) {
            return function ($request, $response) use ($container) 
            {
                $view = $container["view"]->setResponse($response);
                
                if ($request->isXhr() === true)
                {
                    return $view->renderAsJson(["success" => false, "message" => "Not found"])->withStatus(404);
                }
                return $view->render('@core/Errors/NotFound.twig')->withStatus(404);
            };
        };

        // 405
        $container['notAllowedHandler'] = function ($container) {
            return function ($request, $response, $methods) use ($container) 
            {
                $view = $container["view"]->setResponse($response);
                if ($request->isXhr() === true)
                {
                    return $view->renderAsJson
                        ([
                            "success" => false,
                            "message" => "Method Not Allowed" . implode(', ', $methods),
                            "allowed" => $methods
                        ])->withStatus(405)->withHeader('Allow', implode(', ', $methods));
                }
                else
                {
                    return $view->render('@core/Errors/MethodNotAllowed.twig', ["methods" => implode(", ", $methods)])
                                    ->withStatus(405)
                                    ->withHeader('Allow', implode(', ', $methods));
                }
            };
        };

        // Error Handler
        $ehandler = function ($container) {
            return function ($request, $response, $exception) use ($container) 
            {
                /** @var Logger $logger **/
                $logger = $container["logger"];
                
                /** @var \Exception $exception **/
                $logger->addCritical("Uncaught Exception " . get_class($exception) . ": " 
                           . $exception->getMessage() . " in " . $exception->getFile() 
                           . " line " . $exception->getLine(), $exception->getTrace());
                
                $view = $container["view"]->setResponse($response);
                $show_errors = $container["settings"]["displayErrorDetails"];

                if ($request->isXhr() === true) 
                {
                    $msg = ["success" => false, "message" => "Internal Server Error"];
                    if ($show_errors) $msg = array_merge($msg, ["exception" => $exception]);
                    return $view->renderAsJson($msg)->withStatus(500);
                } 
                else 
                {
                    if ($show_errors) $view["exception"] = $exception;
                    return $view->render('@core/Errors/InternalServerError.twig')->withStatus(500);
                }
            };
        };
        $container["phpErrorHandler"] = $ehandler;
        $container['errorHandler'] = $ehandler;
    }
    
    /**
     * Default routes
     * 
     * @param App $slim
     * @param ContainerInterface $container
     */
    public function setupRoutes(App $slim, ContainerInterface $container) 
    {
        $mvc = new SlimDispatcher($container["settings"]["controllers_namespace"], $container);
        $slim->any('/', function ($request, $response, $args) use ($mvc)
        {
            return $mvc
                ->setController("home")
                ->setMethod("index")
                ->prepare($request, $response, $args)
                ->validateMethodType()
                ->run();
        });

        $slim->any('/{controller}/{method}[/{params:.*}]', function ($request, $response, $args) use ($mvc)
        {
            return $mvc
                ->prepare($request, $response, $args)
                ->validateMethodType()
                ->run();
        });
    }
    
    /**
     * Default extension
     * 
     * @param ContainerInterface $container
     */
    public function setupDependencies(ContainerInterface $container) 
    {
        // URL
        $container["url"] = function($c)
        {
            $settings = $c["settings"];
            $component = new PublicPathComponent($settings["base_url"]);
            return $component;
        };
        
        // View
        $container["view"] = function($c) {
            $config = $c["settings"];
            $loader = new FilesystemLoader($config["templates"]);
            foreach($config["templates"] as $tpl_namespace => $tpl_path)
            {
                $loader->addPath($tpl_path, $tpl_namespace);
            }
            $twig = new Environment($loader, ['cache' => $config["templates_cache"]]);
            
            /** @var PublicPathComponent $component**/
            $component = $c["url"];
            $twig->addExtension(new PublicPathExtension($component));
            return new TwigView($twig, $config["default_vars"]);
        };
        
        // Fingerprint
        $container["fingerprint"] = function($c) {
            return new HttpClientFingerprint();
        };
        
        // Logger
        $container["logger"] = function($c) {
            $logger = new Logger("logger");
            $logger->pushHandler(new StreamHandler($c["settings"]['log_path'], $c["settings"]['log_level']));
            return $logger;
        };
    }
    
    /**
     * Default middleware
     * 
     * @param App $slim
     * @param ContainerInterface $container
     */
    public function setupMiddlewares(App $slim, ContainerInterface $container) 
    {
        // Whoops Middleware
        if ($container["settings"]["debug"] ?? false === true)
        {
            if ($container["settings"]["debug_allow_from_ip_only"] ?? false !== false)
            {
                /** @var HttpClientFingerprint $fingerprint **/
                $fingerprint = $container["fingerprint"];
                try { $ipaddress = $fingerprint->getIpAddress(); } 
                catch (\Exception $ex) { $ipaddress = null; }
                if (in_array($ipaddress, $container["settings"]["debug_allow_from_ip_only"]))
                {
                    $slim->add(new WhoopsMiddleware($slim));   
                }
            }
            else
            {
                $slim->add(new WhoopsMiddleware($slim));
            }
        }
    }
    
    /**
     * Default commands
     * 
     * @param Application $app
     * @param ContainerInterface $container
     */
    public function setupCommands(Application $app, ContainerInterface $container) {}
    
    
    /**
     * Gets the namespace
     * 
     * @return string
     */
    protected abstract function getNamespace() : string;
}
