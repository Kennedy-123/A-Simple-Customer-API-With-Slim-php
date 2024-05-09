<?php

class db {

    public function connect() {
        try {
            $dbPassword = '123456';
            $dbUser = 'kenny';
            $dataBase = new PDO('mysql:host=localhost;dbname=slimapp', $dbUser, $dbPassword);
            $dataBase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dataBase;
        } catch (PDOException $e) {
            echo 'Database connection faild';
            die();
        };
    }
}