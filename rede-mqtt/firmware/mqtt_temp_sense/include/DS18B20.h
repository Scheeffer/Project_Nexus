#ifndef DS18B20_H_
#define DS18B20_H_

#include <stdint.h>
#include <stdbool.h>

#include "driver/gpio.h"
#include "esp_rom_sys.h"
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"

/*==========================
 * Configuração de Hardware
 *==========================*/

#define DS18B20_PIN    GPIO_NUM_45

/*==========================
 * Códigos de erro
 *==========================*/

typedef enum
{
    DS18B20_OK = 0,
    DS18B20_ERROR_PRESENCE,
    DS18B20_ERROR_TIMEOUT,
    DS18B20_ERROR_ARGUMENT
} ds18b20_status_t;

/*==========================
 * API Pública
 *==========================*/

/**
 * Inicializa o pino do sensor.
 */
void ds18b20_init(void);

/**
 * Configura a resolução do sensor.
 * Valores permitidos: 9,10,11,12 bits.
 */
ds18b20_status_t set_resolution(uint8_t resolution);

/**
 * Lê a temperatura bruta do sensor.
 */
ds18b20_status_t celsius(uint16_t *temperature);

/**
 * Formata a temperatura em ASCII.
 * Exemplo:
 * +025.0
 * -010.5
 */
ds18b20_status_t disp_temperature(char *buffer);

/*==========================
 * Funções OneWire
 *==========================*/

bool init_procedure(void);

bool send_bit(bool bit);

bool send_byte(uint8_t byte);

bool read_bit(uint8_t *bit);

bool read_byte(uint8_t *byte);

#endif