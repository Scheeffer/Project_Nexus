#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "atuador.h"
#include "wifi.h"
#include "mqtt_app.h"


void app_main() 
{
    gpioInit();/*Configura GPIO */
    wifi_init_sta();
    vTaskDelay(pdMS_TO_TICKS(5000));
    mqtt_start();
  
    while(1)
    {
        vTaskDelay(pdMS_TO_TICKS(10));
    }
}