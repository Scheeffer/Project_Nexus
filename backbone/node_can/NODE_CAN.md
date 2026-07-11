# Fluxo CAN

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_CAN.png" width="600">
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_CAN_2.png" width="600">


#### Tipo 1 - Comunicação http

Conforme detalhado na seção da rede CAN, a comunicação CAN ocorre apenas entre os ESPs e o módulo do velocímetro. A comunicação com o Gateway é realizada via HTTP. Esse tipo de nó estabelece o recebimento e o envio de informações entre o Gateway e o ESP de comunicação.

#### Estrutura de leitura de informações

Para o node receber os dados do ESP é necessário configurar 2 nós, `http in` e ` http response`:

`http in`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/CAN_http_in.png" width="400">

O fluxo do CAN possui 4 pares de recebimento de informações da rede-can, como nas imagens acima, todo `http in`  está conectado em um `http response`, essa ligação é necessária para estabelecer comunicação. Os nós `http in` estão todos configurados de modo similar, todos com método "POST", suas diferenças estão na configuração das URL e na Label, cada URL é responsável por receber um dado especificado pela programação do ESP:

| Label | URL |Dado| 
|------|----------|----------|
| `http_in` | `/can` | string - Feedback do velocímetro e tensão do potenciômetro|
| `Ligar CLP via ESP - CAN` | `/ligar` |bool|
| `Desligar CLP via ESP - CAN` | `/desligar` |bool|
| `[post]/slider` | `/slider` |string - Feedback para frequência do inversor|
> ⚠️ O http_in ,ao ser atualizado, para o temporizador do nó do tipo 6.  
` http response`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/CAN_http_response.png" width="300">

Os nós ` http response`, são necessários somente para completar o fluxo de comunicação com o ESP, neles não são configurados nada, todos estão como na imagem acima.

#### Estrutura de escrita de informações

` http request`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/CAN_http_request.png" width="400">

O fluxo CAN possui dois nós nessa configuração, são responsáveis pelo envio de atualização de dados para o ESP. Em sua configuração o Método é POST, segue abaixo suas diferenças:

| Label | URL |Dado| 
|------|----------|----------|
| `Envia para o ESP32` | `http://192.168.0.103/set_nodered_value` | string - Atualiza valor do velocímetro|
| `requisição http` | `http://192.168.0.103/set_nodered_freq` | string Atualiza valor da frequência do inversor para o ESP|

Sobre os URL:
`http://192.168.0.103` - é a configuração do caminho http para o endereço de ip no ESP.
`set_nodered_value` e `set_nodered_freq`, variáveis internas do código do ESP para atualização dos dados de velocidade do velocímetro e feedback da frequência do inversor, respectivamente.

#### Tipo 2 -  S7 in

O Fluxo CAN possui dois nós para leitura de variáveis do CLP, em sua configuração foi realizada seleção do dispositivo de origem e a variável de interesse e opções de leitura:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_in_profinet.png" width="400">

variáveis selecionadas:

`SET_VEL` - Valor de interesse configurado na IHM do CLP para atualizar dado do Velocímetro

`REV_INV` -  Feedback da Frequência do Inversor

#### Tipo 3 - Escrita de variável S7 - S7 out
Nó para escrita de variáveis do CLP, realizada seleção do dispositivo de origem, variável de interesse:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_out_profinet.png" width="400">

Nesse tipo foram selecionados as variáveis que recebem dados booleanos (true ou false) e valores reais, o que muda entre eles é o endereço de memória:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `START` | `DB4.DBX0.0` | bool | Liga o inversor |
| `STOP` | `DB4.DBX0.1` | bool | Desliga o inversor |
| `SET_FREQ` | `DB4,REAL2` | real | Atualiza valor de Frequência do inversor |
| `FBK_VEL` | `DB6,REAL4` | real | Feedback para o CLP da velocidade do velocímetro |

#### Tipo 4 - Nó Trigger

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_trigger.png" width="400">

Nesse tipo é configurado que caso esse nó receba um dado de ativação, em sua saída terá um pulso de saída "true" por 1 segundo, após esse tempo será um comando "false", foi configurado dessa forma devido a lógica de funcionamento do CLP Siemens, como é possível visualizar os nós trigger estão conectados com os nós que recebem dados booleanos, responsáveis por ligar, desligar e resetar o inversor, caso esses nós recebessem continuamente os dados true ou false, isso afetaria a lógica do CLP, não sendo Possível alterar os valores das variáveis por outros comandos.
Todos os nós trigger estão configurados da mesma forma.

#### Tipo 5 - Nó Change

Os nós changes são utilizados no fluxo CAN para tratar dados entre os equipamentos , é Possível visualizar que os nós changes estão sempre localizados entre nós de comunicação do http e o nós s7.

