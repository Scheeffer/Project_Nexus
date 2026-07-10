# Fluxo mqtt
Antes de explicar os nós vamos explicar como a comunicação do node-red com o servidor de comunicação mqtt funciona, ao utilizar qualquer balão da mqtt é possivel selecionar e cadastrar o o servidor de interesse:

Clique na opção "+":

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/cadastro_mqtt.png" width="400">

Insira os dados respectivos do seu servidor mqtt:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/mqtt_cadastro_disp.png" width="400">

Sobre os dados do servidor mqtt:
`192.168.0.105` - é a configuração do caminho de endereço de ip do servidor mqtt.
`:1883` - é a configuração da porta do servidor mqtt.
`MQTT V3.1.1` - protocolo de uso do servidor mqtt
 E  configuração do ID cliente `Node-red` configurado no servidor mqtt.

Com isso ja explicado vamos para nosso Fluxo mqtt

 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_mqtt.png" width="600">
 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_mqtt_2.png" width="600">

 Para explicar esse fluxo vamos generalizar e explicar cada tipo de nó aplicado:
 
## Tipo 1 - Nó mqtt out

Nó para envio de valores do servidor mqtt, realizado seleção do servidor de destino e o tópico de leitura:
 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/mqtt_out_config.png" width="400">

Nesse fluxo possuimos 2 nós `mqtt out`, que possuem o mesmo servidor cadastrado, o que muda é seu topico de destino:

| Tópico | Tipo | Função | 
|------|----|----------------|
| `ESP32S3/COM/Atuadores` | `string` |Solicitar Alteração do atuador do sistema| 
| `ESP32S3/COM/get` | `string` | Solicitar aquisição de temperatura atual do sensor de temperatura | 

O Tópico `ESP32S3/COM/Atuadores` pode enviar os tipos de string `AQUECIMENTO_ON`,`REFRIGERACAO_ON`,`SYSTEM_OFF` que são tratados pelo servidor para alterar estado do atuador.
O Tópico `ESP32S3/COM/get` pode enviar os tipos de string `GET_TEMP` que é tratado pelo servidor para solicitar aquisição da temperatura.

## Tipo 2 - Nó mqtt in

Nó para recebimento de valores do servidor mqtt, realizado seleção do servidor de destino e o tópico de leitura:
 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/mqtt_in_config.png" width="400">

 Nesse fluxo possuimos 2 nós `mqtt out`, que possuem o mesmo servidor cadastrado, o que muda é seu topico de leitura:

| Tópico | Tipo | Função | 
|------|----|----------------|
| `ESP32S3/COM/Status` | `string` | Atualização do valor de status dos atuadores | 
| `ESP32S3/COM/temperatura` | `string` |Atualização do valor de temperatura| 

O Tópico `ESP32S3/COM/Status` recebe toda atualização tratada pelo servidor mqtt do status dos atuadores que podem ser `Sistema aquecendo`,`Sistema resfriando` e `Sistema desligado` .
O Tópico `ESP32S3/COM/temperatura` recebe toda atualização de temperatura registrada pelo servidor mqtt.

## Tipo 3 - Nó s7 in

Nó para leitura de variáveis do CLP, realizado seleção do dispositivo de origem, variável de interesse e opções de leitura:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_s7_in_mqtt.png" width="400">

A leitura de variavies do CLP, variaveis da IHM para atuação no servidor mqtt

| Tópico | Registrador| Tipo | Função | 
|------|----|----------------|---------|
|`Liga_AQ`|`DB7,X0.0`|bool| Envia true para nó funciont 4 | 
|`Liga_vent` |`DB7,X0.1`|bool|Envia true para nó funciont 3| 
|`Desligar_vent_AQ`|`DB7,X0.2`|bool|Envia true para nó funciont 1| 

Todos os nós s7 in, são sinalizadores de comando que são processados pelos nós functions conectados, verificar no tipo 8.

## Tipo 4 - Nó s7 out

Nó para escrita de variáveis do CLP, realizado seleção do dispositivo de origem, variável de interesse:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_s7_out_mqtt.png" width="400">

Nesse tipo foi selecionado a variável `FDK_temp` que recebem dados real, do nó function `String/Real`:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `FDK_temp` | `DB7,REAL2` | real | registra valor de temperatura enviado pelo no function `String/Real`|

## Tipo 5 - Comunicação http

#### Estrutura de leitura de informações

Para o node receber os dados do esp é necessário configurar 2 nós, `http in` e ` http response`:

####`http in`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_mqtt_in.png" width="400">

O fluxo do mqtt possui 3 pares de recebimento de informações da rede-can, como na imagem acima, todo `http in`  esta conectado em um `http response`, essa ligação é necessária para estabelecer comunicação. Os nós `http in` estão todos configurados de modo similar, todos com método "POST", suas diferenças estão na configuração do URL e na Label, cada URL é responsavel por receber um dado especificado pela programação do esp:

