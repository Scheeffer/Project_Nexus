#include "DS18B20.h"

#include "esp_log.h"

#define TAG "DS18B20"

/* Tempo máximo para aguardar resposta do sensor (µs) */
#define ONEWIRE_TIMEOUT_US    300

/*==========================================================
 * Inicialização
 *==========================================================*/

void ds18b20_init(void)
{
    gpio_config_t io_conf =
    {
        .pin_bit_mask = (1ULL << DS18B20_PIN),
        .mode = GPIO_MODE_INPUT_OUTPUT_OD,
        .pull_up_en = GPIO_PULLUP_ENABLE,
        .pull_down_en = GPIO_PULLDOWN_DISABLE,
        .intr_type = GPIO_INTR_DISABLE
    };

    gpio_config(&io_conf);

    gpio_set_level(DS18B20_PIN, 1);
}

/*==========================================================
 * Reset + Presence Detect
 *==========================================================*/

bool init_procedure(void)
{
    uint32_t timeout;

    /* Reset */
    gpio_set_direction(DS18B20_PIN, GPIO_MODE_OUTPUT_OD);
    gpio_set_level(DS18B20_PIN, 0);

    esp_rom_delay_us(480);

    /* Libera o barramento */
    gpio_set_level(DS18B20_PIN, 1);

    gpio_set_direction(DS18B20_PIN, GPIO_MODE_INPUT_OUTPUT_OD);

    /* Aguarda 15~60 us antes do pulso de presença */
    esp_rom_delay_us(70);

    /* Espera o sensor colocar a linha em LOW */
    timeout = ONEWIRE_TIMEOUT_US;

    while (gpio_get_level(DS18B20_PIN))
    {
        esp_rom_delay_us(1);

        if (--timeout == 0)
        {
            ESP_LOGE(TAG, "Timeout aguardando Presence Pulse.");
            return false;
        }
    }

    /* Espera terminar o pulso de presença */
    timeout = ONEWIRE_TIMEOUT_US;

    while (!gpio_get_level(DS18B20_PIN))
    {
        esp_rom_delay_us(1);

        if (--timeout == 0)
        {
            ESP_LOGE(TAG, "Presence Pulse muito longo.");
            return false;
        }
    }

    esp_rom_delay_us(5);

    return true;
}

/*==========================================================
 * Escrita de 1 bit
 *==========================================================*/

bool send_bit(bool bit)
{
    gpio_set_direction(DS18B20_PIN, GPIO_MODE_OUTPUT_OD);

    gpio_set_level(DS18B20_PIN, 0);

    if (bit)
    {
        esp_rom_delay_us(6);

        gpio_set_level(DS18B20_PIN, 1);

        esp_rom_delay_us(64);
    }
    else
    {
        esp_rom_delay_us(60);

        gpio_set_level(DS18B20_PIN, 1);

        esp_rom_delay_us(10);
    }

    return true;
}

/*==========================================================
 * Escrita de 1 byte
 *==========================================================*/

bool send_byte(uint8_t byte)
{
    for (uint8_t i = 0; i < 8; i++)
    {
        if (!send_bit(byte & 0x01))
            return false;

        byte >>= 1;
    }

    return true;
}

/*==========================================================
 * Leitura de 1 bit
 *==========================================================*/

bool read_bit(uint8_t *bit)
{
    if (bit == NULL)
        return false;

    gpio_set_direction(DS18B20_PIN, GPIO_MODE_OUTPUT_OD);

    gpio_set_level(DS18B20_PIN, 0);

    esp_rom_delay_us(3);

    gpio_set_level(DS18B20_PIN, 1);

    gpio_set_direction(DS18B20_PIN, GPIO_MODE_INPUT_OUTPUT_OD);

    esp_rom_delay_us(10);

    *bit = gpio_get_level(DS18B20_PIN);

    esp_rom_delay_us(55);

    return true;
}

/*==========================================================
 * Leitura de 1 byte
 *==========================================================*/

bool read_byte(uint8_t *byte)
{
    uint8_t value = 0;
    uint8_t bit;

    if (byte == NULL)
        return false;

    for (uint8_t i = 0; i < 8; i++)
    {
        if (!read_bit(&bit))
            return false;

        value |= (bit << i);
    }

    *byte = value;

    return true;
}
/*==========================================================
 * Configuração da resolução
 *==========================================================*/

ds18b20_status_t set_resolution(uint8_t resolution)
{
    uint8_t cfg;

    if ((resolution < 9) || (resolution > 12))
        return DS18B20_ERROR_ARGUMENT;

    switch (resolution)
    {
        case 9:
            cfg = 0x1F;
            break;

        case 10:
            cfg = 0x3F;
            break;

        case 11:
            cfg = 0x5F;
            break;

        default:
            cfg = 0x7F;
            break;
    }

    if (!init_procedure())
        return DS18B20_ERROR_PRESENCE;

    if (!send_byte(0xCC))
        return DS18B20_ERROR_TIMEOUT;

    if (!send_byte(0x4E))
        return DS18B20_ERROR_TIMEOUT;

    if (!send_byte(0x00))
        return DS18B20_ERROR_TIMEOUT;

    if (!send_byte(0x00))
        return DS18B20_ERROR_TIMEOUT;

    if (!send_byte(cfg))
        return DS18B20_ERROR_TIMEOUT;

    return DS18B20_OK;
}


/*==========================================================
 * Leitura da temperatura
 *==========================================================*/

ds18b20_status_t celsius(uint16_t *temperature)
{
    uint8_t low;
    uint8_t high;

    if (temperature == NULL)
        return DS18B20_ERROR_ARGUMENT;

    if (!init_procedure())
        return DS18B20_ERROR_PRESENCE;

    send_byte(0xCC);
    send_byte(0x44);

    /*
     * Conversão de temperatura.
     * Agora o scheduler pode executar outras tarefas.
     */

    vTaskDelay(pdMS_TO_TICKS(750));

    if (!init_procedure())
        return DS18B20_ERROR_PRESENCE;

    send_byte(0xCC);
    send_byte(0xBE);

    if (!read_byte(&low))
        return DS18B20_ERROR_TIMEOUT;

    if (!read_byte(&high))
        return DS18B20_ERROR_TIMEOUT;

    *temperature = ((uint16_t)high << 8) | low;

    return DS18B20_OK;
}


/*==========================================================
 * Formatação da temperatura
 *==========================================================*/

ds18b20_status_t disp_temperature(char *buffer)
{
    uint16_t raw;

    bool negative = false;

    uint16_t integer;

    if (buffer == NULL)
        return DS18B20_ERROR_ARGUMENT;

    if (celsius(&raw) != DS18B20_OK)
    {
        sprintf(buffer, "ERRO");

        return DS18B20_ERROR_TIMEOUT;
    }

    if (raw & 0x8000)
    {
        raw = (~raw) + 1;
        negative = true;
    }

    integer = raw >> 4;

    sprintf(buffer,
            "%c%03u.%u",
            negative ? '-' : '+',
            integer,
            (raw & 0x08) ? 5 : 0);

    return DS18B20_OK;
}