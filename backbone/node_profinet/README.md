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

No caso acima é o retorno do feedback da frequencia do inversor devolvida como um dado Float de 32-bits. Além do feeback da frequência do Inversor, esse nó da comando para parar o contador do nó tipo 6.

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

#### Tipo 4 - Function 2 - Conversor

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_function_2.png" width="600">

Como explicado no tipo 1, o dado do S7 in - REF_INV retorna um dado Float de 32-bits, esse function possui um script para converter esse dado para msg.payload para ser exibido no dashboard.
```
msg.payload = Number(msg.payload.toFixed(2));
return msg;
```

#### Tipo 5 - Toogle

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_toogle.png" width="600">

Essa função tem como principio refinar o controle do node-rede e a IHM do CLP, ele é resposavel por alterar uma variavel interna do CLP que altera a prioridade do comando entre o CLP e o Node red
```
// Recupera o estado atual do contexto do fluxo (se não existir, começa como false)
let estado = context.get('estado') || false;

// Inverte o estado (se era true vira false, se era false vira true)
estado = !estado;

// Salva o novo estado de volta na memória
context.set('estado', estado);

// Envia o novo estado no payload
msg.payload = estado;
return msg;
```
#### Tipo 6 - Cronômetro de Feedback de envio e retorno de mensagem
Esse tipo, também presente nas demais colunas, é constituido de 2 nós o `Inicia cronometro` e `Para Cronômetro e Mede (ms)`. São dois nós dependentes, o nó `Inicia cronometro`,com uma saída, possui uma variavel que ao ser acionada incia um contador de tempo, nessa caso, quando o valor de frequência é alterado pelo dashboard, o nó `Para Cronômetro e Mede (ms)` para o contador de tempo e o converte para ms, possuí duas saída, uma para o codigo seguir seu caminho para o nó dashboard de gauge e outra para o nó dashbaord de texto.

Nó `Inicia cronometro`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_inicia.png" width="600">

```
// Salva o momento exato em que o comando passou por aqui
flow.set('t_start', Date.now());

// Repassa a mensagem original sem alterações
return msg;
```

Nó `Para Cronômetro e Mede (ms)`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_para_mede.png" width="600">

```
// Recupera o tempo salvo na memória
let start = flow.get('t_start');

if (start) {
    // Calcula a diferença entre o agora e o tempo inicial
    let tempo = Date.now() - start;
    
    // Limpa a memória para a próxima medição
    flow.set('t_start', null); 
    
    // Cria uma nova mensagem com o tempo calculado
    let msgTempo = { 
        payload: tempo + " ms", 
        topic: "Velocidade_Comunicacao" 
    };
    
    // Envia a msg original na Saída 1 e o tempo na Saída 2
    return [msg, msgTempo];
}

// Se não houver tempo inicial, apenas passa a msg original
return [msg, null];
```

> ⚠️ **No código é possivel verificar a variável 't_start', como essas funções se repetem no código, é necessária alteração para não gerar conflito e falhar a sincronização dos contadores com seus respectivos inica e para, para isso foi diferenciado essa variavel para 't_start_2' e 't_start_3', esse dado deve ser alterado tanto no `Inicia cronometro` quanto `Para Cronômetro e Mede (ms)` para ficar sincronizado. obs: essa variável repete duas vezes no `Para Cronômetro e Mede (ms)`.

#### Tipo 7 - Exportar dados para nexus-web

Esse tipo se trata da exportação de dados para o site nexus-web, que possui 4 nós, um http in `GET /api/state`, um http response `http` e dois nós function `Return current state` e `set PROFINET state`

Os nós http configuram o direcionamento no node para o site externo.

`GET /api/state`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_in_nexus_web.png" width="300">

`http`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_request_nexus_web.png" width="300">

O nó function `set PROFINET state`, configura o parametro da frequência para exportação

`set PROFINET state`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_profinet_state_nexus_web.png" width="400">

