<?php

class ActiveRecord {

    private $connection;

    public function getConnection($name = 'default') {
        if (!isset($this->connection[$name])) {
            if ($name == 'default') {
                $this->newConnection($name, Config::getConfig('db'));
            } else {
                throw new Exception(sprintf('Undefined connection with name : %s', $name));
            }
        }
        return $this->connection[$name];
    }

    public function newConnection($name, $attributes) {
        try {
            $attributes['options'] = (isset($attributes['options']) && is_array($attributes['options'])) ? $attributes['options'] : null;
            $this->connection[$name] = new PDO($attributes['dsn'], $attributes['user'], $attributes['pass'], $attributes['options']);
            $this->connection[$name]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $this;
    }

}

class Config {

    private static $config;
    private static $directory;

    public static function setDirectory($directory) {
        self::$directory = trim($directory, '/') . '/';
        self::setConfig();
    }

    public static function getDirectory() {
        return self::$directory;
    }

    private static function setConfig() {
        foreach (scandir(self::getDirectory()) as $value) {
            if (preg_match('/^(.*).php$/', $value)) {
                $files[] = require(self::getDirectory() . $value);
            }
        }
        self::$config = $files;
    }

    public static function getConfig($item) {
        for ($i = 0; $i < count(self::$config); $i++) {
            if (isset(self::$config[$i][$item])) {
                return self::$config[$i][$item];
            } else {
                throw new Exception(sprintf('Undefined item with name : %s', $item));
            }
        }
    }

}

class Registry {

    private static $getinstane;
    private $registry;

    public static function getInstance() {
        if (!self::$getinstane instanceof Registry) {
            self::$getinstane = new self;
        }
        return self::$getinstane;
    }

    public function __set($name, $value) {
        if (is_object($value)) {
            if (!isset($this->registry[$name]))
                $this->registry[$name] = $value;
        } else {
            throw new Exception(sprint_r('the class value must be object type , %s given !', gettype($value)));
        }
    }

    public function __get($name) {
        return (object) $this->registry[$name];
    }

}

Config::setDirectory(__DIR__ . '/config');
