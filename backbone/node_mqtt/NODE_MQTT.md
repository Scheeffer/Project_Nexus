# Fluxo MQTT

Antes da explicação dos nós, é necessário compreender o funcionamento da comunicação do Node-RED com o servidor MQTT. Ao utilizar qualquer nó MQTT, é possível selecionar e cadastrar o servidor de interesse:

Clique na opção "+":

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/cadastro_mqtt.png" width="400">

Insira os dados respectivos do servidor MQTT:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/mqtt_cadastro_disp.png" width="400">

Sobre os dados do servidor MQTT:
* `192.168.0.105` - Configuração do endereço de IP do servidor MQTT.
* `:1883` - Configuração da porta do servidor MQTT.
* `MQTT V3.1.1` - Protocolo de uso do servidor MQTT.
* Configuração do ID de cliente `Node-red` estabelecida no servidor MQTT.

Com essa etapa concluída, segue a explicação do fluxo MQTT.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_mqtt.png" width="600">
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_mqtt_2.png" width="600">

Para detalhar este fluxo, cada tipo de nó aplicado será explicado individualmente:

## Tipo 1 - Nó mqtt out

Nó para envio de valores ao servidor MQTT, onde é realizada a seleção do servidor de destino e do tópico de escrita:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/mqtt_out_config.png" width="400">

Este fluxo possui 2 nós `mqtt out` que compartilham o mesmo servidor cadastrado. A diferença entre eles está no tópico de destino:

| Tópico | Tipo | Função | 
|------|----|----------------|
| `ESP32S3/COM/Atuadores` | `string` | Solicitar alteração de estado do atuador do sistema. | 
| `ESP32S3/COM/get` | `string` | Solicitar aquisição da temperatura atual do sensor. | 

O tópico `ESP32S3/COM/Atuadores` pode enviar as strings `AQUECIMENTO_ON`, `REFRIGERACAO_ON` e `SYSTEM_OFF`, as quais são tratadas pelo servidor para alterar o estado do atuador.
O tópico `ESP32S3/COM/get` pode enviar a string `GET_TEMP`, que é tratada pelo servidor para solicitar a aquisição da temperatura.

## Tipo 2 - Nó mqtt in

Nó para recebimento de valores do servidor MQTT, onde é realizada a seleção do servidor de origem e do tópico de leitura:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/mqtt_in_config.png" width="400">

Este fluxo possui 2 nós `mqtt in` que compartilham o mesmo servidor cadastrado. A diferença entre eles está no tópico de leitura:

| Tópico | Tipo | Função | 
|------|----|----------------|
| `ESP32S3/COM/Status` | `string` | Atualização do valor de status dos atuadores. | 
| `ESP32S3/COM/temperatura` | `string` | Atualização do valor de temperatura. | 

O tópico `ESP32S3/COM/Status` recebe todas as atualizações tratadas pelo servidor MQTT referentes ao status dos atuadores, que podem ser `Sistema aquecendo`, `Sistema resfriando` e `Sistema desligado`.
O tópico `ESP32S3/COM/temperatura` recebe todas as atualizações de temperatura registradas pelo servidor MQTT.

## Tipo 3 - Nó s7 in

Nó para leitura de variáveis do CLP, onde é realizada a seleção do dispositivo de origem, da variável de interesse e das opções de leitura:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_s7_in_mqtt.png" width="400">

A leitura engloba variáveis do CLP e variáveis da IHM para atuação no servidor MQTT:

| Tópico | Registrador | Tipo | Função | 
|------|----|----------------|---------|
| `Liga_AQ` | `DB7,X0.0` | `bool` | Envia `true` para o nó *function* 4. | 
| `Liga_vent` | `DB7,X0.1` | `bool` | Envia `true` para o nó *function* 3. | 
| `Desligar_vent_AQ` | `DB7,X0.2` | `bool` | Envia `true` para o nó *function* 1. | 

Todos os nós `s7 in` operam como sinalizadores de comando que são processados pelos nós *function* conectados (verificar no Tipo 8).

## Tipo 4 - Nó s7 out

