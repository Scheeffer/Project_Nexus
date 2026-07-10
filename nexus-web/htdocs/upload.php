<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<title> Upload </title>
		<meta charset="utf-8">
		<link id="tabIcon" rel="icon" type="image/ico" href="imagens/IFSC.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Personalized style CSS -->
   	 	<link rel="stylesheet" type="text/css" href="estilo.css">

   	 	<!-- Script js -->
   	 	<!-- <script src="my_script.js" defer></script> -->
	</head>
	<body>
        <header class="d-flex f-row align-c">
            <div class="icon h-100"></div>
            Projeto Nexus
        </header>
        <div class="canvas d-flex f-col">
            <div class="controller d-flex align-c h-100 .md\:f-dir">
    
                <div class="operators d-col" id="operator_PROFINET"> 
                    <div class="sensor h-50 w-100 d-flex just-c f-col align-c" id="sensor_1">     
                        Protocolo PROFINET
    					<div id="PROFINET_state"></div>
                        <!-- <button class="switch" id="refresh_PROFINET" onclick="toggleRefresh(this)"></button> -->
                    </div>
                    <div class="actuator h-50 w-100 d-flex just-c f-col align-c" id="actuator_1"> 
                      <div id="PROFINET_DISPLAY" class="Display_data d-flex f-row just-a align-c">
                            <span>
                                <div> Estado     </div>
                                <div> Habilitar  </div>
                                <div> Reset      </div>
                                <div> Frequencia </div>
                            </span>
                            <span>                          
                                <div id="PROFINET_estado"    >---</div>
                                <div id="PROFINET_habilitar" >---</div>
                                <div id="PROFINET_reset"     >---</div>
                                <div id="PROFINET_frequencia">---</div>
                            </span>
                        </div>
                    </div>
                </div>    
                <div class="operators d-col" id="operator_MQTT">
                    <div class="sensor h-50 w-100 d-flex just-c f-col align-c" id="sensor_2">     
                        Protocolo MQTT
                        <div id="MQTT_state"></div>
                        <!-- <button class="switch" id="refresh_MQTT" onclick="toggleRefresh(this)"></button> -->
                    </div>
                    <div class="actuator h-50 w-100 d-flex just-c f-col align-c" id="actuator_2"> 
                       <div id="MQTT_DISPLAY" class="Display_data d-flex f-row just-a align-c">
                            <span>
                                <div> Temperatura </div>
    							<div> Estado      </div>
                            </span>
                            <span>
                                <div id="MQTT_temperatura">---</div>
    							<div id="MQTT_estado"     >---</div>
                            </span>
                        </div>
                    </div>
                </div>  
                <div class="operators d-col" id="operator_CAN">
                    <div class="sensor h-50 w-100 d-flex just-c f-col align-c" id="sensor_3">     
                        Protocolo CAN
                        <div id="CAN_state"></div>
                        <!-- <button class="switch" id="refresh_CAN" onclick="toggleRefresh(this)"></button> -->
                    </div>
                    <div class="actuator h-50 w-100 d-flex just-c f-col align-c" id="actuator_3"> 
                        <div id="CAN_DISPLAY" class="Display_data d-flex f-row just-a align-c">
                            <span>
                                <div> speed </div>
                                <div> Gear  </div>
                                <div> Error </div>
                            </span>
                            <span>
                                <div id="CAN_velocity">---</div>
                                <div id="CAN_gear"    >---</div>
                                <div id="CAN_error"   >---</div>
                            </span>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
		
	</body>

    <script>
		const PROTOCOL_TAG = ["PROFINET","MQTT","CAN"];
        function toggleLED(state){
            for(let i = 1; i < 4; i++){
                document.getElementById(`sensor_${i}` ).classList.toggle(state);
            }
        };
		function toggleFetchError(PROTOCOL){
            document.getElementById(`operator_${PROTOCOL}`).classList.remove('sensorON');
            document.getElementById(`${PROTOCOL}_state`).style.color = "red";
            document.getElementById(`${PROTOCOL}_state`).innerHTML   = "offline" ;
        }
		function toggleFetchSuccess(PROTOCOL, data){            
            if(data[PROTOCOL].online == false){ 
                toggleFetchError(PROTOCOL);
                return ;
            }
            document.getElementById(`operator_${PROTOCOL}`).classList.add('sensorON');
            document.getElementById(`${PROTOCOL}_state`).style.color = "#26C755";
            document.getElementById(`${PROTOCOL}_state`).innerHTML   = "online" ;
        }
        
		var i = 0;
        FETCH_DATA();
        async function FETCH_DATA(){
            const NODE_IP = "127.0.0.1";
            const NODE_URL = `http://${NODE_IP}:1880/api/state`;   
                     
            try{                
                const response = await fetch(NODE_URL);                               
                const data = await response.json();                
                console.log(++i, data);        
                
                displayDATA(data);
                SET_DATA(data);

                toggleLED("sensorUpdate");
                setTimeout(() => { toggleLED("sensorUpdate"); }, 200);  
                if(data.PROFINET.online == true) {toggleFetchSuccess("PROFINET", data)} else {toggleFetchError("PROFINET")}             
                if(data.MQTT.online     == true) {toggleFetchSuccess("MQTT"    , data)} else {toggleFetchError("MQTT"    )}  
                if(data.CAN.online      == true) {toggleFetchSuccess("CAN"     , data)} else {toggleFetchError("CAN"     )}  
            
                
            } catch(error) { 
                console.error("Fetch failed:", error.message);            

                toggleLED("sensorError");
                setTimeout(() => { toggleLED("sensorError"); }, 200);
                toggleFetchError("PROFINET");
                toggleFetchError("MQTT"    );
                toggleFetchError("CAN"     );   
            }
            setTimeout(() => {FETCH_DATA()}, 3000);
        }

        function displayDATA(DATA){                
			//---------------------[ PROFINET Protocol   ]---------------------//
            if(DATA.PROFINET.online == true){
                document.getElementById("PROFINET_estado"    ).innerHTML = DATA.PROFINET.estado    ? "Ligado"     : "Desligado"   ;
                document.getElementById("PROFINET_habilitar" ).innerHTML = DATA.PROFINET.habilitar ? "Habilitado" : "Desabilitado";
                document.getElementById("PROFINET_reset"     ).innerHTML = DATA.PROFINET.resetar   ? "Resetar"    : "---"         ;
                document.getElementById("PROFINET_frequencia").innerHTML = `${DATA.PROFINET.frequencia} KHz`;   
            }          
            //---------------------[ MQTT     Protocol   ]---------------------//
            if(DATA.MQTT.online == true){
                document.getElementById("MQTT_temperatura"   ).innerHTML = DATA.MQTT.estado;
                document.getElementById("MQTT_estado"        ).innerHTML = DATA.MQTT.temperatura;
            }
            if(DATA.CAN.online == true){
            //---------------------[ CAN      Protocol   ]---------------------//   
                let NDR = ['N','D','R'];
                document.getElementById("CAN_velocity"       ).innerHTML = `${(DATA.CAN.velocidade*0.1).toFixed(1)} km`;
                document.getElementById("CAN_gear"           ).innerHTML = NDR[DATA.CAN.marcha];
                document.getElementById("CAN_error"          ).innerHTML = DATA.CAN.erro;         
            }
        }

        async function SET_DATA(data) {
            try {
                const response = await fetch("controller.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        action: "set_data",
                        profinet: {
                            online:     data.PROFINET.online,
                            estado:     data.PROFINET.estado,
                            habilitar:  data.PROFINET.habilitar,
                            resetar:    data.PROFINET.resetar,
                            frequencia: data.PROFINET.frequencia
                        },
                        mqtt: {
                            online:      data.MQTT.online,
                            temperatura: data.MQTT.temperatura,
                            estado:      data.MQTT.estado
                        },
                        can: {
                            online:     data.CAN.online,
                            velocidade: data.CAN.velocidade,
                            marcha:     data.CAN.marcha,
                            erro:       data.CAN.erro
                        }
                    })
                });                   
                     
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                const data2 = await response.json(); // Or response.text() if backend doesn't return JSON
                
            } catch (error) {
                console.error("Fetch failed:", error);
            }
        }
    </script>
</html>




