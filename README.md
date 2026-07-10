
<div align="center">

# 🛰️ NEXUS — Sistema de Automação de Multiredes

**Backbone Ethernet integrando três células de produção com protocolos industriais distintos**

[![Disciplina](https://img.shields.io/badge/disciplina-Comunica%C3%A7%C3%A3o%20de%20Dados-blue.svg)](#)
[![Instituição](https://img.shields.io/badge/IFSC-DAELN-green.svg)](https://www.ifsc.edu.br/)
[![Protocolos](https://img.shields.io/badge/protocolos-PROFINET%20%7C%20CAN%20%7C%20MQTT-orange.svg)](#)
[![Backbone](https://img.shields.io/badge/backbone-Node--RED%20%2B%20MQTT-red.svg)](#)
[![Status](https://img.shields.io/badge/status-em%20desenvolvimento-yellow.svg)](#)
[![Entrega](https://img.shields.io/badge/entrega-26%2F06%2F2026-informational.svg)](#)
[![License](https://img.shields.io/badge/license-MIT-lightgrey.svg)](LICENSE)

</div>

---

<div align="center">
  <img src="figs/animacao-sistema.svg" alt="Animação do sistema NEXUS em operação: sensores publicando telemetria, operador comandando via dashboard e atuadores respondendo" width="900"/>
  <br/>
  <sub>Loop de 16s — sensor → rede → IHM → operador → atuador → feedback. Veja a leitura completa em <a href="#4-diagrama-de-blocos-geral-do-sistema">§4</a>.</sub>
</div>

---
Descrição da animação:

É possível visualizar nesta animação um exemplo de operação do NEXUS.
Ao iniciar o processo, é possível visualizar as 3 redes se comunicando com o dashboard no NodeRed via switch. Cada rede envia e recebe seus parametros passando por um switch. No dashbboard do computador do backbone é possível ver a temperatura do sensor da Célula 3 (MQTT). Onde é enviado um comando de aquecer, o comando é transportado pela rede até a Célula 3, liga o atuador e começa a aquecer a planta. O sensor envia o parâmetro de volta, e desativa o aquecimento ao chegar nas condições especificadas (O valor inicial de 18,2°C foi modificado para 21,4°C ao decorrer da execução).

## 1. Contexto

Projeto final da disciplina de **Comunicação de Dados** (IFSC — Departamento Acadêmico de Eletrônica). O objetivo é a **construção coletiva de um sistema de automação heterogêneo**: três duplas, três protocolos distintos, **uma única rede integrada**.

O critério central é a **interoperabilidade**: ao final, *o status de qualquer sensor da sala deve poder ser lido por qualquer célula, e qualquer atuador deve poder ser comandado, independentemente da rede em que esteja fisicamente conectado.*

| Item | Descrição |
|------|-------|
| Instituição | IFSC — Departamento Acadêmico de Eletrônica |
| Disciplina | Comunicação de Dados |
| Integrantes | Matheus e Cainã, Álvaro e Alexandre, Henzo e Lucas |
| Integração | Backbone Ethernet + Broker MQTT central (Node-RED) |


---

## 2. Descrição geral

O sistema é dividido em **três células de produção**, cada uma com três nós, construído por uma dupla e operando um **protocolo industrial diferente** na camada local. Cada nó é responsável por:

1. **Autonomia local** — ler o próprio sensor e comandar o próprio atuador *sem depender da rede externa*.
2. **Visibilidade global** — espelhar suas variáveis no backbone para que as outras células leiam/escrevam.

O Node-RED age como **hub multi-protocolo**: fala **S7/ISO-on-TCP** com o CLP (PROFINET), **HTTP REST** com o ESP32 da célula CAN, e **MQTT** com o ESP32 da célula MQTT. A "língua geral" não é um protocolo único no fio — é a **Tabela Global de Variáveis** consolidada dentro do Node-RED, que será apresentado mais a frente.

---

## 3. As três células (introdução)

| Dupla | Protocolo local | Controlador | Sensor | Atuador | Bridge p/ backbone |
|:-----:|:----------------|:------------|:-------|:--------|:-------------------|
| **1** — Cainã & Matheus | **PROFINET** | CLP Siemens S7‑121xC (`192.168.0.1`) | IHM KTP700 Basic | Inversor SINAMICS G120C | **S7 / ISO‑on‑TCP** (node‑red‑contrib‑s7) |
| **2** — Álvaro & Alexandre | **CAN** | ESP32 TWAI (`192.168.0.63`) | Potenciômetro (nó CAN substituto) | Display Dashboard E620 | **HTTP REST** (`/can`, `/set_nodered_*`) |
| **3** — Lucas & Henzo | **MQTT** | **ESP32 broker** — Mosquitto embarcado (`192.168.0.105:1883`) | Temperatura com **ESP32‑S3** (cliente MQTT) | Aquecimento + Refrigeração (GPIO18/19) com **ESP32 atuador** (cliente MQTT) | **MQTT** (Node‑RED como cliente do broker embarcado) |

Cada célula tem sua documentação completa, diagramas e código nas pastas abaixo.

---

## 4. Diagrama de blocos geral do sistema

```mermaid
flowchart TB
    subgraph CELULA1[" Célula 1 — PROFINET (Cainã & Matheus)"]
        direction TB
        S1["Sensor / IHM<rd/>KTP700 Basic"] --- PLC["CLP Siemens<br/>S7-1200"]
        A1["Atuador<br/>Inversor G120C"] --- PLC
    end

    subgraph CELULA2[" Célula 2 — CAN (Álvaro & Alexandre)"]
        direction TB
        S2["Sensor<br/>Potenciômetro (nó CAN)"] --- ESP2["ESP32<br/>TWAI/CAN"]
        A2["Atuador<br/>Display E620"] --- ESP2
    end

    subgraph CELULA3[" Célula 3 — MQTT (Lucas & Henzo)"]
        direction TB
        S3["Sensor temperatura<br/>ESP32-S3 (cliente MQTT)"] -.-> BR3[" ESP32 broker<br/>Mosquitto embarcado<br/>192.168.0.105:1883"]
        A3["ESP32 atuador (cliente MQTT)<br/>aquece GPIO18 / refrigera GPIO19"] -.-> BR3[" ESP32 broker<br/>Mosquitto embarcado<br/>192.168.0.105:1883"]
    end

    SW["🔀 Switch Ethernet<br/>(Backbone)"]
    NR["📊 Node-RED<br/>hub S7 + HTTP + MQTT<br/>dashboard + Tabela Global<br/>(cliente do broker da Célula 3)"]

    PLC -- "Aplicação: S7comm Apresentação: ISO-on-TCP  Transporte: TCP  Rede: IP  Enlace/Física: Ethernet" --> SW
    ESP2 -- "Aplicação: HTTP REST  Transporte: TCP  Rede:IP         Enlace/Física: WIFI802.11" --> SW
    BR3 -- "Aplicação: MQTT v3.1.1 (:1883)  Transporte: TCP  Rede: IP  Enlace/Física: WIFI 802.11" --> SW
    SW --- NR

    classDef cell fill:#ffffff,stroke:#888,stroke-width:1px;
    classDef local fill:#e8f5e9,stroke:#2e7d32,stroke-width:1px;
    classDef central fill:#fff3e0,stroke:#e65100,stroke-width:1px;
    classDef broker fill:#ffe0b2,stroke:#e65100,stroke-width:2px;
    class CELULA1,CELULA2,CELULA3 cell;
    class PLC,ESP2,ESP3,S3 local;
    class NR central;
    class BR3 broker;
```

### Legenda do diagrama

| Símbolo | Significado |
|--------|-------------|
| 🟩 Nós **verdes** (`PLC`, `ESP2`, sensores/atuadores) | **Controladores locais** de cada célula. |
|  Nó Broker MQTT(`ESP32 broker`) | **Broker da rede MQTT** — um ESP32 dedicado rodando **Mosquitto embarcado** (`192.168.0.105:1883`). Todo o tráfego MQTT saindo da célula(sensor, atuador e e fluxo que vai pro Node-RED) passa por ele. |
| 🟧 Nó **laranja** (`Node-RED`) | **Nível central (PC, `192.168.0.100`).** Agregação, dashboard, Tabela Global e comando remoto. **É cliente MQTT do broker embarcado**, não o servidor. |
| 🔀 `Switch` | Domínio de comutação do backbone Ethernet (full-duplex, sem colisão entre portas). |
| Linha pontilhada dentro da Célula 3 | Tráfego **MQTT via Wi-Fi** entre os três ESP32 da célula (a "rede local" da célula 3 é o próprio MQTT). |
| Rótulo das setas | Pilha de protocolos **camada por camada** (mapeamento OSI) que cada célula usa para subir ao backbone. |

 - **Células 1 e 2:** a malha de controle roda no controlador local (CLP fecha a malha do inversor; ESP32 CAN opera o barramento CAN de forma autônoma).
 - **Célula 3:** o broker embarcado dá autonomia de *comunicação* à célula (o MQTT local funciona sem o PC), a *decisão* de aquecer/refrigerar é **remota** — o ESP32 atuador só executa comandos recebidos via tópico (`AQUECIMENTO_ON` / `REFRIGERACAO_ON` / `SYSTEM_OFF`).


---

## 5. Sumário / Navegação

- 📁 [**Backbone (Node-RED + Broker)**](./backbone/README.md) — switch, broker MQTT, dashboard, tabela global
- 📁 [**Rede PROFINET**](./rede-profinet/README.md) — Dupla 1
- 📁 [**Rede CAN**](./rede-can/README.md) — Dupla 2
- 📁 [**Rede MQTT**](./rede-mqtt/README.md) — Dupla 3

---


## 6. Equipe

| Dupla | Integrantes | Responsabilidade |
|:-----:|:------------|:-----------------|
| 1 | Cainã, Matheus | Rede PROFINET + **hospedagem do broker/Node-RED** |
| 2 | Álvaro, Alexandre | Rede CAN |
| 3 | Lucas, Henzo | Rede MQTT |

---

## 7. Licença

Distribuído sob a licença [MIT](LICENSE).