```
const protocolState = flow.get("protocolState") || {};

protocolState.PROFINET = {
    ...(protocolState.PROFINET || {}),
    online: true,
    frequencia: Number(msg.payload ?? 0)
};

flow.set("protocolState", protocolState);

return msg;
```

O nó function `Return current state`, compila os dados extraidos, tanto da coluna profinet quanto da can e mqtt e os exporta para o link do nexus-web.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_response_nexus_web.png" width="400">

```
const defaultState = {
    deviceId: "NEXUS Central Node V2",

    PROFINET: {
        online: true,
        frequencia: 0,
        estado: false,
        habilitar: false,
        resetar: false
    },
    CAN: {
        online: true,
        velocidade: 0,
        marcha: 0,
        erro: 0
    },
    MQTT: {
        online: true,
        temperatura: "---",
        estado: "---"
    }
};

const saved = flow.get("protocolState") || {};
const response = {
    deviceId: saved.deviceId || defaultState.deviceId,
    PROFINET: {
        ...defaultState.PROFINET,
        ...(saved.PROFINET || {})
    },
    CAN: {
        ...defaultState.CAN,
        ...(saved.CAN || {})
    },
    MQTT: {
        ...defaultState.MQTT,
        ...(saved.MQTT || {})
    }
};

msg.headers = {
    "Content-Type": "application/json",
    "Access-Control-Allow-Origin": "https://curricularium.infinityfreeapp.com"
};

msg.payload = JSON.stringify(response);

return msg;
```
#### Tipo 8 -  Nós de Dashboard

Antes de explicar os nós de dashboard, vamos esclarecer a configuração do dashboard, no nosso caso, foi realizado a criação de um dashbaord com 3 grupos, cada um desses grupos sendo um tipo de comunicação e na exibição estão expostos da mesma forma, espectivamente Profinet, CAN e MQTT.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_dashboard_groups.png" width="400">

 A pagina do Dashboard fica dessa forma:
 
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Dashboard.png" width="800">

Voltando para os nós nessa secção, há 7 nós da biblioteca dashboard, 4 botões responsavel pelo acionionamento dos nós trigger (tipo 3) e toggle (tipo 5) para as entradas do clp, 1 nó numeric para atualizar os valores de frenquência para o clp e 2 nós de exibição de dados.

##### Botões

Os botões são configurados de forma simples, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE PROFINET do dashboard NEXUS, em icon e configurado um indicador gráfico retirado do site https://fontawesome.com/v4/icon/arrow-right, Label o Nome exibido no dashboard, opções opcionais n foram configuradas, e foi configurando para quando acionado emitir um payload  boleano "true". todos os botões dessa secção foram configurados da mesma forma, alterando somente o nome da label.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_button_profinet.png" width="400">

Labels presentes na configuração:
| `Liga Inversor` | `Desliga Inversor` | `Reset Inversor` | `Acionamento PROFINET via Node` |

##### Numeric
No nó Numeric, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE PROFINET do dashboard NEXUS, foi configurado o Label `Ajuste de frequência`, o formato de valor `{{value}}` e as configurações do numeric, como o range de 0 a 60 e passo de 5. Esse nó quando alterado atualiza o valor da frequencia do inversor e também inicia o no contador do nó tipo 6.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_slider_profinet.png" width="400">
 
##### Gauge

No nó Gauge, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE PROFINET do dashboard NEXUS, nele foi configurado o tipo Gauge, a Label `Frequência Inversor`, formato do valor `{{value}}`, Unidade em `Hz` e parametros graficos para o ponteiro e range.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_gauge_profinet.png" width="400">

 ##### Text
 
 No nó text, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE PROFINET do dashboard NEXUS, nele foi configurado a Label `Delay retorno` e o formato do valor `{{msg.payload}}`. esse nó recebe os dados do nó tipo 6, que o envia o valor em ms do cronometro.

 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_text_profinet.png" width="400">
