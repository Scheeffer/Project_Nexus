<?php

    class Connection{
        private $host   = "mysql-ag-br1-17.hospedagemelastica.com.br:33175";
        private $dbname = "mvvrym_sylphina";
        private $user   = "mvvrym_sylphina";
        private $pass   = "p6e6r0o7la";
        
        public function connectDB(){
            try{
                $connection = new PDO(
                    "mysql:host=$this->host;dbname=$this->dbname;charset=utf8",
                    "$this->user",
                    "$this->pass"
                );
                $connection->exec("SET time_zone = '-03:00'");
                return $connection;
            } catch(PDOException $error) {
                echo "<p>".$error->getMessage()."</p>";
            }
        }
    }


    class Database{
        private $connection;
        private $auth;

        public function __construct(Connection $connection){
            $this->connection = $connection->connectDB();
        }
        public function createTable(){
            $query = "CREATE DATABASE if0_41646299_datacom_2026_1;
            
					CREATE TABLE profinet_protocol (
                        id           TINYINT NOT NULL PRIMARY KEY DEFAULT 1,
                        estado       VARCHAR(50),
                        habilitar    BOOLEAN NOT NULL DEFAULT FALSE,
                        resetar      BOOLEAN NOT NULL DEFAULT FALSE,
                        frequencia   INT NOT NULL DEFAULT 0,
                        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,                        
                    );
                    CREATE TABLE CAN_PROTOCOL (
                        id        TINYINT NOT NULL PRIMARY KEY DEFAULT 1,
                        velocity INT NOT NULL DEFAULT 0,
                        gear     INT NOT NULL DEFAULT 0,
                        error    INT NOT NULL DEFAULT 0,
                        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,                        
                    );
                    CREATE TABLE MQTT_PROTOCOL (
                        id          TINYINT NOT NULL PRIMARY KEY DEFAULT 1,
                        temperatura VARCHAR(32),
                        estado      VARCHAR(32),
                        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,                        
                    );                    
                    INSERT INTO can_protocol (id,velocity, gear, error) VALUES (0,0, 0, 0);
            ";
        }

        //----------------------[SET SENSOR DATA]----------------------//
        public function SET_PROFINET_DATA($data){
            //$data["horario"] = date("Y-m-d H-i-s");
            $query = "UPDATE profinet_protocol SET online=:online, estado=:estado, resetar=:resetar, habilitar=:habilitar, frequencia=:frequencia, horario=now() WHERE id = 1;";
            $PDOstatement = $this->connection->prepare($query);
            $PDOstatement->bindValue(":online"       , $data["online"     ], PDO::PARAM_BOOL);
            $PDOstatement->bindValue(":estado"       , $data["estado"     ], PDO::PARAM_BOOL);
            $PDOstatement->bindValue(":resetar"      , $data["resetar"    ], PDO::PARAM_BOOL);
            $PDOstatement->bindValue(":habilitar"    , $data["habilitar"  ], PDO::PARAM_BOOL);
            $PDOstatement->bindValue(":frequencia"   , $data["frequencia" ], PDO::PARAM_INT );
            return $PDOstatement->execute();
        }
        public function SET_CAN_DATA($data){
            //$data["horario"] = date("Y-m-d H-i-s");
            $query = "UPDATE can_protocol SET online=:online, velocidade=:velocidade, marcha=:marcha, horario=now(), erro=:erro WHERE id = 1;";
            $PDOstatement = $this->connection->prepare($query);
            $PDOstatement->bindValue(":online"     , $data["online"     ], PDO::PARAM_BOOL);
            $PDOstatement->bindValue(":velocidade" , $data["velocidade" ], PDO::PARAM_INT);
            $PDOstatement->bindValue(":marcha"     , $data["marcha"     ], PDO::PARAM_INT);
            $PDOstatement->bindValue(":erro"       , $data["erro"       ], PDO::PARAM_INT);
            return $PDOstatement->execute();
        }
        public function SET_MQTT_DATA($data){
            //$data["horario"] = date("Y-m-d H-i-s");
            
            $query = "UPDATE mqtt_protocol SET online=:online, temperatura=:temperatura, estado=:estado, horario=now() WHERE id = 1;";
            $PDOstatement = $this->connection->prepare($query);
            $PDOstatement->bindValue(":online"      , $data["online"     ], PDO::PARAM_BOOL);
            $PDOstatement->bindValue(":temperatura" , $data["temperatura" ], PDO::PARAM_STR);
            $PDOstatement->bindValue(":estado"      , $data["estado"      ], PDO::PARAM_STR);
            return $PDOstatement->execute();
        }

        
        //----------------------[GET SENSOR DATA]----------------------//
         public function GET_PROFINET_DATA(){
            $query = "SELECT * FROM profinet_protocol WHERE id = 1";
            $PDOstatement = $this->connection->prepare($query);
            $PDOstatement->execute();
            return $PDOstatement->fetch(PDO::FETCH_ASSOC);
        }
        public function GET_CAN_DATA(){
             $query = "SELECT * FROM can_protocol WHERE id = 1";

            $PDOstatement = $this->connection->prepare($query);
            $PDOstatement->execute();
            return $PDOstatement->fetch(PDO::FETCH_ASSOC);
        }
        public function GET_MQTT_DATA(){
            $query = "SELECT * FROM mqtt_protocol WHERE id = 1";
            $PDOstatement = $this->connection->prepare($query);
            $PDOstatement->execute();
            return $PDOstatement->fetch(PDO::FETCH_ASSOC);
        }
    }
?>