#include "mqtt_app.h"

#include "mqtt_client.h"
#include "esp_log.h"
#include "DS18B20.h"
#define TOPICO_PUB "ESP32S3/COM/temperatura"
#define TOPICO_SUB "ESP32S3/COM/get"

static const char *TAG = "MQTT";
char buffer[9];
static esp_mqtt_client_handle_t cliente;
/******************enderço do broker*****************/
esp_mqtt_client_config_t config =
{
   .broker.address.uri = "mqtt://192.168.0.105:1883"
      // "mqtt://broker.hivemq.com:1883"
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

              ESP_LOGI("MQTT", "PUB ID = %d", pub_id);
              ESP_LOGI("MQTT", "Broker URI: %s", config.broker.address.uri);
         break;

        case MQTT_EVENT_SUBSCRIBED:

            ESP_LOGI("MQTT",
                     "Inscrito");

        break;

        case MQTT_EVENT_DATA:

          char comando[32];

             ESP_LOGI("MQTT",
                 "TOPIC=%.*s",
                   event->topic_len,
                   event->topic);

            ESP_LOGI("MQTT",
              "DATA=%.*s",
              event->data_len,
              event->data);

            memcpy(comando,
               event->data,
                 event->data_len);

             comando[event->data_len] = '\0';


             /* Remove CR e LF */
               for(int i = 0; comando[i] != '\0'; i++)
                {
                   if(comando[i] == '\r' || comando[i] == '\n')
                    {
                        comando[i] = '\0';
                      
                    }
                }

             ESP_LOGI("MQTT",
             "COMANDO='%s'",
             comando);

            if(strcmp(comando, "GET_TEMP") == 0)
               {
                     ESP_LOGI("MQTT",
                       "Enviando temperatura");

                     mqtt_publish_temp();
               }

      break;

        case MQTT_EVENT_DISCONNECTED:

            ESP_LOGI("MQTT",
                     "Desconectado");

        break;
        case MQTT_EVENT_PUBLISHED:

            ESP_LOGI("MQTT",
             "Mensagem publicada ID=%d",
             event->msg_id);

        break;
    }
}

void mqtt_start(void)
{
/*esp_mqtt_client_config_t config =
{
   .broker.address.uri = "mqtt://192.168.15.8:1883"
      // "mqtt://broker.hivemq.com:1883"
};*/

    cliente =
        esp_mqtt_client_init(&config);

    esp_mqtt_client_register_event(
            cliente,
            ESP_EVENT_ANY_ID,
            mqtt_event_handler,
            NULL);

    esp_mqtt_client_start(cliente);
}
void mqtt_publish_temp(void)
{
  char buffer[16];

    if (disp_temperature(buffer) == DS18B20_OK)
    {
      esp_mqtt_client_publish(cliente,
                            TOPICO_PUB,
                            buffer,
                            0,
                            1,
                            0);
    }
}