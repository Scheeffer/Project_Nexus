# 🌐 Gateway — Integração (Node-RED como hub multi-protocolo)

[![Node-RED](https://img.shields.io/badge/Node--RED-5.0-red.svg)](https://nodered.org/)
[![S7](https://img.shields.io/badge/PROFINET-node--red--contrib--s7-blue.svg)](#)
[![HTTP](https://img.shields.io/badge/CAN-HTTP%20REST-green.svg)](#)
[![MQTT](https://img.shields.io/badge/MQTT-cliente-orange.svg)](#)

### Definição de Gateway

Um **gateway** (ou porta de ligação) é um nó de rede projetado especificamente para interconectar sistemas, equipamentos ou sub-redes que utilizam arquiteturas, linguagens e protocolos de comunicação distintos. A sua função principal é atuar como um **tradutor e intermediário**, intercetando os dados num determinado formato, convertendo-os e retransmitindo-os no formato adequado para o sistema de destino.

No contexto do **Projeto Nexus**, o computador que executa o **Node-RED** atua estritamente como um **Gateway Industrial / IoT**, exercendo essa função através dos seguintes mecanismos:

* **Tradução Multiprotocolo:** O sistema interliga equipamentos de naturezas totalmente diferentes. Ele realiza a ingestão de dados de CLPs Siemens via **PROFINET** (através da biblioteca S7), de dispositivos na rede **CAN** e de sensores sem fios via **MQTT**. O Node-RED traduz e normaliza estes dados brutos incompatíveis num formato universal padronizado (objetos JSON).
* **Intermediação Centralizada (Middleware):** O computador configurado com o IP fixo `192.168.0.100` funciona como o ponto focal da rede `COM_N_26.1`. Os dispositivos finais não comunicam diretamente entre si; em vez disso, enviam as informações para este nó central, que faz a gestão e a ponte de dados entre as sub-redes.
* **Processamento e Roteamento de Aplicação:** Ao contrário de um elemento de infraestrutura pura (como um switch ou um backbone), que apenas transporta os pacotes de dados, o Node-RED abre e processa o conteúdo das mensagens. Ele aplica lógica de controlo (através dos nós `function` e `change`) e encaminha a informação processada para o destino final — seja alimentando a interface gráfica (Dashboard) ou enviando comandos de volta para os atuadores em campo.
# O que é o Node-RED - https://nodered.org/

O Node-RED é uma ferramenta de desenvolvimento de código aberto focada na programação baseada em fluxos (Flow-Based Programming), originalmente criada pela IBM para simplificar a interconexão de dispositivos de hardware, APIs e serviços online no ecossistema da Internet das Coisas (IoT). Por meio de uma interface gráfica executada diretamente no navegador, os usuários podem arrastar, soltar e interligar blocos funcionais chamados de "nós", o que reduz drasticamente a necessidade de codificação manual complexa para estabelecer fluxos de dados automatizados.

A base estrutural do Node-RED repousa sobre o conceito de nós de entrada, processamento e saída, onde cada nó executa uma tarefa atômica e se comunica enviando mensagens padronizadas na forma de objetos JSON (JavaScript Object Notation). Toda a lógica visual construída pelo usuário, incluindo a disposição e o encadeamento desses nós, é convertida e salva nativamente em arquivos JSON de texto limpo, o que confere à plataforma extrema leveza, portabilidade e facilidade para exportação, importação e controle de versão de projetos de automação.

Por baixo dessa interface visual, o Node-RED roda inteiramente sobre o Node.js, um ambiente de execução (runtime) JavaScript assíncrono e orientado a eventos, construído sobre o motor V8 do Google Chrome. A arquitetura de I/O (Entrada/Saída) não bloqueante do Node.js permite que o Node-RED gerencie milhares de conexões e eventos simultâneos em tempo real com um consumo mínimo de memória e CPU. Isso o torna altamente eficiente tanto para servidores robustos em nuvem quanto para gateways industriais de recursos limitados e sistemas embarcados.

Na comunicação entre equipamentos distintos, o Node-RED atua como um middleware ou gateway inteligente, realizando a ingestão multiprotocolo de dados provenientes de hardwares que utilizam linguagens incompatíveis, como MQTT, Modbus, HTTP ou comunicação Serial. Após capturar esses dados brutos, a ferramenta realiza o parsing e a normalização das informações em tempo real e as roteia para seus respectivos destinos — sejam eles bancos de dados, painéis de monitoramento ou comandos de controle enviados de volta para os dispositivos.

# O que é o Node-RED - https://nodered.org/

O Node-RED é uma ferramenta de desenvolvimento de código aberto focada na programação baseada em fluxos (Flow-Based Programming), originalmente criada pela IBM para simplificar a interconexão de dispositivos de hardware, APIs e serviços online no ecossistema da Internet das Coisas (IoT). Por meio de uma interface gráfica executada diretamente no navegador, os usuários podem arrastar, soltar e interligar blocos funcionais chamados de "nós", o que reduz drasticamente a necessidade de codificação manual complexa para estabelecer fluxos de dados automatizados.

A base estrutural do Node-RED repousa sobre o conceito de nós de entrada, processamento e saída, onde cada nó executa uma tarefa atômica e se comunica enviando mensagens padronizadas na forma de objetos JSON (JavaScript Object Notation). Toda a lógica visual construída pelo usuário, incluindo a disposição e o encadeamento desses nós, é convertida e salva nativamente em arquivos JSON de texto limpo, o que confere à plataforma extrema leveza, portabilidade e facilidade para exportação, importação e controle de versão de projetos de automação.

Por baixo dessa interface visual, o Node-RED roda inteiramente sobre o Node.js, um ambiente de execução (runtime) JavaScript assíncrono e orientado a eventos, construído sobre o motor V8 do Google Chrome. A arquitetura de I/O (Entrada/Saída) não bloqueante do Node.js permite que o Node-RED gerencie milhares de conexões e eventos simultâneos online com um consumo mínimo de memória e CPU. Isso o torna altamente eficiente tanto para servidores robustos em nuvem quanto para gateways industriais de recursos limitados e sistemas embarcados.

Na comunicação entre equipamentos distintos, o Node-RED atua como um middleware ou gateway inteligente, realizando a ingestão multiprotocolo de dados provenientes de hardwares que utilizam linguagens incompatíveis, como MQTT, Modbus, HTTP ou comunicação Serial. Após capturar esses dados brutos, a ferramenta realiza o parsing e a normalização das informações online e as roteia para seus respectivos destinos — sejam eles bancos de dados, painéis de monitoramento ou comandos de controle enviados de volta para os dispositivos.

# Configuração de Rede

Antes de detalhar a configuração do fluxo de nós, é necessário compreender a topologia da rede. O sistema utiliza um roteador TP-Link configurado como Access Point, permitindo que dispositivos conectados via cabo Ethernet e via Wi-Fi compartilhem a mesma faixa de IP. O endereço de gateway da rede está configurado como `192.168.0.167`.

A rede sem fio foi nomeada como **COM_N_26.1**, configurada como rede aberta (sem senha). A nomenclatura refere-se à disciplina de Comunicação de Dados ("COM"), ao turno noturno ("N") e ao período letivo de desenvolvimento ("26.1").

A imagem abaixo apresenta a configuração interna do roteador:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Configura%C3%A7%C3%A3o%20Modem.png" alt=" Componentes" width="900">

O computador no qual o Node-RED está instalado, e que atua como o **gateway** central do sistema, deve ter sua interface de conexão (RJ45 ou Wi-Fi) configurada com IP estático. Conforme detalhado na imagem a seguir, os parâmetros de adaptador de rede exigem a definição manual do endereço IPv4 fixado em `192.168.0.100`, alinhado com a respectiva máscara de sub-rede e apontando para o gateway padrão da rede (`192.168.0.167`).

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Ip%20Broker.png" alt=" Componentes" width="400">

# Fluxo Node-RED - Projeto Nexus

Estabelecida a topologia da rede, a arquitetura do fluxo segue a premissa de que todos os dispositivos e subsistemas já se encontram conectados à rede **COM_N_26.1**, com seus respectivos endereços de IP configurados conforme as especificações detalhadas nas documentações de cada módulo neste repositório.

Abaixo, é apresentada a visualização macro dos nós do projeto:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Visualiza%C3%A7%C3%A3o%20Node%20Projeto%20Nexus.png" alt=" Componentes" width="900">
> ⚠️ Os indicadores vermelhos na imagem ocorrem porque a captura de tela foi realizada com os dispositivos offline, especificamente para a elaboração da documentação deste repositório.

Na imagem acima, é possível identificar 3 colunas de dados, respectivamente: Profinet, CAN e MQTT.
O Node-RED é uma plataforma muito versátil. Sua aplicação permite a adição de bibliotecas que auxiliam no desenvolvimento dos projetos. Para o Projeto Nexus, são utilizadas 3 bibliotecas principais: a biblioteca nativa do Node-RED; a biblioteca `plcindustry`, para comunicação com o CLP Siemens; e a biblioteca `node-red-dashboard`, para a configuração da interface gráfica.

A imagem a seguir mostra a visualização das bibliotecas instaladas e utilizadas na aba "Gerenciar Paleta":

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Generaliza%C3%A7%C3%A3o%20n%C3%B3s%20utilizados.png" width="900">
> ⚠️ Na imagem acima, os indicadores "> XX nodes" informam a quantidade de nós disponíveis para uso em cada biblioteca.

Abaixo, segue a relação da quantidade de nós utilizados. **Total Geral:** 116 elementos

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
  
# Fluxos

Nas pastas deste repositório, você encontrará a explicação detalhada de cada coluna e de seus respectivos nós.

- 📁 [**Rede PROFINET**](./node_profinet/NODE_PROFINET.md)
- 📁 [**Rede CAN**](./node_can/NODE_CAN.md) 
- 📁 [**Rede MQTT**](./node_mqtt/NODE_MQTT.md)
