# 🟧 Rede MQTT — Célula 3 (Lucas & Henzo)

[![Protocolo](https://img.shields.io/badge/protocolo-MQTT%20v3.1.1-orange.svg)](https://mqtt.org/)
[![Broker](https://img.shields.io/badge/broker-Mosquitto%20embarcado%20(ESP32)-red.svg)](https://components.espressif.com/components/espressif/mosquitto)
[![MCU](https://img.shields.io/badge/MCU-2%C3%97%20ESP32%20%2B%201%C3%97%20ESP32--S3-blueviolet.svg)](#)
[![Framework](https://img.shields.io/badge/framework-ESP--IDF-yellow.svg)](https://idf.espressif.com/)

> ⚠️ **Correção de arquitetura (09/07/2026):** versões anteriores deste README descreviam um único ESP32 cliente com sensor DS18B20 e controle local por histerese, com broker "no PC". **Essa descrição não corresponde ao firmware real** enviado em `firmware/`. Este documento foi reescrito a partir do código-fonte extraído dos projetos. As diferenças estão marcadas ao longo do texto; o que era planejado e ainda não existe está na seção **Pendências**.

---

## 1. Arquitetura real da célula

A célula MQTT é composta por **três ESP32 com papéis distintos**. A "rede local" da célula **é o próprio MQTT**: os nós não se falam diretamente — tudo passa pelo broker.

| Nó | Firmware | Papel | IP |
|----|----------|-------|-----|
| 🧠 **Broker** | `firmware/embedded_brocker.rar` | ESP32 rodando **Mosquitto embarcado** (componente `espressif/mosquitto` ^2.0.20), escutando `0.0.0.0:1883`. É o **servidor MQTT de todo o sistema NEXUS** — o Node-RED se conecta aqui como cliente. | `192.168.0.105` |
| 🌡️ **Sensor** | ESP32-S3 *(firmware ainda não versionado — ver Pendências)* | Lê a temperatura e publica em `ESP32S3/COM/temperatura`; responde a solicitações em `ESP32S3/COM/get`. | — |
| 🔥❄️ **Atuador** | `firmware/ESP32_act.rar` | Cliente `esp-mqtt` (ESP-IDF via PlatformIO, `esp32doit-devkit-v1`). Assina o tópico de comando e aciona **GPIO18 (aquecimento)** / **GPIO19 (refrigeração)**. | — |

Todos os nós conectam-se à rede Wi-Fi do projeto **`COM_N_26.1`** (aberta, sem senha — decisão documentada no [`backbone/README.md`](../backbone/README.md)).

```mermaid
flowchart LR
    S3["🌡️ ESP32-S3<br/>sensor de temperatura<br/>(cliente MQTT)"]
    BR["🧠 ESP32 broker<br/>Mosquitto embarcado<br/>192.168.0.105:1883"]
    ACT["🔥❄️ ESP32 atuador<br/>(cliente esp-mqtt)<br/>GPIO18 aquece · GPIO19 refrigera"]
    NR["📊 Node-RED (PC, 192.168.0.100)<br/>dashboard + Tabela Global<br/>(cliente MQTT)"]

    S3 -. "pub ESP32S3/COM/temperatura<br/>sub ESP32S3/COM/get" .-> BR
    ACT -. "sub ESP32/COM/Atuador<br/>pub ESP32/COM/Status" .-> BR
    NR -. "pub comandos · sub telemetria" .-> BR

    classDef broker fill:#ffe0b2,stroke:#e65100,stroke-width:2px;
    classDef cliente fill:#e8f5e9,stroke:#2e7d32,stroke-width:1px;
    classDef central fill:#fff3e0,stroke:#e65100,stroke-width:1px;
    class BR broker;
    class S3,ACT cliente;
    class NR central;
```

**Leitura do diagrama:** todas as linhas são **MQTT sobre TCP/IP via Wi-Fi** (pontilhado = meio não guiado). Não há ligação física direta sensor→atuador: o acoplamento entre eles acontece **por tópicos**, mediado pelo broker. Isso é a topologia estrela característica do MQTT — e o motivo pelo qual a célula continua trocando dados internamente mesmo se o PC/Node-RED sair do ar (o broker não está no PC).

---

## 2. Tabela de tópicos (contrato da célula)

| Tópico | Direção (visão do broker) | Payload (string) | Publicador | Assinantes |
|--------|--------------------------|-------------------|------------|-----------|
| `ESP32S3/COM/temperatura` | entrada de telemetria | valor numérico em texto (ex.: `23.75`) | ESP32-S3 sensor | Node-RED |
| `ESP32S3/COM/get` | comando ao sensor | `GET_TEMP` | Node-RED | ESP32-S3 sensor |
| `ESP32/COM/Atuador` | comando ao atuador | `AQUECIMENTO_ON` · `REFRIGERACAO_ON` · `SYSTEM_OFF` | Node-RED | ESP32 atuador |
| `ESP32/COM/Status` | estado do atuador | `Sistema aquecendo` · `Sistema resfriando` · `Sistema desligado` · `ESP32 online` (no connect) | ESP32 atuador | Node-RED |

> ⚠️ **Bug de integração conhecido — divergência de nome de tópico:** a documentação do backbone e o flow `trabalho_COM.json` originais usavam **`ESP32S3/COM/Atuadores`** (plural, prefixo `S3`) para o comando, mas o firmware real do atuador assina **`ESP32/COM/Atuador`** (singular, sem `S3`). Com esse descasamento, **o comando do dashboard nunca chega ao atuador**. O flow corrigido em [`../backbone/node_mqtt/`](../backbone/node_mqtt/) já usa o tópico do firmware (`ESP32/COM/Atuador`); ao editar qualquer um dos lados, mantenha os dois idênticos.

Todos os `publish`/`subscribe` do atuador usam **QoS 1** (*at least once*): o comando de atuação não pode se perder; duplicatas são inofensivas porque escrever o mesmo estado duas vezes é idempotente.

---

## 3. Firmware do atuador (`ESP32_act`)

| Item | Valor |
|------|-------|
| Board | `esp32doit-devkit-v1` |
| Plataforma | PlatformIO `espressif32@6.5.0` (ESP-IDF 5.1.x) |
| Cliente MQTT | `esp-mqtt` nativo do IDF |
| Broker configurado | `mqtt://192.168.0.105:1883` (hardcoded em `mqtt_app.c`) |
| Wi-Fi | SSID `COM_N_26.1`, rede aberta (sem campo de senha na `wifi_config_t`) |

### 3.1 Estrutura

```text
ESP32_act/
├── platformio.ini
├── include/
│   ├── atuador.h    ← GPIOs, enum acionamento_sistema_t
│   ├── mqtt_app.h   ← enum estado_sistema_t, mqtt_start(), mqtt_publish_status()
│   └── wifi.h       ← SSID
└── src/
    ├── main.c       ← wifi_init_sta() → delay 5 s → mqtt_start() → gpioInit()
    ├── atuador.c    ← gpioInit(), atualiza_saidas()
    ├── mqtt_app.c   ← conexão, subscribe, parser de comandos, publish de status
    └── wifi.c       ← station mode + reconexão automática
```

### 3.2 Máquina de estados **implementada** (comando remoto)

O firmware atual é um executor de comandos: não lê sensor e não decide nada sozinho. Cada string recebida em `ESP32/COM/Atuador` leva diretamente a um estado, e cada troca de estado publica o status:

```mermaid
stateDiagram-v2
    direction LR
    [*] --> DESLIGADO
    DESLIGADO --> AQUECENDO: "AQUECIMENTO_ON"
    DESLIGADO --> REFRIGERANDO: "REFRIGERACAO_ON"
    AQUECENDO --> REFRIGERANDO: "REFRIGERACAO_ON"
    REFRIGERANDO --> AQUECENDO: "AQUECIMENTO_ON"
    AQUECENDO --> DESLIGADO: "SYSTEM_OFF"
    REFRIGERANDO --> DESLIGADO: "SYSTEM_OFF"

    note right of AQUECENDO
        GPIO18=1, GPIO19=0
        pub "Sistema aquecendo"
    end note
    note right of REFRIGERANDO
        GPIO18=0, GPIO19=1
        pub "Sistema resfriando"
    end note
    note left of DESLIGADO
        GPIO18=0, GPIO19=0
        pub "Sistema desligado"
    end note
```

| Estado | GPIO18 | GPIO19 | Como se chega |
|--------|:---:|:---:|----------------|
| `DESLIGADO` | 0 | 0 | Boot, ou comando `SYSTEM_OFF` |
| `AQUECENDO` | 1 | 0 | Comando `AQUECIMENTO_ON` |
| `REFRIGERANDO` | 0 | 1 | Comando `REFRIGERACAO_ON` |

`atualiza_saidas()` sempre **zera as duas saídas antes** de ligar a selecionada — intertravamento por software que impede aquecimento e refrigeração simultâneos mesmo em sequências rápidas de comandos.

> 📌 **Diferença em relação à versão anterior deste README:** a máquina com histerese (`T < SP − h` → aquecer etc.) e o estado `OVERRIDE` **não existem neste firmware**. Aqui *tudo* é "override": o modo remoto é o único modo. A histerese local está listada nas Pendências como requisito para o critério de Autonomia.

### 3.3 Bugs conhecidos no firmware

| # | Arquivo | Problema | Efeito | Correção |
|---|---------|----------|--------|----------|
| 1 | `atuador.c` → `gpioInit()` | A struct `gpio_config_t io_conf` é preenchida, mas **`gpio_config(&io_conf)` nunca é chamado** | GPIO18/19 podem não estar configurados como saída → relés não acionam de forma confiável (depende do estado de reset do pino) | Adicionar `gpio_config(&io_conf);` ao fim da função |
| 2 | `mqtt_app.c` → `MQTT_EVENT_DATA` | `char comando[32]` recebe `memcpy` de `event->data_len` bytes **sem verificar o limite** | Payload MQTT com ≥ 32 bytes estoura o buffer na pilha (crash ou corrupção) | Limitar a cópia: `int n = event->data_len < 31 ? event->data_len : 31;` |
| 3 | `main.c` / ordem de init | `gpioInit()` é chamado **depois** de `mqtt_start()` | Janela em que um comando pode chegar antes dos GPIOs existirem | Inverter: `gpioInit()` antes de `mqtt_start()` |

---

## 4. Firmware do broker (`embedded_brocker`)

Projeto ESP-IDF (template `app-template`) cuja única função é **ser o servidor MQTT do sistema**:

- Dependência: `espressif/mosquitto: "^2.0.20~7"` (Mosquitto real, portado para ESP-IDF pela Espressif).
- `mosq_broker_run()` roda numa task FreeRTOS dedicada (stack de 8192 bytes, prioridade 5), escutando em `0.0.0.0:1883`, sem TLS.
- Um callback (`handle_message_cb`) loga no monitor serial **toda mensagem que atravessa o broker** — cliente, tópico, QoS, retain e payload. Na apresentação isso funciona como um *sniffer* de camada de aplicação: dá para provar ao professor, em tempo real, que os dados das outras redes estão trafegando pela célula MQTT.

**Por que broker embarcado em vez de Mosquitto no PC?** Trade-off real: (+) a célula 3 tem sua própria infraestrutura de comunicação — se o PC cair, sensor e atuador continuam se falando, o que reforça a autonomia da célula como *rede*; (+) demonstra domínio do protocolo além do uso trivial de um broker pronto; (−) o ESP32 é um ponto único de falha com menos RAM e sem persistência de sessão/retained após reboot; (−) sem TLS e sem autenticação — aceitável numa rede fechada de bancada, inaceitável em produção.

---

## 5. Integração com o dashboard (Node-RED)

O Node-RED (`192.168.0.100`) conecta-se ao broker `192.168.0.105:1883` como cliente `Node-red` (MQTT v3.1.1) e expõe o grupo **REDE MQTT** no dashboard. O flow completo e sua documentação estão em [`../backbone/node_mqtt/`](../backbone/node_mqtt/README.md); em resumo:

| Widget | Nó dashboard | Fluxo |
|--------|--------------|-------|
| Botão **Aquecimento** | `ui_button` → `ESP32/COM/Atuador` | payload `AQUECIMENTO_ON` |
| Botão **Refrigeração** | `ui_button` → `ESP32/COM/Atuador` | payload `REFRIGERACAO_ON` |
| Botão **Desligar** | `ui_button` → `ESP32/COM/Atuador` | payload `SYSTEM_OFF` |
| Botão **Leitura temperatura** | `ui_button` → `ESP32S3/COM/get` | payload `GET_TEMP` |
| Texto **Status** | `ESP32/COM/Status` → `ui_text` | espelho do estado do atuador |
| Texto + **Chart temperatura** | `ESP32S3/COM/temperatura` → `ui_text`/`ui_chart` | telemetria com histórico |

A interoperabilidade cross-protocolo (Regra de Ouro do projeto) acontece no mesmo flow: comandos vindos do **CLP PROFINET** (`s7 in`, bits `DB7,X0.0..0.2`) e da **célula CAN** (`http in` `/mqtt_aquecer`, `/mqtt_resfriar`, `/mqtt_desligar`) convergem para os mesmos nós `function` que publicam em `ESP32/COM/Atuador` — ou seja, **qualquer rede opera o atuador desta célula**, e a temperatura desta célula é escrita de volta no CLP (`DB7,REAL2`) e enviada ao ESP da célula CAN via HTTP.

---

## 6. Pendências

- [ ] **Autonomia local (crítico — critério de 25%):** implementar no `ESP32_act` (ou no ESP32-S3) o controle bang-bang com histerese descrito na seção 3.2 como planejado: ler a temperatura (assinar `ESP32S3/COM/temperatura` já resolve, sem hardware novo), comparar com setpoint e decidir localmente, mantendo os comandos remotos como *override* explícito.
- [ ] **Versionar o firmware do ESP32-S3 (sensor)** em `firmware/` — hoje só os tópicos dele são conhecidos, pelo flow do backbone.
- [ ] **Corrigir os bugs 1–3** da seção 3.3 (o bug 1 impede o funcionamento básico).
- [ ] **Padronizar o tópico de comando** (`ESP32/COM/Atuador`) em todos os artefatos — firmware, flow e docs.
- [ ] Substituir os `.rar` por **código-fonte versionado** (pastas com `src/`/`include/` commitadas, `build/` e `.pio/` no `.gitignore`) — diff de código em RAR não existe, e o repositório carrega ~50 MB de cache de build.
- [ ] Avaliar **LWT** (`ESP32/COM/Status` = `offline` como testamento) para o bit de diagnóstico da Regra de Ouro.

---

## 7. Conteúdo desta pasta

```text
rede-mqtt/
├── README.md
├── componentes/   ← lista de componentes, datasheets
├── diagramas/     ← exports dos diagramas
├── figs/          ← fotos da bancada
└── firmware/
    ├── ESP32_act.rar         ← projeto PlatformIO do atuador
    ├── embedded_brocker.rar  ← projeto ESP-IDF do broker Mosquitto
    └── trabalho_COM.json     ← flow Node-RED da coluna MQTT
```
