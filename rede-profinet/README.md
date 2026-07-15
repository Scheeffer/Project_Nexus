# 🟦 Rede PROFINET — Célula 1 (Cainã & Matheus)

## 1. Descrição do Projeto

O protocolo local utilizado é o **PROFINET**, com um CLP S7-1217C configurado como mestre da rede, uma IHM operando como interface de operação e visualização, e um Inversor de Frequência atuando como elemento final de controle. O CLP também opera como **bridge** para o gateway central (Node-RED) via protocolo **S7 / ISO‑on‑TCP**, nativo dos controladores Siemens. 

O objetivo desta rede consiste em controlar um motor de 380 V e 2 cv de potência por meio de um inversor de frequência. A IHM é utilizada para definir o setpoint de frequência, além de ligar e desligar o motor. A comunicação local independe do gateway central, operando de forma autônoma. A vantagem desta topologia é a integração entre o acionamento de potência e plataformas de supervisão de alto nível (Dashboards).

| Item | Valor |
|------|-------|
| Controlador | **CLP Siemens S7‑1217C** (endpoint `192.168.0.1`, rack 0 / slot 1) |
| Interface / IHM | **IHM KTP700 Basic** (endpoint `192.168.0.10`) |
| Atuador | **Inversor de frequência SINAMICS G120C** (endpoint `192.168.0.5`) |
| Bridge Gateway | **S7 / ISO‑on‑TCP** via `node‑red‑contrib‑s7` (cycletime 1000 ms) |
| Software | TIA Portal _(versão: V20)_ |

### Variáveis Disponíveis ao Node-RED

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `START` | `DB4,X0.0` | bool | Liga o inversor |
| `STOP` | `DB4,X0.1` | bool | Desliga o inversor |
| `ENTRADA_REF_FREQUENCIA` | `DB4,REAL2` | real | Define a frequência |
| `FDK_HZ` | `DB2,REAL6` | real | Feedback de frequência |
| `RESET_INV` | `DB4,X0.2` | bool | Reseta as falhas no inversor de frequência |
| `HABILITA NODE RED` | `DB2,X10.1` | bool | Habilita o comando via Node-RED |
| `FDK_VEL` | `DB6,REAL4` | real | Feedback da velocidade na rede CAN |
| `SET_VEL` | `DB6,REAL0` | real | Define o valor da velocidade na rede CAN |
| `Liga_AQ` | `DB7,X0.0` | bool | Liga o Aquecedor na rede MQTT |
| `Liga_Vent` | `DB7,X0.1` | bool | Liga o Ventilador na rede MQTT |
| `Desliga_Vent_AQ` | `DB7,X0.2` | bool | Desliga o Ventilador ou o Aquecedor na rede MQTT |
| `FDK_temp` | `DB7,REAL2` | real | Feedback do valor da temperatura na rede MQTT |


---

## 2. Diagrama de Blocos

O sistema adota uma **topologia em estrela**, onde todas as comunicações são centralizadas em um switch. Esta abordagem minimiza o impacto de falhas individuais nos cabos e simplifica o diagnóstico e a manutenção da infraestrutura de comunicação.

* **Switch Industrial Phoenix Contact FL Switch 1108:** Atua como o nó central da rede PROFINET. É responsável pelo chaveamento físico dos pacotes de dados, garantindo a comutação eficiente entre a camada de controle de campo e a camada de monitoramento.
  
  * **Camada de Controle de Campo:** Representa o nível operacional e físico do sistema, atuando diretamente no maquinário por meio de sensores, atuadores e CLPs. É caracterizada pela execução da lógica de controle em tempo real, exigindo comunicação de rede determinística e de baixíssima latência para garantir ações físicas e respostas imediatas.
  * **Camada de Monitoramento:** É o nível tecnológico de supervisão e gestão de dados focado na interface humana. Sua função é coletar as informações geradas no campo para compor históricos e alimentar Dashboards, permitindo que operadores acompanhem visualmente o sistema e ajustem parâmetros gerais sem a exigência crítica de controle em milissegundos.

