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
Como gateway, o nosso access point para comunicação entre os dispositivos, utilizamos um roteador Tp-link com a configuração Access Point, a onde os dispotivos conectados via rede e os dispotivos via Wifi compartilham a mesma faixa de rede, nossa rede se Wifi  foi nomeada exclusivamente para esse projeto COM_N_26.1, "COM" sigla para materia academia desse projeto, Comunicação de Dados, "N" de noturno, que muito representativo e necessário exposição, pois o horário noturno é o unico que temos para nos dedicar a graduação a necessidade de trabalhar durante o dia e "26.1", ano e semestre de desenvolvimento desse projeto, que até onde esperamos que seja o ultimo.
Segue abaixo configuração interna do roteador:



---


> ⚠️ **Segurança:** revise os flows antes de commitar — remova IPs sensíveis se necessário e **nunca** versione senhas de broker.