| Label | URL |Dado| 
|------|----------|----------|
| `Recebe Aquecer` | `/mqtt_aquecer` | string - Envia sinalizador para nó function 4|
| `Recebe Resfriar` | `/mqtt_resfriar` |string - Envia sinalizador para nó function 3|
| `Recebe desligar` | `/mqtt_desligar` |string - Envia sinalizador para nó function 1 |

####` http response`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/CAN_http_response.png" width="300">

Os nós ` http response`, são necessários somente para completar o fluxo de comunicação com o esp, neles não são configurados nada, todos estão como na imagem a cima.

#### Estrutura de escrita de informações

#####` http request`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_request_mqtt.png" width="400">

O fluxo mqtt possui um nó `http request` , que é responsavel por receber a atualização dos status dos atuadores e temperatura dos nós `ESP32S3/COM/Status` e `ESP32S3/COM/temperatura`, respectivamente . Em sua configuração o Método é POST, segue abaixo demais configurações:
| Label | URL |Dado| 
|------|----------|----------|
| `requisição http` | `http://192.168.0.103/set_mqtt_sim` | string - recebe os valores em string da atualização do status dos atuadores e atualização de temperatura enviados pelos mqtt in|

Sobre os URL:
`http://192.168.0.103` - é a configuração do caminho http para o endereço de ip no ESP.
`set_mqtt_sim`, variável interna do código do ESP CAN para atualização dos dados dos status dos atuadores e atualização de temperatura enviados pelos mqtt in.

#### Tipo 6 - Cronômetro de Feedback de envio e retorno de mensagem
Esse tipo, também presente nas demais colunas, é constituido de 2 nós o `Inicia cronometro` e `Para Cronômetro e Mede (ms)`. São dois nós dependentes, o nó `Inicia cronometro`,com uma saída, possui uma variável que ao ser acionada inicia um contador de tempo, nessa caso, quando o valor de frequência é alterado pelo dashboard, o nó `Para Cronômetro e Mede (ms)` para o contador de tempo e o converte para ms, possuí duas saída, uma para o código seguir seu caminho para o nó dashboard de texto de temperatura e outra para o nó dashbaord de texto para valor em ms.

Nó `Inicia cronometro`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_inicia.png" width="600">

```
// Salva o momento exato em que o comando passou por aqui
flow.set('t_start_3', Date.now());

// Repassa a mensagem original sem alterações
return msg;
```

Nó `Para Cronômetro e Mede (ms)`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_para_mede.png" width="600">

```
// Recupera o tempo salvo na memória
let start = flow.get('t_start_3');

if (start) {
    // Calcula a diferença entre o agora e o tempo inicial
    let tempo = Date.now() - start;
    
    // Limpa a memória para a próxima medição
    flow.set('t_start_3', null); 
    
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

> ⚠️ **No código é possivel verificar a variável 't_start', como essas funções se repetem no código, é necessária alteração para não gerar conflito e falhar a sincronização dos contadores com seus respectivos inica e para, para isso foi diferenciado essa variável para 't_start_2' e 't_start_3', esse dado deve ser alterado tanto no `Inicia cronometro` quanto `Para Cronômetro e Mede (ms)` para ficar sincronizado. obs: essa variável repete duas vezes no `Para Cronômetro e Mede (ms)`.

#### Tipo 7 - Exportar dados para nexus-web

Esse tipo se trata da exportação de dados para o site nexus-web, nesse fluxo possui somente um nó, `set CAN state`.

`set MQTT state`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_mqtt_state_nexus_web.png" width="400">

```
const protocolState = flow.get("protocolState") || {};

protocolState.MQTT = {
    ...(protocolState.MQTT || {}),
    online: true,
    status: String(msg.payload)
};

flow.set("protocolState", protocolState);
return msg;
```
#### Tipo 8 - Nós function
Esse tipo de nó possui varias funcões, tirando as funções agregadas aos tipos 6 e 7.

##### `function 1` `function 3`  `function 4` - Function para envio de string para `mqtt out`
Esses nós quando recebem sinalização de entrada dos nós `s7 in` e `http in` eles retornam as `msg.payload = "AQUECIMENTO_ON"` sendo que a mensagem alterna de `AQUECIMENTO_ON` no `function 4` ,`REFRIGERACAO_ON` no `function 3` ,`SYSTEM_OFF` e `function 1`.

```
// O nó s7 in envia true ou false no msg.payload

if (msg.payload === true) {
    msg.payload = "AQUECIMENTO_ON"; // Sua string para nível lógico alto (1)
} else {
    msg.payload = "AQUECIMENTO_ON";   // Sua string para nível lógico baixo (0)
}

// Opcional: Você pode mudar o tópico para identificar o status
//msg.topic = "status_inversor";

return msg;
```
> ⚠️ As variáveis `msg.payload = "AQUECIMENTO_ON"` visivel no scrip da `function 4` é a variavel que se altera para `msg.payload = "REFRIGERACAO_ON"` e `msg.payload = "SYSTEM_OFF"` nos nós `function 3` `function 1`, respectivamente.

##### `String/Real`
Essa function é responsavel pela conversão dos dados de temperatatura enviados pelo nó mqtt in `ESP32S3/COM/temperatura` no formato string e encaminhar para o nó s7 out `FDK_temp`.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_mqtt_state_nexus_web.png" width="400">

```
// Supondo que msg.payload venha como "123.45" ou "123,45"
let stringOriginal = msg.payload.toString();