* **CLP Siemens S7-1217C (Endereço IP: `192.168.0.1`):** Unidade central de processamento e lógica. Executa o algoritmo de controle do processo, gerencia os intertravamentos de segurança e coordena os demais periféricos.
 
* **IHM Siemens KTP700 Basic (Endereço IP: `192.168.0.10`):** Interface Homem-Máquina. Permite a interação do operador com o sistema para a visualização de variáveis em tempo real, inserção de parâmetros operacionais e interação com as demais redes.
 
* **Inversor de Frequência Siemens Sinamics G120C (Endereço IP: `192.168.0.5`):** Acionamento e controle de velocidade do motor elétrico. Envia dados de diagnóstico (frequência, status de ligado/desligado e falhas) e recebe comandos de referência do CLP.
  
* **Camada de Supervisão e Integração - Node-RED (Endereço IP: `192.168.0.100`):** Atua como o *gateway central* de dados. É responsável por coletar informações das redes para exibição em dashboards, além de gerenciar o acionamento e controle entre as redes. 

```
    IHM["IHM KTP700 Basic<br/>192.168.0.10"] == "PROFINET" ==> SW["FL Switch 1108 <br/>Phoenix Contact"]
    PLC["CLP S7-1217C<br/>192.168.0.1"] == "PROFINET" ==> SW
    G120["Inversor G120C<br/>192.168.0.5"] == "PROFINET" ==> SW
    SW == "S7 / ISO-on-TCP" ==> NR["Node-RED (gateway central)<br/>192.168.0.100"]
```
---

## 3. Diagrama de Estados 

Este diagrama de estados descreve a sequência lógica de controle e a troca de sinais que ocorrem via rede **PROFINET** entre a **IHM KTP700**, o **CLP S7-1217C** e o **Inversor G120C** durante o ciclo de operação do motor.

* **Inicialização -> PARADO (`Inicialização da Rede / Conexão PROFINET OK`):** Ao energizar o painel elétrico, o Switch estabelece a comunicação entre todos os nós. Assim que o CLP reconhece a presença da IHM e do G120C na rede sem erros de barramento, o sistema entra no modo de espera seguro (`Parado`).

* **PARADO -> ACIONANDO (`IHM envia bit START para o CLP / CLP envia START para o G120C`):** O comando de partida é acionado na tela da IHM. Essa informação é enviada via rede para o CLP, que processa as lógicas de intertravamento. Atendidas as condições de segurança, o CLP envia a palavra de comando com o bit de partida para o inversor via PROFINET, iniciando o acionamento do motor.

* **ACIONANDO -> RODANDO (`G120C retorna rampa concluída / Setpoint de frequência alcançado`):** O inversor acelera o motor. Assim que a frequência real medida pelo drive se iguala à frequência desejada (Setpoint), o inversor atualiza sua palavra de status na rede, informando ao CLP que a rampa foi concluída. O sistema assume o estado `Rodando`.

* **RODANDO -> PARADO (`IHM envia bit STOP para o CLP / CLP envia STOP para o G120C`):** Durante a operação normal, solicita-se a parada através da IHM. O comando é enviado para o CLP, que remove o sinal de partida destinado ao inversor. O G120C desacelera o motor de forma controlada até a inércia total, retornando ao estado `Parado`.

* **RODANDO -> FALHA (`G120C dispara bit de trip / CLP lê código de erro`):** Ocorrendo qualquer anomalia elétrica ou mecânica durante a operação, o inversor interrompe a saída por proteção e envia instantaneamente um bit de "Trip" (Falha Ativa) e o código do erro para o CLP. O controlador bloqueia o sistema e sinaliza o alarme na IHM.

* **FALHA -> PARADO (`IHM envia bit RESET para o CLP / CLP envia pulso Reset para o G120C`):** Após a eliminação da causa do problema em campo, o comando de "Reset" é acionado na IHM. O CLP processa a solicitação e envia um pulso (borda de subida) de reset ao inversor. Caso a condição de falha não persista, o inversor limpa o erro e o sistema retorna ao estado `Parado`.

