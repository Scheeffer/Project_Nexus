<?php
    require "backend.php";
    header("Content-Type: application/json");

    try {
        $Connection = new Connection();
        $DataBase   = new Database($Connection);

        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        


        if ($data === null) {
            http_response_code(400);
            echo json_encode([
                "ok" => false,
                "error" => "Invalid JSON"
            ]);
            exit;
        }

        switch($data["action"]){
            case "get_data":
                $_data = [
                    "profinet" => $DataBase->GET_PROFINET_DATA(),
                    "can"      => $DataBase->GET_CAN_DATA(),
                    "mqtt"     => $DataBase->GET_MQTT_DATA()
                ];

                echo json_encode([
                    "ok" => true,
                    "message" => "data retrieved",
                    "data" => $_data
                ]);
                break;
            case "set_data":
                $success = false;
                                
                if($DataBase->SET_PROFINET_DATA($data["profinet"]) && $DataBase->SET_MQTT_DATA($data["mqtt"]) && $DataBase->SET_CAN_DATA($data["can"])){
                    $success = true;
                }                

                echo json_encode([
                    "ok" => true,
                    "message" => $success ? "Data updated" : "Data ERROR"
                ]);
                break;
            default:
                http_response_code(400);
                echo json_encode([
                    "ok" => false,
                    "error" => "Unknown action"
                ]);
                break;
        }

    } catch (Exception $e) {
        http_response_code(500);

        echo json_encode([
            "ok" => false,
            "error" => $e->getMessage()
        ]);
    }
?>
