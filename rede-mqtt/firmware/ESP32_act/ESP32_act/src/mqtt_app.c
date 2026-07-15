#include "mqtt_app.h"

#include "mqtt_client.h"

#include "atuador.h"

#include "esp_log.h"

#define TOPICO_PUB "ESP32/COM/Status"
#define TOPICO_SUB "ESP32/COM/Atuador"

estado_sistema_t estado_atual;
acionamento_sistema_t atuador_atual;

static const char *TAG = "MQTT";
char buffer[13] = "STATUS_ATUAL";
static esp_mqtt_client_handle_t cliente;
/******************enderço do broker*****************/
esp_mqtt_client_config_t config =
{
   .broker.address.uri =
       "mqtt://192.168.0.105:1883"
};
/*************************************************/
/*
    Callback de eventos MQTT
*/
static void mqtt_event_handler(
        void *handler_args,
        esp_event_base_t base,
        int32_t event_id,
        void *event_data)
{
    esp_mqtt_event_handle_t event =
            (esp_mqtt_event_handle_t)event_data;

    switch(event_id)
    {
        case MQTT_EVENT_CONNECTED:
     
            ESP_LOGI("MQTT", "Broker conectado");

             int sub_id = esp_mqtt_client_subscribe(
            cliente,
            TOPICO_SUB,
            1);

            ESP_LOGI("MQTT", "SUB ID = %d", sub_id);

            int pub_id = esp_mqtt_client_publish(
            cliente,
            TOPICO_PUB,
            "ESP32 online",
            0,
            1,
            0);
              mqtt_publish_status();
              ESP_LOGI("MQTT", "PUB ID = %d", pub_id);
              ESP_LOGI("MQTT", "Broker URI: %s", config.broker.address.uri);
         break;

        case MQTT_EVENT_SUBSCRIBED:

            ESP_LOGI("MQTT","Inscrito");

        break;

        case MQTT_EVENT_DATA:

        char comando[32];

        /* Copia o payload recebido */
         memcpy(comando, event->data, event->data_len);

         /* Adiciona terminador de string */
           comando[event->data_len] = '\0';
             /* Remove CR e LF */
               for(int i = 0; comando[i] != '\0'; i++)
                {
                   if(comando[i] == '\r' || comando[i] == '\n')
                    {
                        comando[i] = '\0';
                      
                    }
                }

         printf("Comando recebido: %s\n", comando);
                 ESP_LOGI("MQTT",
                   "COMANDO='%s'",
                      comando);
    if(strcmp(comando, "SYSTEM_OFF") == 0)
    {
        estado_atual = SISTEMA_DESLIGADO;
        atualiza_saidas(DESLIGADO);
         mqtt_publish_status();
    }
    else if(strcmp(comando, "AQUECIMENTO_ON") == 0)
    {
        estado_atual = SISTEMA_AQUECENDO;
        atualiza_saidas(AQUECENDO);
         mqtt_publish_status();
    }
    else if(strcmp(comando, "REFRIGERACAO_ON") == 0)
    {
        estado_atual = SISTEMA_REFRIGERANDO;
        atualiza_saidas(REFRIGERANDO);
        mqtt_publish_status();
    }
    else
    {
        printf("Comando desconhecido\n");
    }

    break;
      

        case MQTT_EVENT_DISCONNECTED:

            ESP_LOGI("MQTT","Desconectado");
            /**abre os reles */
            atualiza_saidas(DESLIGADO);
            /*Tenta iniciar uma nova conexção com o brocker*/
            mqtt_start();
        break;
    }
}

void mqtt_start(void)
{

    cliente =
        esp_mqtt_client_init(&config);

    esp_mqtt_client_register_event(
            cliente,
            ESP_EVENT_ANY_ID,
            mqtt_event_handler,
            NULL);

    esp_mqtt_client_start(cliente);
}
void mqtt_publish_status(void)
{
    if(estado_atual == SISTEMA_DESLIGADO)
    {esp_mqtt_client_publish(cliente,TOPICO_PUB,"Sistema desligado",0,1,0);}
    else if (estado_atual == SISTEMA_AQUECENDO)
    {esp_mqtt_client_publish(cliente,TOPICO_PUB,"Sistema aquecendo",0,1,0);}
    else if (estado_atual == SISTEMA_REFRIGERANDO)
    {esp_mqtt_client_publish(cliente,TOPICO_PUB,"Sistema resfriando",0,1,0);}
    else{
      esp_mqtt_client_publish(cliente,TOPICO_PUB,"Aguardando comando",0,1,0);  
    }
    
}