Nó para escrita de variáveis no CLP, onde é realizada a seleção do dispositivo de destino e da variável de interesse:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_s7_out_mqtt.png" width="400">

Neste tipo, foi selecionada a variável `FDK_temp`, que recebe dados do tipo real provenientes do nó *function* `String/Real`:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `FDK_temp` | `DB7,REAL2` | `real` | Registra o valor de temperatura enviado pelo nó *function* `String/Real`. |

## Tipo 5 - Comunicação http

#### Estrutura de leitura de informações

Para que o Node-RED receba os dados do ESP, é necessário configurar 2 nós: `http in` e `http response`.

#### `http in`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_mqtt_in.png" width="400">

O fluxo do MQTT possui 3 pares de recebimento de informações da rede CAN. Conforme ilustrado na imagem acima, todo nó `http in` está conectado a um `http response`; essa ligação é necessária para estabelecer a comunicação. Os nós `http in` estão configurados de modo similar, todos utilizando o método "POST". As diferenças residem na configuração da URL e no rótulo (Label), sendo cada URL responsável por receber um dado especificado pela programação do ESP:

| Label | URL | Dado | 
|------|----------|----------|
| `Recebe Aquecer` | `/mqtt_aquecer` | `string` - Envia sinalizador para o nó *function* 4. |
| `Recebe Resfriar` | `/mqtt_resfriar` | `string` - Envia sinalizador para o nó *function* 3. |
| `Recebe desligar` | `/mqtt_desligar` | `string` - Envia sinalizador para o nó *function* 1. |

#### `http response`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/CAN_http_response.png" width="300">

Os nós `http response` são necessários exclusivamente para fechar o ciclo de comunicação com o ESP. Neles, nenhuma configuração adicional é exigida, mantendo-se o padrão exibido na imagem acima.

#### Estrutura de escrita de informações

##### `http request`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_request_mqtt.png" width="400">

O fluxo MQTT possui um nó `http request` responsável por receber a atualização do status dos atuadores e da temperatura oriundos dos nós `ESP32S3/COM/Status` e `ESP32S3/COM/temperatura`, respectivamente. Em sua configuração, o método utilizado é o "POST". Seguem as demais definições:

| Label | URL | Dado | 
|------|----------|----------|
| `requisição http` | `http://192.168.0.103/set_mqtt_sim` | `string` - Recebe os valores em string referentes à atualização do status dos atuadores e de temperatura enviados pelos nós `mqtt in`. |

Sobre as URLs:
`http://192.168.0.103` - Configuração do caminho HTTP para o endereço de IP no ESP.
`set_mqtt_sim` - Variável interna do código do ESP CAN para atualização dos dados de status dos atuadores e de temperatura enviados pelos nós `mqtt in`.

#### Tipo 6 - Cronômetro de Feedback de envio e retorno de mensagem

Este tipo, também presente nas demais colunas, é constituído de 2 nós: `Inicia cronometro` e `Para Cronômetro e Mede (ms)`. São nós dependentes. O nó `Inicia cronometro`, contendo uma saída, possui uma variável que, ao ser acionada (neste caso, quando o valor de frequência é alterado pelo dashboard), inicia um contador de tempo. O nó `Para Cronômetro e Mede (ms)` interrompe o contador de tempo, converte o valor para milissegundos (ms) e possui duas saídas: uma para dar seguimento ao fluxo em direção ao nó dashboard de texto de temperatura, e outra para o nó dashboard de texto que exibe o valor em ms.

Nó `Inicia cronometro`:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_inicia.png" width="600">

```javascript
// Salva o momento exato em que o comando passou por aqui
flow.set('t_start_3', Date.now());

// Repassa a mensagem original sem alterações
return msg;
```

Nó `Para Cronômetro e Mede (ms)`:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_para_mede.png" width="600">

