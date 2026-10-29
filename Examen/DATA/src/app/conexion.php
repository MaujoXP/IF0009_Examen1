<?php
use Psr\Container\ContainerInterface;
$container->set('base_datos', function(ContainerInterface $c){

    $config = $c->get('config_db');

    $opc = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_OBJ
    ];

        $dsn="mysql:host={$config->host};dbname={$config->database};charset={$config->charset}";

        try{
            $conexion = new PDO($dsn,$config->username, $config->password, $opc);
        } catch(PDOException $e){
            die("Error de conexion: " . $e->getMessage());
        }

        return $conexion;

});