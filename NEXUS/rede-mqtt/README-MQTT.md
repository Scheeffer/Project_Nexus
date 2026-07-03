# 🟧 Rede MQTT — Célula 3 (Lucas & Henzo)

[![Protocolo](https://img.shields.io/badge/protocolo-MQTT-orange.svg)](https://mqtt.org/)
[![MCU](https://img.shields.io/badge/MCU-ESP32%20DevKit-blueviolet.svg)](#)
[![Framework](https://img.shields.io/badge/framework-ESP--IDF%20(PlatformIO)-yellow.svg)](https://platformio.org/)

Célula de produção que usa **MQTT** como protocolo. Um **ESP32** (ESP-IDF) atua como **cliente MQTT**: publica o estado do processo e recebe comandos. O segundo **ESP32** é responsávelpor controlar o **sensor térmico** — o atuador tem um canal de **aquecimento** e um de **refrigeração**.

---

## 1. Descrição do projeto

O ESP32 lê a temperatura do processo, decide localmente o estado (desligado / aquecendo / refrigerando) e aciona as saídas correspondentes. Em paralelo, publica o estado via MQTT e pode receber comandos/override do backbone.

| Item | Descrição |
|------|-------|
| Controlador | **ESP32** (`esp32doit-devkit-v1`) |
| Framework | **ESP-IDF** (`espressif32@6.5.0`, IDF 5.1.x) via PlatformIO |
| Cliente MQTT | `esp-mqtt` (nativo do ESP-IDF) |
| Atuador | Aquecimento (**GPIO18**) + Refrigeração (**GPIO19**) |
| Sensor | Temperatura *DS18B20* |

### Estados do sistema (do firmware)

| Estado (`estado_sistema_t` / `acionamento_sistema_t`) | GPIO18 (aquec.) | GPIO19 (refrig.) |
|---|:---:|:---:|
| `DESLIGADO` | 0 | 0 |
| `AQUECENDO` | 1 | 0 |
| `REFRIGERANDO` | 0 | 1 |

> ⚙️ **Autonomia:** a decisão `aquecer / refrigerar / desligar` deve rodar **no ESP32**, não no Node-RED. O backbone serve para monitorar e sobreescrever. 

---

## 2. Diagrama de blocos da célula

```mermaid
flowchart LR
    SENS["🌡️ Sensor DS18B20<br/>(1-Wire, pull-up 4k7)"] -- "temperatura T (°C)" --> ESP

    subgraph LOCAL["Controlador local — ESP32 (autonomia)"]
        direction TB
        ESP["ESP32 (ESP-IDF)<br/>cliente esp-mqtt<br/>lógica bang-bang<br/>com histerese"]
    end

    ESP -- "GPIO18 = 1 → aquecer" --> AQ["🔥 Aquecimento<br/>(relé/SSR)"]
    ESP -- "GPIO19 = 1 → refrigerar" --> RF["❄️ Refrigeração<br/>(ventilador/peltier)"]

    ESP == "pub: d3/backbone/sensor/status (QoS0)<br/>pub: d3/backbone/diag/online (LWT, retained)" ==> BR
    BR == "sub: d3/backbone/atuador/cmd (QoS1) — override" ==> ESP

    BR["🧠 Broker MQTT<br/>(backbone, TCP :1883)<br/>hospedado no PC"]

    classDef local fill:#e8f5e9,stroke:#2e7d32,stroke-width:1px;
    classDef central fill:#fff3e0,stroke:#e65100,stroke-width:1px;
    class ESP local;
    class BR central;
```

### Legenda do diagrama de blocos

| Elemento | Significado |
|----------|-------------|
| 🟩 `ESP32` (verde) | Onde roda a **malha de controle autônoma** (lê `T`, decide o estado, aciona GPIOs). |
| 🟧 `Broker` (laranja) | Nível central, no PC. Só **recebe telemetria** e **envia override** — não fecha a malha. |
| `==>` (linha grossa) | Enlace **MQTT sobre TCP/IP** através do backbone Ethernet/Wi-Fi. |
| `-->` (linha fina) | Sinal **físico local** dentro da bancada (1-Wire do sensor, GPIO dos atuadores). |
| `pub` / `sub` | Papel MQTT do ESP32 no tópico: **pub**lica telemetria/diagnóstico, **sub**screve comando. |

> Direção do fluxo: **sensor → ESP32 → atuador** é a malha local (fechada no próprio ESP, funciona sem rede). **ESP32 ⇄ broker** é só espelhamento/override para o backbone.

---

## 3. Máquina de estados

Controlador **bang-bang com histerese** (liga-desliga). Símbolos:

| Símbolo | Significado |
|:-------:|-------------|
| `T` | Temperatura medida pelo DS18B20 (°C) |
| `SP` | *Setpoint* — temperatura desejada |
| `h` | *Histerese* — meia-largura da banda morta em torno do `SP` |
| Banda morta | Faixa `[SP − h, SP + h]`: dentro dela nada é acionado (evita chaveamento contínuo) |

```mermaid
stateDiagram-v2
    direction LR
    [*] --> DESLIGADO

    DESLIGADO --> AQUECENDO: T < SP − h
    AQUECENDO --> DESLIGADO: T ≥ SP

    DESLIGADO --> REFRIGERANDO: T > SP + h
    REFRIGERANDO --> DESLIGADO: T ≤ SP

    DESLIGADO --> OVERRIDE: cmd do backbone recebido
    AQUECENDO --> OVERRIDE: cmd do backbone recebido
    REFRIGERANDO --> OVERRIDE: cmd do backbone recebido
    OVERRIDE --> DESLIGADO: fim do override / retomada local

    note right of DESLIGADO
        GPIO18=0, GPIO19=0
        publica d3/backbone/sensor/status
    end note
    note right of AQUECENDO
        GPIO18=1, GPIO19=0
    end note
    note right of REFRIGERANDO
        GPIO18=0, GPIO19=1
    end note
    note left of OVERRIDE
        estado forçado pelo
        dashboard (Node-RED)
        via d3/backbone/atuador/cmd
    end note
```

### Descrição dos estados e das transições

| Estado | GPIO18 (aquec.) | GPIO19 (refrig.) | Descrição |
|--------|:---:|:---:|-----------|
| `DESLIGADO` | 0 | 0 | Repouso; `T` está dentro da banda morta. Estado inicial e de descanso. |
| `AQUECENDO` | 1 | 0 | `T` caiu abaixo do limite inferior; aquece até atingir o `SP`. |
| `REFRIGERANDO` | 0 | 1 | `T` subiu acima do limite superior; refrigera até atingir o `SP`. |
| `OVERRIDE` | forçado | forçado | Backbone assumiu o comando manualmente; ignora a lógica local até liberar. |

> ⚙️ **Por que os limiares são assimétricos (liga na borda, desliga no centro)?**
> A transição de **ligar** usa a **borda da banda** (`SP − h` / `SP + h`), mas a de **desligar** usa o **centro** (`SP`). Essa diferença de `h` entre ligar e desligar **é** a histerese: garante que o atuador só desliga depois de trazer `T` de volta ao `SP`, e só religa quando `T` sair de novo por `h`. Sem isso, o relé ficaria chaveando ("chattering") toda vez que `T` oscilasse em torno de um único limiar — desgaste mecânico e ruído elétrico.
>
> 📌 **Nota sobre transições diretas AQUECENDO ↔ REFRIGERANDO:** foram **removidas** de propósito. Um processo térmico não cruza a banda morta inteira em um único ciclo de controle; ele sempre passa pelo `SP` (→ `DESLIGADO`) antes de inverter. Se um transitório brusco ocorrer, o próximo ciclo já reavalia e corrige. Manter o passo por `DESLIGADO` deixa a máquina mais legível e fiel à física do sistema.

---

## 4. Fluxograma da lógica de controle

```mermaid
flowchart TD
    A([Ciclo]) --> B[Ler temperatura]
    B --> C{"Override do backbone?"}
    C -- Sim --> D[Aplicar comando recebido] --> P
    C -- Não --> E{"temp abaixo de SP - h?"}
    E -- Sim --> F[AQUECENDO: GPIO18=1, GPIO19=0]
    E -- Não --> G{"temp acima de SP + h?"}
    G -- Sim --> H[REFRIGERANDO: GPIO18=0, GPIO19=1]
    G -- Não --> I[DESLIGADO: 0,0]
    F --> P[atualiza_saidas estado]
    H --> P
    I --> P
    P --> Q[mqtt_publish_status]
    Q --> R([aguarda período]) --> A
```

---

## 5. Diagrama elétrico / ligações

| Saída | GPIO | Função | Observação |
|-------|:----:|--------|------------|
| Aquecimento | **GPIO18** | Liga elemento de aquecimento (via relé) | Relé/SSR; carga em fonte separada |
| Refrigeração | **GPIO19** | Liga ventilador/peltier (via relé) | GND comum com o ESP |
| Sensor temp | DS18B20 | Entrada do sensor | Se DS18B20: pull-up 4k7 no 1-Wire |

> Esquemático formal → exportar de Fritzing/KiCad para `figs/`.

---

## 6. Firmware

```text
firmware/esp32-idf/
├── platformio.ini                 ← env esp32doit-devkit-v1, framework = espidf
├── CMakeLists.txt                 ← projeto ESP32_act
├── sdkconfig.esp32doit-devkit-v1
├── .gitignore                     ← ignora .pio, build/ e SEGREDOS
├── .vscode/                       ← extensions.json, settings.json
└── main/
    ├── wifi.h                     ← SANITIZADO (inclui wifi_secrets.h)
    ├── wifi_secrets.example.h     ← template (copie p/ wifi_secrets.h)
    ├── mqtt_app.h                 ← estados + mqtt_start()/mqtt_publish_status()
    └── atuador.h                  ← GPIO18/19 + atualiza_saidas()
```

> 🔐 **Antes do primeiro build:** `cp main/wifi_secrets.example.h main/wifi_secrets.h` e preencha SSID/senha. Esse arquivo está no `.gitignore`.

### Versões

| Software | Versão |
|----------|--------|
| PlatformIO platform `espressif32` | **6.5.0** (ESP-IDF 5.1.x) |
| Framework | ESP-IDF (`espidf`) |
| Board | `esp32doit-devkit-v1` |

---

## 7. Pendências (o que falta versionar, adicione pendências aqui.)

- [ ] Endereço do **broker** e **tópicos** usados no `mqtt_app.c`

---

## 8. Conteúdo desta pasta

```text
rede-mqtt/
├── README.md
├── diagramas/    ← exports dos diagramas
├── componentes/  ← lista de componentes, datasheets
├── figs/         ← fotos da bancada
└── firmware/esp32-idf/  ← projeto ESP-IDF
```