```javascript
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

> ⚠️ **Atenção:** No código, é possível observar a variável `t_start`. Como essas funções se repetem em outras instâncias do projeto, é necessária a alteração do nome da variável para evitar conflitos e falhas na sincronização dos contadores com seus respectivos nós de início e parada. Para isso, a variável foi diferenciada para `t_start_2` e `t_start_3`. Este dado deve ser alterado tanto no nó `Inicia cronometro` quanto no `Para Cronômetro e Mede (ms)` para manter a sincronização. Observação: esta variável se repete duas vezes no script do nó `Para Cronômetro e Mede (ms)`.

#### Tipo 7 - Exportar dados para nexus-web

Este tipo trata da exportação de dados para o site nexus-web. No escopo deste fluxo, há somente um nó responsável por essa função.

`set MQTT state`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_mqtt_state_nexus_web.png" width="400">

```javascript
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

Este tipo de nó abriga várias funções, excluindo as agregadas aos tipos 6 e 7.

##### `function 1`, `function 3` e `function 4` - Envio de string para `mqtt out`
Quando estes nós recebem sinalização de entrada dos nós `s7 in` ou `http in`, eles retornam `msg.payload = "AQUECIMENTO_ON"`. A mensagem alterna para `AQUECIMENTO_ON` no nó `function 4`, `REFRIGERACAO_ON` no nó `function 3` e `SYSTEM_OFF` no nó `function 1`.

```javascript
// O nó s7 in envia true ou false no msg.payload

if (msg.payload === true) {
    msg.payload = "AQUECIMENTO_ON"; // String para nível lógico alto (1)
} else {
    msg.payload = "AQUECIMENTO_ON";   // String para nível lógico baixo (0)
}

// Opcional: Mudança de tópico para identificar o status
//msg.topic = "status_inversor";

return msg;
```
> ⚠️ A definição `msg.payload = "AQUECIMENTO_ON"`, visível no script do nó `function 4`, é a variável que deve ser alterada para `msg.payload = "REFRIGERACAO_ON"` e `msg.payload = "SYSTEM_OFF"` nos nós `function 3` e `function 1`, respectivamente.

##### `String/Real`
Esta *function* é responsável pela conversão dos dados de temperatura, enviados pelo nó `mqtt in` `ESP32S3/COM/temperatura` no formato `string`, para o formato `real`, encaminhando o resultado para o nó `s7 out` `FDK_temp`.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_mqtt_state_nexus_web.png" width="400">

```javascript
// Supondo que msg.payload venha como "123.45" ou "123,45"
let stringOriginal = msg.payload.toString();

// Remove espaços e substitui vírgula por ponto, se houver
let stringLimpa = stringOriginal.trim().replace(',', '.');

// Converte para número Real (Float)
let numeroReal = parseFloat(stringLimpa);

// Verifica se a conversão foi válida para evitar enviar erros ao CLP
if (!isNaN(numeroReal)) {
    msg.payload = numeroReal;
    msg.topic = "DB7,REAL2"; // Endereço exato da Tag REAL no CLP
    return msg;
} else {
    node.error("Erro na conversão: O valor recebido não é uma String numérica válida. Recebido: " + stringOriginal);
    return null; // Aborta o fluxo para não enviar dados incorretos ao CLP
}
```

#### Tipo 9 - Envio de dados para http request

Este nó *function* é responsável por receber os dados dos nós `mqtt in` `ESP32S3/COM/Status` e `ESP32S3/COM/temperatura` e processá-los para serem direcionados ao nó `http request` da rede CAN.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_envio_http_mqtt.png" width="400">

```javascript
// 1. Inicializa os valores guardados na memória do Node-RED caso ainda estejam vazios
let tempSalva = context.get('tempSalva') || 0.0;
let statusSalvo = context.get('statusSalvo') || "Aguardando";

// 2. Filtra o dado analisando diretamente a mensagem de entrada
let conteudo = msg.payload.toString().trim();

// Se o conteúdo for um número (ou começar com um número), é referente à temperatura
if (!isNaN(parseFloat(conteudo)) && !conteudo.includes("aquecer") && !conteudo.includes("refri")) {
    tempSalva = parseFloat(conteudo);
    context.set('tempSalva', tempSalva); // Salva na memória
} 
// Caso contrário, é o texto do status (ex: "Aquecendo", "Resfriando", "Aguardando")
else {
    statusSalvo = conteudo;
    context.set('statusSalvo', statusSalvo); // Salva na memória
}

// 3. Monta o objeto JSON unificado esperado pelo ESP32
msg.payload = {
    temp: tempSalva,
    status: statusSalvo
};

return msg;
```

