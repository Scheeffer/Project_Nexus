#include <stdio.h>
#include <stdbool.h>
#include <unistd.h>
#include "nvs_flash.h"
#include "esp_netif.h"
#include "esp_event.h"
#include "wifi.h"
#include "esp_log.h"
#include "mosq_broker.h"

static void broker_message_callback(
    char *client,
    char *topic,
    char *data,
    int len,
    int qos,
    int retain)
{
    ESP_LOGI("BROKER", "Cliente : %s", client);
    ESP_LOGI("BROKER", "Topico  : %s", topic);
    ESP_LOGI("BROKER", "QoS     : %d", qos);
    ESP_LOGI("BROKER", "Retain  : %d", retain);

    ESP_LOGI("BROKER", "Payload : %.*s", len, data);
}

static void broker_task(void *arg)
{
    struct mosq_broker_config config =
    {
        .host = "0.0.0.0",
        .port = 1883,
        .tls_cfg = NULL,
        .handle_message_cb = broker_message_callback,
    };

    ESP_LOGI("BROKER", "Iniciando broker...");

    mosq_broker_run(&config);

    vTaskDelete(NULL);
}

void app_main(void)
{
	wifi_init_sta();

    xTaskCreate(
        broker_task,
        "broker",
        8192,
        NULL,
        5,
        NULL);
        
    while (true) {
        
        sleep(1);
    }
}