// Remove espaços e substitui vírgula por ponto, se houver
let stringLimpa = stringOriginal.trim().replace(',', '.');

// Converte para número Real (Float)
let numeroReal = parseFloat(stringLimpa);

// Verifica se a conversão foi válida para evitar enviar erros ao CLP
if (!isNaN(numeroReal)) {
    msg.payload = numeroReal;
    msg.topic = "DB7,REAL2"; // Substitua pelo endereço exato da sua Tag REAL no CLP
    return msg;
} else {
    node.error("Erro na conversão: O valor recebido não é uma String numérica válida. Recebido: " + stringOriginal);
    return null; // Aborta o fluxo para não enviar lixo ao CLP
}
```
#### Tipo 9 - Envio de dados para http request

Esse nó function é responsável por receber os dados dos mqtt in `ESP32S3/COM/Status` e `ESP32S3/COM/temperatura` e processa-los para serem direcionados para o nó `http request´ da rede CAN

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_envio_http_mqtt.png" width="400">

```
// 1. Inicializa os valores guardados na memória do Node-RED caso ainda estejam vazios
let tempSalva = context.get('tempSalva') || 0.0;
let statusSalvo = context.get('statusSalvo') || "Aguardando";

// 2. Filtra o dado analisando diretamente a mensagem que entrou
let conteudo = msg.payload.toString().trim();

// Se o conteúdo for um número (ou começar com um número), veio da temperatura
if (!isNaN(parseFloat(conteudo)) && !conteudo.includes("aquecer") && !conteudo.includes("refri")) {
    tempSalva = parseFloat(conteudo);
    context.set('tempSalva', tempSalva); // Salva na memória
} 
// Caso contrário, é o texto do status (ex: "Aquecendo", "Resfriando", "Aguardando")
else {
    statusSalvo = conteudo;
    context.set('statusSalvo', statusSalvo); // Salva na memória
}

// 3. Monta o JSON unificado perfeito que o seu ESP32 espera receber
msg.payload = {
    temp: tempSalva,
    status: statusSalvo
};

return msg;
```
#### Tipo 10 - Dashboard
Antes de explicar os nós de dashboard, vamos esclarecer a configuração do dashboard, no nosso caso, foi realizado a criação de um dashbaord com 3 grupos, cada um desses grupos sendo um tipo de comunicação e na exibição estão expostos da mesma forma, espectivamente Profinet, CAN e MQTT.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_dashboard_groups.png" width="400">

 A pagina do Dashboard fica dessa forma:
 
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Dashboard.png" width="800">

Voltando para os nós desse fluxo, da biblioteca dashboard há 8 nós, 4 botões, 3 text e 1 chart
.
##### Botões

Nos Nós botões são configurados de forma simples, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE MQTT do dashboard NEXUS, em icon e configurado um indicador gráfico retirado do site https://fontawesome.com/v4/icon/arrow-right, Label é Nome exibido no dashboard, opções opcionais não foram configuradas, e foi configuradp para quando acionado emitir um payload string que retornam as variávies de acionamento `AQUECIMENTO_ON` no botão `Aquecimento` ,`REFRIGERACAO_ON` no botão `Refrigeração` ,`SYSTEM_OFF` no botão `Desligar` e `GET_TEMP` no botão `Leitura temperatura`

|Botão Dashboard | msg.payload |
|----------------|--------------|
| `Aquecimento` | `AQUECIMENTO_ON` | 
|`Refrigeração` | `REFRIGERACAO_ON` |
|`Desligar` | `SYSTEM_OFF` |
|`Leitura temperatura` | `GET_TEMP` |

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_button_mqtt.png" width="800">

##### Text

Nos nós text, em group é diferenciado a coluna que ficará posicionado, nesse caso REDE MQTT do dashboard NEXUS, esse fluxo possui 3 nós text que recebem tem variaveis para exibição diferentes.

|Label |tipo Valor | Origem do dado |
|----------------|--------------|----------|
| `temperatura` |{{msg.payload}} |Dado de temperatura encaminhado pelo nó mqtt in `ESP32S3/COM/temperatura`|
| `Delay` |{{msg.payload}}| Dado de valor de tempo ms encaminhado pelo nó function `Para Cronômetro e Mede (ms)` |
| `text` | {{msg.payload}} | Dado de Status dos atuadores encaminhado pelo nó mqtt in `ESP32S3/COM/Status`|

 <img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_text_mqtt.png" width="400">
 
##### Chart
O nó chart foi configurado para receber os dados do nó mqtt in ESP32S3/COM/temperatura e exibe os dados em gráfico com histórico de amostragem

|Label | Origem do dado |
|----------------|--------------|----------|
| `chart` |Dado de temperatura encaminhado pelo nó mqtt in `ESP32S3/COM/temperatura`|

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_chart_mqtt.png" width="400">


 

