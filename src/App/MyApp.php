<?php declare(strict_types=1);

namespace App;

use Monolog\Logger;
use Symfony\Component\Console\Application;
use Psr\Container\ContainerInterface;
use App\Commands\GreetCommand;

class MyApp extends \Pollus\Core\Module
{
    public function getConfigArray(): array 
    {
        return array_replace_recursive(parent::getConfigArray(), 
        [            
            // Debug
            'debug' => true,
            
            // Whoops IP Address
            'debug_allow_from_ip_only' => ['127.0.0.1'],
            
            // Template path
            'templates' =>
            [
                'app' =>  __DIR__ . "/Views"
            ],
            
            // View 
            'default_vars' => 
            [
                'app_title' => "My App"
            ],
            
            // Log path
            'log_path' => __DIR__ . "/../../logs/" . basename(__DIR__) . "-" . date('Y-m-d') . ".log",
            'log_level' => Logger::DEBUG
        ]);
    }
    
    public function setupCommands(Application $app, ContainerInterface $container) 
    {
        parent::setupCommands($app, $container);
        
        // Example command
        $app->add(new GreetCommand());
    }

    protected function getNamespace(): string 
    {
        return "App";
    }

}