```
mermaid
stateDiagram-v2
    [*] --> Parado : Inicialização da Rede / Conexão PROFINET OK
    
    Parado --> Acionando : IHM envia bit START para o CLP / CLP envia START para o G120C
    
    Acionando --> Rodando : G120C retorna rampa concluída / Setpoint de frequência alcançado
    
    Rodando --> Parado : IHM envia bit STOP para o CLP / CLP envia STOP para o G120C
    
    Rodando --> Falha : G120C dispara bit de trip / CLP lê código de erro 
    
    Falha --> Parado : IHM envia bit RESET para o CLP / CLP envia pulso Reset para o G120C
```

## 4. Diagrama de Sequência

O **Diagrama de Sequência** a seguir apresenta o fluxo de comandos necessários para acionar o inversor a partir da IHM via rede PROFINET. Cada interação atualiza o estado do sistema, compreendendo os fluxos de ligar, desligar e resetar o inversor em caso de falha.  

```mermaid
sequenceDiagram
    autonumber
    actor Operador
    participant IHM as IHM (KTP700)
    participant CLP as CLP (S7-1217C)
    participant G120 as Inversor (G120C)

    Note over IHM, G120: Inicialização / Conexão PROFINET OK
    CLP->>G120: Envia STW1 = 0x047E (Pronto / Saídas Bloqueadas)
    CLP->>IHM: Atualiza Status: "Aguardando Setpoint"
    IHM->>Operador: Exibe tela com botão START desabilitado

    %% Etapa de Seleção de Frequência
    rect rgb(240, 240, 240)
        Note over Operador, CLP: Etapa de Seleção de Frequência
        Operador->>IHM: Insere Frequência (Ex: 60Hz)
        IHM->>CLP: Envia valor do Setpoint via PROFINET
        CLP->>CLP: Normaliza valor para formato Profidrive (NSOLL_A)
        CLP->>IHM: Habilita botão "START" na tela
    end

    %% Partida do Motor
    Operador->>IHM: Pressiona botão "START"
    IHM->>CLP: Envia bit de comando (START active)
    CLP->>G120: Altera palavra de controle (STW1 = 0x047F)
    Note over G120: Inicia Rampa de Aceleração
    G120->>CLP: Envia velocidade atual (NIST_A) & ZSW1
    
    %% Frequência Alcançada
    Note over G120: Setpoint de Frequência Alcançado
    G120->>CLP: Altera bit de status (ZSW1.8 = 1)
    CLP->>IHM: Atualiza Status: "Motor Rodando"
    IHM->>Operador: Exibe valor atualizado da frequência

    %% Parada do Motor
    Operador->>IHM: Pressiona botão "STOP"
    IHM->>CLP: Envia bit de comando (STOP active)
    CLP->>G120: Retorna palavra de controle (STW1 = 0x047E)
    Note over G120: Desaceleração por Rampa (OFF1)
    G120->>CLP: Altera bit de status (ZSW1.8 = 0)
    CLP->>IHM: Atualiza Status: "Motor Parado"
```

---

## 5. Diagrama Elétrico 

A documentação do projeto engloba um diagrama elétrico multifilar, essencial para observar todas as conexões elétricas de potência e as derivações da **Rede PROFINET**. 

Veja a documentação completa no arquivo [Diagrama Multifilar Profinet](https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/diagramas/Diagrama_Multifilar.pdf).

