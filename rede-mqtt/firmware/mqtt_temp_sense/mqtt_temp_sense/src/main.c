#include "freertos/FreeRTOS.h"
#include "freertos/task.h"

#include "wifi.h"
#include "mqtt_app.h"
#include "DS18B20.h"


void app_main(void)
{
    wifi_init_sta();
    ds18b20_init();

     set_resolution(12);
    vTaskDelay(
        pdMS_TO_TICKS(5000));

    mqtt_start();

    while(1)
    {
       vTaskDelay(pdMS_TO_TICKS(10));
    }
}