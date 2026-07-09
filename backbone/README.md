# 🌐 Backbone — Integração (Node-RED como hub multi-protocolo)

[![Node-RED](https://img.shields.io/badge/Node--RED-5.0-red.svg)](https://nodered.org/)
[![S7](https://img.shields.io/badge/PROFINET-node--red--contrib--s7-blue.svg)](#)
[![HTTP](https://img.shields.io/badge/CAN-HTTP%20REST-green.svg)](#)
[![MQTT](https://img.shields.io/badge/MQTT-cliente-orange.svg)](#)

O backbone é o núcleo central de alta capacidade da rede (sua "espinha dorsal"), cuja função é agregar, rotear e escoar volumes de tráfego de dados entre diversas sub-redes menores, provedores regionais e data centers, garantindo a conectividade de longa distância e a resiliência de toda a topologia. Em nosso projeto usamos o **Node-red**, como  backbone, sendo ele responsavel pelo gerenciamento e fluxo de dados dos nossos dispositivos.

# O que é o Node-red - https://nodered.org/

O Node-RED é uma ferramenta de desenvolvimento de código aberto focada na programação baseada em fluxos (Flow-Based Programming), originalmente criada pela IBM para simplificar a interconexão de dispositivos de hardware, APIs e serviços online no ecossistema da Internet das Coisas (IoT). Através de uma interface gráfica executada diretamente no navegador, os usuários podem arrastar, soltar e interligar blocos funcionais chamados de "nós", o que reduz drasticamente a necessidade de codificação manual complexa para estabelecer fluxos de dados automatizados.

A base estrutural do Node-RED repousa sobre o conceito de nós de entrada, processamento e saída, onde cada nó executa uma tarefa atômica e se comunica enviando mensagens padronizadas na forma de objetos JSON (JavaScript Object Notation). Toda a lógica visual construída pelo usuário, incluindo a disposição e o encadeamento desses nós, é convertida e salva nativamente em arquivos JSON de texto limpo, o que confere à plataforma uma extrema leveza, portabilidade e facilidade para exportação, importação e controle de versão de projetos de automação.

Por baixo dessa interface visual, o Node-RED roda inteiramente sobre o Node.js, um ambiente de execução (runtime) JavaScript assíncrono e orientado a eventos, construído sobre o motor V8 do Google Chrome. A arquitetura de I/O (Entrada/Saída) não-bloqueante do Node.js permite que o Node-RED gerencie milhares de conexões e eventos simultâneos em tempo real com um consumo mínimo de memória e CPU, tornando-o altamente eficiente tanto para servidores robustos em nuvem quanto para gateways industriais de recursos limitados e sistemas embarcados.

Na comunicação entre equipamentos distintos, o Node-RED atua como um middleware ou gateway inteligente, realizando a ingestão multiprotocolo de dados provenientes de hardwares que utilizam linguagens incompatíveis, como MQTT, Modbus, HTTP ou comunicação Serial. Após capturar esses dados brutos, a ferramenta realiza o parsing e a normalização das informações em tempo real e as roteia para seus respectivos destinos — sejam eles bancos de dados, painéis de monitoramento ou comandos de controle enviados de volta para dispositivos

# Configuração de Rede

Antes de explicar nosso fluxo de nós do node-red, segue a baixo nossa configuração de rede.
Como gateway, o nosso via de dados para comunicação entre os dispositivos, utilizamos um roteador Tp-link com a configuração Access Point, a onde os dispotivos conectados via rede e os dispotivos via Wifi compartilham a mesma faixa de rede, aqui configuramos nosso ip gateway como 192.168.0.167.
Nossa rede se Wifi foi nomeada exclusivamente para esse projeto: COM_N_26.1, sem senha, "COM" sigla para materia academia desse projeto, Comunicação de Dados, "N" de noturno, que é muito representativo e necessário exposição, pois o horário noturno é o unico que temos para dedicar a graduação devido necessidade de trabalhar durante o dia e "26.1", ano e semestre de desenvolvimento desse projeto, que até onde esperamos que seja o ultimo.

Segue abaixo configuração interna do roteador:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Configura%C3%A7%C3%A3o%20Modem.png" alt=" Componentes" width="900">

o Computador em que o node-red estiver instalado deve estar com a porta de conexão, RJ45 ou Wifi com o ip fixado em 192.168.0.100, conforme imagem:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Ip%20Broker.png" alt=" Componentes" width="400">

# Fluxo Node-red Projeto Nexus
Com a contextualização da configuração de rede realizada, vamos seguir com a nossa configuração de fluxo. Partimos do principio que todos os sistema de disposivos estão conectados na rede COM_N_26.1, com seus respectivos Ips conforme especificado em suas própias pastas desse repositório.

Segue a visualização macro do nó do projeto:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Visualiza%C3%A7%C3%A3o%20Node%20Projeto%20Nexus.png" alt=" Componentes" width="900">
> ⚠️ Indicadores vermelhos na imagem, são devido a aquisição de imagem ter sido com os dispositivos offline, para elaboração desse repositório.

Na Imagem acima é possivel identificar 3 colunas de dados, respectivamente, Profinet, Can e Mqtt.
O Node-red é uma plataforma muito versatil em sua aplicação conta com a possibilidade de adicionar bibliotecas que ajudam no desenvolvimento dos projetos, para nosso projeto, utilizamos 3 bibliotecas, a biblioteca nativa do node-red,com seu respectivo nome, a biblioteca plcindustry, exclusiva para comunicação com clp Siemens e a biblioteca node-red-dashboard, para configuração do nosso dashboard. Segue imagem da visualização na Aba Gerenciar Paleta das bibliotecas instaladas e utilizadas:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Generaliza%C3%A7%C3%A3o%20n%C3%B3s%20utilizados.png" width="900">
> ⚠️ Na imagem a cima, os indicadores > XX nodes, indicam quantas nós para uso cada biblioteca tem.


Sobre a quantidade de nós utilizados temos segue a relação: **Total Geral:** 116 elementos

### Nós Nativos (Core Node-RED) - 78 nós
* **22** `comment` (Comentários visuais)
* **15** `function` (Lógica em JavaScript)
* **7** `http in` (Recepção de requisições HTTP)
* **7** `http response` (Respostas HTTP)
* **7** `change` (Alteração de payloads)
* **5** `trigger` (Gatilhos de tempo/eventos)
* **4** `inject` (Entradas manuais/injetores)
* **3** `http request` (Envio de requisições HTTP)
* **2** `mqtt in` (Recepção de dados MQTT)
* **2** `mqtt out` (Envio de dados MQTT)
* **1** `json` (Conversão de dados)
* **1** `tab` (Estrutura da aba do projeto)
* **1** `mqtt-broker` (Configuração do servidor MQTT)
* **1** `global-config` (Declaração de dependências dos módulos)

### Interface Gráfica (`node-red-dashboard`) - 22 nós
* **7** `ui_button` (Botões de ação)
* **5** `ui_text` (Exibição de texto)
* **3** `ui_group` (Agrupamento de interface)
* **2** `ui_gauge` (Mostradores analógicos)
* **1** `ui_slider` (Controle deslizante)
* **1** `ui_numeric` (Entrada numérica)
* **1** `ui_chart` (Gráfico de linha)
* **1** `ui_spacer` (Espaçador de layout)
* **1** `ui_tab` (Aba do dashboard)

### Comunicação com CLP (`plcindustry` / S7) - 16 nós
* **8** `s7 in` (Leitura de variáveis do CLP)
* **7** `s7 out` (Escrita de variáveis no CLP)
* **1** `s7 endpoint` (Configuração de conexão IP/Rack/Slot)

## Explicação Nós

### Campo Profinet
Antes de explicar os nós vamos explicar como a comunicação do node-red com o CLP funciona, ao utilizar qualquer balão da biblioteca plcindustry é possivel selecionar e cadastrar o CLP de interesse, como explicação:
Clique na opção "+":
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_como_cadastrar_disp_profinet.png" width="400">

Insira os dados recpectivos do seu CLP siemens:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/st_config_disp.png" width="400">

Cadastre as variaveis de memória para uso no node-red do seu dispotivo:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_cadastro_variavel_profinet.png" width="400">

Com isso ja explicado vamos para nosso Fluxo Profinet

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_profinet.png" width="600">
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_profinet_2.png" width="600">

Para explicar esse nó vamos generalizar e explicar cada tipo de nó aplicado:

#### Tipo 1 - Leitura de Variavel S7 - S7 in
Nó para leitura de variáveis do CLP, realizado seleção do dispotivo de origem, variável de interesse e opções de leitura:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_in_profinet.png" width="400">

No caso acima é o retorno do feedback da frequencia do inversor devolvida como um dado Float de 32-bits.

#### Tipo 2 - Escrita de Variavel S7 - S7 out
Nó para escrita de variáveis do CLP, realizado seleção do dispotivo de origem, variável de interesse:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_out_profinet.png" width="400">

Nesse tipo foram selecionados as variaveis que recebem dados boleanos (true ou false), o que muda entre eles é o endereço de memória:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `START` | `DB4.DBX0.0` | bool | Liga o inversor |
| `STOP` | `DB4.DBX0.1` | bool | Desliga o inversor |
| `RESET_INV` | `DB4,x0.2` | bool | Reseta as falhas no inversor de frequência |
| `HABILITA NODE RED` | `DB2,X10.1` | bool | Habilita o comando via node red |

Unico que recebe um valor diferente é o S7 out - SET_FREQ, que recebe um valor real para atualizar o valor de frequeência do Inversor:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `ENTRADA_REF_FREQUENCIA` | `DB4,REAL2` | real | Seta frequência |

#### Tipo 3 - Nó Trigger

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_trigger.png" width="400">

Nesse tipo é configurado que caso esse nó receba um dado de ativação, em sua saída terá um pulso de sáida "true" por 1 segundo, após esse tempo será um comando "false", foi configuraado dessa forma devido a lógica de funcionamento do CLP Siemens, como é possivel visualizar os nós trigger estão conectados com os nós que recebem dados boleanos, responsavel por ligar, desligar e resetar o inversor, caso esses nós recebessem continuamente os dados true ou false, isso afearia a logica do CLP, não senso possivel alterar os valores das variáveis por outros comandos.
Todos os nós trigger estão configurados da mesma forma

#### Tipo 4 - Funcition 2 - Conversor



Como explicado no tipo 1, o dado do S7 in - REF_INV retorna um dado Float de 32-bits, esse function possui um script para converter esse dado para msg.payload para ser exibido no dashboard.