Segue arquivo do programa do CLP, compativel com software TIA Portal - [Programação do CLP](https://github.com/Scheeffer/Project_Nexus/tree/main/rede-profinet/projeto-tia).

Na imagem a seguir, detalham-se as conexões PROFINET configuradas no ambiente TIA Portal, evidenciando a comunicação estabelecida entre o IO Controller (CLP Mestre) e os respectivos IO Devices (IHM e Inversor de Frequência).

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/clp_profinet.jpeg" alt="Componentes" width="500">

[Programação do CLP](https://github.com/Scheeffer/Project_Nexus/tree/main/rede-profinet/projeto-tia).

## 6. Componentes e Modelos
| Componente | Especificação | Fornecedor| Link | Manual |
|-----------|---------------|:---:|----------------|-------|
| CLP Siemens S7-1217C | CPU 1217C DC/DC/DC | Siemens  |<a href="https://www.mercadolivre.com.br/clp-siemens-cpu1217c-dcdcdc-6es7-2171ag400xb0-s71200/up/MLBU3687628652?pdp_filters=item_id%3AMLB6067313602&from=gshop&matt_tool=59586449&matt_word=&matt_source=google&matt_campaign_id=22120855419&matt_ad_group_id=179138688171&matt_match_type=&matt_network=g&matt_device=c&matt_creative=729092955262&matt_keyword=&matt_ad_position=&matt_ad_type=pla&matt_merchant_id=463061090&matt_product_id=MLBU3687628652&matt_product_partition_id=2391408921319&matt_target_id=pla-2391408921319&cq_src=google_ads&cq_cmp=22120855419&cq_net=g&cq_plt=gp&cq_med=pla&gad_source=1&gad_campaignid=22120855419&gbraid=0AAAAAD93qcC-vFAzmTlMO4eXjA2yH04be&gclid=Cj0KCQjwr4jSBhCSARIsAOX1E-LskTHhsEsUYxt1abX5UpLJKIB4Qt8gBJ08Qnp1oUG8r6bVNgvKWEkaAhdOEALw_wcB" target="_blank">Link</a>| <a href="https://cache.industry.siemens.com/dl/files/302/109977302/att_1307432/v2/s71200_system_manual_en-US.pdf" target="_blank">Manual</a>|
| IHM KTP700 Basic | HMI 7" |  Siemens |<a href="https://www.mercadolivre.com.br/ihm-siemens-ktp700-basic-6av21232gb030ax0-nova/up/MLBU1754608387?pdp_filters=item_id%3AMLB3699801806&from=gshop&matt_tool=35963832&matt_word=&matt_source=google&matt_campaign_id=22090193654&matt_ad_group_id=174661932924&matt_match_type=&matt_network=g&matt_device=c&matt_creative=727914177760&matt_keyword=&matt_ad_position=&matt_ad_type=pla&matt_merchant_id=5678717624&matt_product_id=MLBU1754608387&matt_product_partition_id=2389866685188&matt_target_id=pla-2389866685188&cq_src=google_ads&cq_cmp=22090193654&cq_net=g&cq_plt=gp&cq_med=pla&gad_source=1&gad_campaignid=22090193654&gbraid=0AAAAAD93qcBA6YeC6NhF5lCo67H0xXuKx&gclid=Cj0KCQjwr4jSBhCSARIsAOX1E-IrfxbxojItCajlbwRjY_jW8jaHDfC_f4qBHDq-mUOMMTpIZJivrAIaAjdXEALw_wcB" target="_blank">Link</a>| <a href="https://support.industry.siemens.com/dl/files/542/109994542/att_1341412/v1/eu_data_act_hmi_panels_product_information_en-US.pdf" target="_blank">Manual</a>|
| Inversor SINAMICS G120C |0,55kW a 132kW (0,75CV a 150CV)| Siemens |<a href="https://www.dimensional.com.br/inversor-trifasico-380-480v-4-1a-2-2kw-sinamics-g120c-6sl32101ke158uf2-siemens/p" target="_blank">Link</a>| <a href="https://cache.industry.siemens.com/dl/files/116/109818116/att_1354200/v1/G120C_op_instr_0226_en-US.pdf" target="_blank">Manual</a>
| FL Switch 1000 |10/100/1000 MBit/s | Phoenix Contact | <a href="https://www.mercadolivre.com.br/switch-ethernet-industrial-8-portas-rj-45-phoenix-contact-100mbs/p/MLB22766483#polycard_client=search-desktop&be_origin=backend&search_layout=grid&position=10&type=product&tracking_id=efb02124-a3e4-4a02-90ae-1a1b275c851e&wid=MLB5157563910&sid=search" target="_blank">Link</a>| <a href="https://product-download.phoenixcontact.com/9745187?response-content-disposition=inline;%20filename%3D%224138_en_E.pdf%22&Expires=1782785463&Signature=msPp8Eq8MXTxJ6kaXXaRVwvEBTcI-9kMYA-qBCapOC9VKcxcGpJtXoBGBoz889TkVqNsSzWGy8udwmioMobk82UywyKuueZn2JCsGU5mjFtz2bECTVISr5Ze3GAnM9FroefE6gqFY9chRWQXxiIxv11I2~RE5xv0H~UReCsvXjASOKIzFKflEyqU6pojK6w4GKmOYfo8~p2KynMD~w~~qjwhNZaE2QEdRF7lLoyhFBnpOLcZXrSwF~5091PX1EHsyTGtLYjR7wzFR-20AH7g8fCGye3YClK-fKr8-6c8hrldg18A8ywB9pbiPyqvPcLXmnDX~HSYD-vUQzr3QjEf5A__&Key-Pair-Id=K1I2N54A7B0GD" target="_blank">Manual</a>|
| Gabinete Para quadro Elétrico | 50x40x25cm | -|
| Proteção de curto circuito e sobre carga | Disjuntores trifásico 10A curva C  | -| |
| Fonte de alimentação | 24V VCC 2,5A  | EATON|<a href="https://www.mercadolivre.com.br/fonte-chaveada-24v-25a-psee2g1ac24dc60wsc-phoenix/up/MLBU2744053218#polycard_client=search-desktop&be_origin=backend&search_layout=grid&position=7&type=product&tracking_id=3575352c-2871-414b-bc9d-7d146c6c67a3&wid=MLB5173451578&sid=search" target="_blank">Link</a> 
---

Na imagem abaixo há o quadro de automação montado com todos os equipamentos necessários para fazer as conexões da rede profinet.

* **CLP - S7-1217C** 
* **Switch** com 8 pontos de conexão
* **Inversor G120C** 
* **Fonte de alimentação** 24V 2,5A
* **Borne Fusível** com elemento fusível de 1A
* **Disjuntor trifásico** geral de 10A
* **Disjuntor motor** para o inversor regulado em 6,3A 
* **Disjuntor monofásico** de 6A para a fonte 24V
* **Bloco de distribuição** verde para o aterramento; azul para o neutro; cinza para o GND e vermelho para o 24VCC.


<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/Componentes%20internos.png" alt=" Componentes" width="300">


Na imagem abaixo há o quadro de automação visto por fora e a tela da IHM ligada na tela geral de controle das redes.


<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/IHM%20Externo.png" alt="IHM" width="300">

Na imagem abaixo há o quadro de automação visto por dentro, com as conexões de alimentação e da rede profinet.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/IHM%20Interno.png" alt="IHM" width="300">
---

## 7. Telas IHM 

### Tela IHM Geral

Esta tela contém os botões de seleção da rede a ser controlada ou monitorada. Devido à arquitetura do CLP e aos seus requisitos de segurança, configurou-se um comando para prioridade de controle,`Habilita Comando IHM`, permitindo a alternância da operação entre a IHM e o Node-RED. Para a operação via Node-RED, exige-se o acionamento prévio do botão `Acionamento PROFINET via Node` diretamente no dashboard.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/geral_ihm.jpeg" alt="Componentes" width="500">


### Tela IHM Rede PROFINET 

A imagem a seguir ilustra a interface de controle e leitura da rede PROFINET. Os botões realizam funções dedicadas para atuar sobre o inversor. Nos blocos de exibição, `Velocidade Bomba Atual` indica a frequência instantânea do equipamento, enquanto o campo `Frequência` é editável para a definição de novos setpoints.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/profinet_ihm.jpeg" alt="Componentes" width="500">
 

### Tela IHM Rede CAN

A imagem abaixo demonstra a interface de monitoramento e controle destinada à rede CAN. Apresenta dois blocos de informação principais: o `Mostrador de Velocidade` exibe o valor em tempo real lido no velocímetro, e o campo `Setpoint Velocidade` é editável para a parametrização de novos valores de referência.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/can_ihm.jpeg" alt="Componentes" width="500">


### Tela IHM Rede MQTT 

A interface de atuação e leitura da rede MQTT está ilustrada abaixo. Os botões enviam dados de status para publicação no broker MQTT, e o bloco de texto exibe o valor da temperatura mensurado pelo sensor pertencente a esta mesma rede.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/rede-profinet/figs/mqtt_ihm.jpeg" alt="Componentes" width="500">
```
