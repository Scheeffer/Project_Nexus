<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<title> Nexus </title>
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
    					<div id="PROFINET_state">Aguardando...</div>
                        <button class="switch" id="refresh_PROFINET" onclick="toggleRefresh(this)"></button>
   						<div id="PROFINET_sound" class="sound_off"></div>
                    </div>
                    <div class="actuator h-50 w-100 d-flex just-c f-col align-c" id="actuator_1">               
                        <div id="PROFINET_DISPLAY" class="Display_data PROFINET_icon d-flex f-row just-a align-c">
                            <span>
                                <div> Estado     </div>
                                <div> Habilitar  </div>
                                <div> Reset      </div>
                                <div> Frequencia </div>
    							<div> Horario    </div>
                            </span>
                            <span>                          
                                <div id="PROFINET_estado"    >---</div>
                                <div id="PROFINET_habilitar" >---</div>
                                <div id="PROFINET_resetar"   >---</div>
                                <div id="PROFINET_frequencia">---</div>
    							<div id="PROFINET_date"      >---</div>
                            </span>
                        </div>
    				</div>
                </div>    
                <div class="operators d-col" id="operator_MQTT">
                    <div class="sensor h-50 w-100 d-flex just-c f-col align-c" id="sensor_2">     
                        Protocolo MQTT
    					<div id="MQTT_state">Aguardando...</div>
                        <button class="switch" id="refresh_MQTT" onclick="toggleRefresh(this)"></button>
    					<div id="MQTT_sound" class="sound_off"></div>
                    </div>
                    <div class="actuator h-50 w-100 d-flex just-c f-col align-c" id="actuator_2">                    
                        <div id="MQTT_DISPLAY" class="Display_data MQQT_icon d-flex f-row just-a align-c">
                            <span>
                                <div> Temperatura </div>
    							<div> Estado      </div>
    							<div> Horario     </div>
                            </span>
                            <span>
                                <div id="MQTT_temperatura">---</div>
    							<div id="MQTT_estado"     >---</div>
    							<div id="MQTT_date"       >---</div>
                            </span>
                        </div>
                    </div>
                </div>  
                <div class="operators d-col" id="operator_CAN">
                    <div class="sensor h-50 w-100 d-flex just-c f-col align-c" id="sensor_3">     
                        Protocolo CAN
    					<div id="CAN_state">Aguardando...</div>
                        <button class="switch" id="refresh_CAN" onclick="toggleRefresh(this)"></button>
    					<div id="CAN_sound" class="sound_off"></div>
                    </div>
                    <div class="actuator h-50 w-100 d-flex just-c f-col align-c" id="actuator_3"> 
                      	<div id="CAN_DISPLAY" class="Display_data d-flex f-row just-a align-c">
                            <span>
                                <div> Velocidade </div>
                                <div> Marcha     </div>
                                <div> Erro       </div>
                                <div> Horario    </div>
                            </span>
                            <span>
                                <div id="CAN_velocity">---</div>
                                <div id="CAN_gear"    >---</div>
                                <div id="CAN_error"   >---</div>
                                <div id="CAN_date"    >---</div>
                            </span>
                        </div>
                    </div>
                </div>  
            </div>
        </div>		
	</body>

    <script>
        function toggleLED(state){
            for(let i = 1; i < 4; i++){
                document.getElementById(`sensor_${i}` ).classList.toggle(state);
            }
        };
		
		GET_DATA(1000);
		var i = 0;
		var GET_DATA_TIMEOUT;
        function GET_DATA(delay = 3000)
        {
            clearTimeout(GET_DATA_TIMEOUT);
            GET_DATA_TIMEOUT = setTimeout(() => {
				fetch("controller.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                        },
                    body: JSON.stringify({
                        action: "get_data"
                        })                        
                 }).then(response => {
                    if (!response.ok) {
                      throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json(); // Returns another promise
                 }).then(data => {
                    if (!data.ok) {
                        throw new Error(`Server error: ${data.status}`);
                    }
                    const PROTOCOL_DATA = data;	//.json();                
                    //console.log(++i, PROTOCOL_DATA);                             
                    displayDATA(PROTOCOL_DATA);

                    toggleLED("sensorDisplay");
                    setTimeout(() => { toggleLED("sensorDisplay"); }, 200);
                    GET_DATA(); 
                    
                }).catch(error => {
                    console.error("Fetch failed:", error);

                    toggleLED("sensorError");
                    setTimeout(() => { toggleLED("sensorError"); }, 200);                
                    
                    GET_DATA();
                });
            }, delay);                        
        }

        function displayDATA(PROTOCOL){
            //---------------------[ PROFINET Protocol   ]---------------------//
            if(PROTOCOL.data.profinet.online){
                document.getElementById("operator_PROFINET").classList.add("PROFINET_ON");   
                toggleFetchSuccess("PROFINET");
                if(sounds.PROFINET.habilitado && !sounds.PROFINET.estado_anterior){
                	SetAnnouncement("PROFINET connected");                    
                }
                sounds.PROFINET.estado_anterior = true;
            } else {
                document.getElementById("operator_PROFINET").classList.remove("PROFINET_ON");
                toggleFetchError("PROFINET");
                if(sounds.PROFINET.habilitado && sounds.PROFINET.estado_anterior){
                	SetAnnouncement("PROFINET disconnected");                    
                }
                sounds.PROFINET.estado_anterior = false;
            }
            document.getElementById("PROFINET_estado"    ).innerHTML = PROTOCOL.data.profinet.estado    ? "Ligado"     : "Desligado"   ;
            document.getElementById("PROFINET_habilitar" ).innerHTML = PROTOCOL.data.profinet.habilitar ? "Habilitado" : "Desabilitado";
            document.getElementById("PROFINET_resetar"   ).innerHTML = PROTOCOL.data.profinet.resetar   ? "Resetar"    : "---"         ;
            document.getElementById("PROFINET_frequencia").innerHTML = `${PROTOCOL.data.profinet.frequencia} KHz`;   
            document.getElementById("PROFINET_date"      ).innerHTML = PROTOCOL.data.profinet.horario.split(" ")[1];
           
            //---------------------[ CAN      Protocol   ]---------------------//       
            if(PROTOCOL.data.can.online){
                document.getElementById("operator_CAN").classList.add("CAN_ON");      
                toggleFetchSuccess("CAN");
                if(sounds.CAN.habilitado && !sounds.CAN.estado_anterior){
                	SetAnnouncement("CAN connected");                    
                }
                sounds.CAN.estado_anterior = true;
            } else {
                document.getElementById("operator_CAN").classList.remove("CAN_OFF");
                toggleFetchError("CAN");
                if(sounds.CAN.habilitado && sounds.CAN.estado_anterior){
                 	SetAnnouncement("CAN disconnected");
                }                       
                sounds.CAN.estado_anterior = false;
            }
            let NDR = ['N','D','R'];
            document.getElementById("CAN_velocity"       ).innerHTML = `${(PROTOCOL.data.can.velocidade*0.1).toFixed(1)} km`;
            document.getElementById("CAN_gear"           ).innerHTML = NDR[PROTOCOL.data.can.marcha];
            document.getElementById("CAN_error"          ).innerHTML = PROTOCOL.data.can.erro;       
            document.getElementById("CAN_date"           ).innerHTML = PROTOCOL.data.can.horario.split(" ")[1];
            
            //---------------------[ MQTT     Protocol   ]---------------------//
            if(PROTOCOL.data.mqtt.online){
                document.getElementById("operator_MQTT").classList.add("MQTT_ON");  
                toggleFetchSuccess("MQTT");
                if(sounds.MQTT.habilitado && !sounds.MQTT.estado_anterior){
                	SetAnnouncement("MQTT connected");                    
                }
                sounds.MQTT.estado_anterior = true;
            } else {
                document.getElementById("operator_MQTT").classList.remove("MQTT_ON");
                toggleFetchError("MQTT");
                if(sounds.MQTT.habilitado && sounds.CAN.estado_anterior){
                 	SetAnnouncement("MQTT disconnected");                    
                }         
                sounds.CAN.estado_anterior = false;
            }
            document.getElementById("MQTT_temperatura"   ).innerHTML = `${PROTOCOL.data.mqtt.temperatura}°C`;;
            document.getElementById("MQTT_estado"        ).innerHTML = PROTOCOL.data.mqtt.estado;
            document.getElementById("MQTT_date"          ).innerHTML = PROTOCOL.data.mqtt.horario.split(" ")[1];        
        }

		function toggleFetchSuccess(PROTOCOL){            
            document.getElementById(`operator_${PROTOCOL}`).classList.add(`${PROTOCOL}_ON`);
            document.getElementById(`operator_${PROTOCOL}`).classList.remove(`${PROTOCOL}_OFF`);
            document.getElementById(`${PROTOCOL}_state`).style.color = "#26C755";
            document.getElementById(`${PROTOCOL}_state`).innerHTML   = "Online" ;
        }
		function toggleFetchError(PROTOCOL){
            document.getElementById(`operator_${PROTOCOL}`).classList.add(`${PROTOCOL}_OFF`);
            document.getElementById(`operator_${PROTOCOL}`).classList.remove(`${PROTOCOL}_ON`);
            document.getElementById(`${PROTOCOL}_state`).style.color = "red";
            document.getElementById(`${PROTOCOL}_state`).innerHTML   = "offline" ;
        }

        function toggleRefresh(button) {
            sounds.switchAudio.play();                     
            const PROTOCOL = button.id.split("_")[1];
            sounds[PROTOCOL].habilitado = !sounds[PROTOCOL].habilitado;
            button.classList.toggle('ON'); 
            document.getElementById(`${PROTOCOL}_sound`).classList.toggle('sound_on');             
                                                                                     
            //let text = sounds[PROTOCOL].habilitado ? `${PROTOCOL} connected` : `${PROTOCOL} disconnected`;
        }
		

        document.addEventListener("visibilitychange", (event)=>{
            if(document.visibilityState == "hidden"){
                clearTimeout(GET_DATA_TIMEOUT);
            } else{
                GET_DATA(1000);
            }
        });

        const sounds = {
            PROFINET: {
                habilitado:      false,
                estado_anterior: false
            },
            CAN: {
                habilitado:      false,
                estado_anterior: false
            },
            MQTT: {
                habilitado:      false,
                estado_anterior: false
            },
            switchAudio : new Audio("imagens/switch.mp3")
        };
		

		var announcement;
        function SetAnnouncement(texto, config = {}) {
            console.log(sounds["PROFINET"].habilitado,sounds["MQTT"].habilitado,sounds["CAN"].habilitado);   
           
            if(speechSynthesis){
                speechSynthesis.cancel();
            }            
            
            announcement = new SpeechSynthesisUtterance(texto);
            announcement.lang  = config.lang  ?? "en-US";
            announcement.rate  = config.rate  ?? 1;
            announcement.pitch = config.pitch ?? 1;
            speechSynthesis.speak(announcement);
        }


    </script>
</html>
        