#### Tipo 10 - Dashboard

Antes de detalhar os nós de interface, cabe esclarecer a configuração geral do dashboard. Foi criado um dashboard dividido em 3 grupos, representando cada um dos tipos de comunicação e dispostos visualmente como as colunas Profinet, CAN e MQTT, respectivamente.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_dashboard_groups.png" width="400">

A página do Dashboard é apresentada da seguinte forma:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Dashboard.png" width="800">

Retornando aos nós deste fluxo, são utilizados 8 nós da biblioteca dashboard: 4 *buttons*, 3 *texts* e 1 *chart*.

##### Botões (*Button*)

Os nós do tipo botão são configurados de forma simples. No campo `Group`, é definida a coluna na qual o botão será posicionado (neste caso, `REDE MQTT` do dashboard NEXUS). Em `Icon`, é configurado um indicador gráfico oriundo do repositório de ícones (FontAwesome). O campo `Label` define o nome exibido no dashboard. Quando acionados, estes botões emitem um *payload* do tipo string contendo as seguintes variáveis de acionamento:

| Botão Dashboard | msg.payload |
|----------------|--------------|
| `Aquecimento` | `AQUECIMENTO_ON` | 
| `Refrigeração` | `REFRIGERACAO_ON` |
| `Desligar` | `SYSTEM_OFF` |
| `Leitura temperatura` | `GET_TEMP` |

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_button_mqtt.png" width="400">

O botão `Leitura temperatura` serve como gatilho para o início do cronômetro da *function* descrita no Tipo 6 (`Inicia cronometro`).

##### Texto (*Text*)

Nos nós de texto, o campo `Group` também define o posicionamento (coluna `REDE MQTT`). O fluxo possui 3 nós de texto configurados para exibir diferentes variáveis:

| Label | Formato do Valor | Origem do Dado |
|----------------|--------------|----------|
| `temperatura` | `{{msg.payload}}` | Dado de temperatura encaminhado pelo nó `mqtt in` `ESP32S3/COM/temperatura`. |
| `Delay` | `{{msg.payload}}` | Dado de tempo (em ms) encaminhado pelo nó *function* `Para Cronômetro e Mede (ms)`. |
| `text` | `{{msg.payload}}` | Dado de status dos atuadores encaminhado pelo nó `mqtt in` `ESP32S3/COM/Status`. |

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_text_mqtt.png" width="400">

##### Gráfico (*Chart*)

O nó gráfico foi configurado para receber os dados do nó `mqtt in` `ESP32S3/COM/temperatura`, exibindo os valores visualmente com histórico de amostragem.

| Label | Origem do Dado |
|----------------|--------------|
| `chart` | Dado de temperatura encaminhado pelo nó `mqtt in` `ESP32S3/COM/temperatura`. |

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_chart_mqtt.png" width="400">

#### Tipo 11 - Inject

O nó `Inject` foi inserido a fim de testar a comunicação de modo manual com o servidor MQTT. Este fluxo possui 4 nós `Inject`, cada um com sua `Label` correspondente à mensagem enviada. A diferença entre eles reside no parâmetro `msg.payload`, que envia as strings `AQUECIMENTO_ON`, `REFRIGERACAO_ON` e `SYSTEM_OFF` (referentes às labels Aquecimento, Refrigeração e Desligar, respectivamente).

<img width="457" height="561" alt="image" src="https://github.com/user-attachments/assets/e74cf6d9-3704-4f4a-acbb-67d569085abd" />

O 4º nó `Inject`, denominado `GET`, apresenta um comportamento diferente: além de enviar o `msg.payload` configurado como `get`, ele está ajustado para enviar a mensagem repetidamente em intervalos de 1 segundo, recebendo, assim, a atualização da temperatura a cada segundo.

<img width="457" height="554" alt="image" src="https://github.com/user-attachments/assets/b41ab72a-d09c-4131-9b94-27398bf6510a" />
