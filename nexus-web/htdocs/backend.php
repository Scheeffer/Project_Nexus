<?php

    class Connection{
		// Insira os dados do seb website/banco de dados nestas variaveis
        private $host   = "---";
        private $dbname = "---";
        private $user   = "---";
        private $pass   = "---";
        
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