Nos blocos  `Ligar Inversor CLP pelo CAN` e `Desligar Inversor CLP pelo CAN´ - há os nós change para a variavel booleana, que altera a string "true" para o dado booleano "true" que será tratado pelos nós S7 out de ligar e desligar o inversor

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_change_bool.png" width="400">

No bloco `Ajusta a Freq do Inversor CLP a partir do ESP` - há o nó change que altera o valor string para real, para ser tratado pelo nó S7 out responsável por alterar a frequência do inversor. 

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_change_number.png" width="400">

Nos blocos `SET Velocidade CAN` e `Feedback Frequência Inversor CLP` - há os nós change que alteram o valor real para string, para ser tratado pelo nó http request que tratam a atualização do valor do velocímetro alterado no CLP  e o feedback da frequência do inversor para o ESP.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_change_string.png" width="400">

No bloco `Leitura variáveis ESP CAN`, há os nós change que filtram de velocidade e tensão dos dados enviados pelo http in.
Os dados filtrados são direcionados para seus destinos de exibição e atualização.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_change_msg_tensao.png" width="400">

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_change_msg_velocidade.png" width="400">

A variável do payload.velocidade e payload.tensao são os filtros de dados.

#### Tipo 6 - Cronômetro de Feedback de envio e retorno de mensagem
Esse tipo, também presente nas demais colunas, é constituído de 2 nós o `Inicia cronômetro` e `Para Cronômetro e Mede (ms)`. São dois nós dependentes, o nó `Inicia cronômetro`,com uma saída, possui uma variável que ao ser acionada inicia um contador de tempo, nessa caso, quando o valor de frequência é alterado pelo dashboard, o nó `Para Cronômetro e Mede (ms)` para o contador de tempo e o converte para ms, possui duas saídas, uma para o código seguir seu caminho para o nó dashboard de gauge e outra para o nó dashboard de texto.

Nó `Inicia cronômetro`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_inicia.png" width="600">

```
// Salva o momento exato em que o comando passou por aqui
flow.set('t_start_2', Date.now());

// Repassa a mensagem original sem alterações
return msg;
```

Nó `Para Cronômetro e Mede (ms)`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_para_mede.png" width="600">

```
// Recupera o tempo salvo na memória
let start = flow.get('t_start_2');

if (start) {
    // Calcula a diferença entre o agora e o tempo inicial
    let tempo = Date.now() - start;
    
    // Limpa a memória para a próxima medição
    flow.set('t_start_2', null); 
    
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

> ⚠️ **No código é Possível verificar a variável 't_start', como essas funções se repetem no código, é necessária alteração para não gerar conflito e falhar a sincronização dos contadores com seus respectivos inica e para, para isso foi diferenciado essa variável para 't_start_2' e 't_start_3', esse dado deve ser alterado tanto no `Inicia cronômetro` quanto `Para Cronômetro e Mede (ms)` para ficar sincronizado. obs: essa variável repete duas vezes no `Para Cronômetro e Mede (ms)`.

#### Tipo 7 - Exportar dados para nexus-web

Esse tipo se trata da exportação de dados para o site nexus-web, nesse fluxo possui somente um nó, `set CAN state`.

`set CAN state`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_CAN_state_nexus_web.png" width="400">

```
let body = msg.payload;

if (typeof body === "string") {
    try {
        body = JSON.parse(body);
    } catch (err) {
        node.warn("Invalid CAN JSON");
        return msg;
    }
}

const can = body.adc && typeof body.adc === "object"
    ? body.adc
    : body;

const protocolState = flow.get("protocolState") || {};

protocolState.CAN = {
    online: true,
    velocidade: Number(can.velocidade ?? can.velocity ?? protocolState.CAN?.velocidade ?? 0),
    marcha:     Number(can.marcha ?? can.gear ?? protocolState.CAN?.marcha ?? 0),
    erro:       Number(can.erro ?? can.error ?? protocolState.CAN?.erro ?? 0)
};

flow.set("protocolState", protocolState);

return msg;
```
#### Tipo 8 -  Nós de Dashboard

Antes de explicar os nós de dashboard, para esclarecer a configuração do dashboard, neste caso, foi realizado a criação de um dashboard com 3 grupos, cada um desses grupos sendo um tipo de comunicação e na exibição estão expostos da mesma forma, respectivamente Profinet, CAN e MQTT.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_dashboard_groups.png" width="400">

 A página do Dashboard fica dessa forma:
 
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Dashboard.png" width="800">

Voltando para os nós desse fluxo, da biblioteca dashboard há 4 nós, um Gauge, 2 text e 1 slider. 

##### Gauge

No nó Gauge, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE CAN do dashboard NEXUS, nele foi configurado o tipo Gauge, a Label `Velocidade`, Unidade em `Km/h` e parâmetros gráficos para o ponteiro e range, sua função é espelhar os dados do velocímetro CAN.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_gauge_CAN.png" width="400">

 ##### Text
 
Nos nós text, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE CAN do dashboard NEXUS, foram configurados a Label `Tensão(Volts):` e `Delay retorno` e ambas com formato do valor `{{msg.payload}}`. Esses nós recebem respectivamente o valor de tensão enviado http in, filtrado pelo nó change e o valor de tempo do nó do tipo 6.

 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_text_CAN.png" width="400">

 ##### Slider
 
Nos nó Slider, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE CAN do dashboard NEXUS, foram configurados a Label `SET Velocidade ` e configurações do slider, como o range de 0 a 100 e passo de 1. A atualização do valor do slider inicia o temporizador do nó de tipo 6 que está em série com esse nó

 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_slider_CAN.png" width="400">

#### Tipo 9 -  Nó Json

Esse nó é responsável por converte entre uma cadeia de caracteres JSON e sua representação de objeto JavaScript, em qualquer direção. Sua função é auxiliar a interpretação do código enviado pelo nó http in.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_text_CAN.png" width="400">

 
