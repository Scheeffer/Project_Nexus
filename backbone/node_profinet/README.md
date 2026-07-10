# Fluxo Profinet
Antes de explicar os nós vamos explicar como a comunicação do node-red com o CLP funciona, ao utilizar qualquer balão da biblioteca plcindustry é possivel selecionar e cadastrar o CLP de interesse:

Clique na opção "+":

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_como_cadastrar_disp_profinet.png" width="400">

Insira os dados respectivos do seu CLP siemens:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/st_config_disp.png" width="400">

Cadastre as variáveis de memória para uso no node-red do seu dispositivo:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_cadastro_variavel_profinet.png" width="400">

Com isso ja explicado vamos para nosso Fluxo Profinet

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_profinet.png" width="600">
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_profinet_2.png" width="600">

Para explicar esse nó vamos generalizar e explicar cada tipo de nó aplicado:

#### Tipo 1 - Leitura de variável S7 - S7 in
Nó para leitura de variáveis do CLP, realizado seleção do dispositivo de origem, variável de interesse e opções de leitura:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_in_profinet.png" width="400">

`REV_INV`

No caso acima é o retorno do feedback da frequencia do inversor devolvida pelo CLP como um dado Float de 32-bits. Além do feeback da frequência do Inversor, esse nó da comando para parar o contador do nó tipo 6.

#### Tipo 2 - Escrita de variável S7 - S7 out
Nó para escrita de variáveis do CLP, realizado seleção do dispositivo de origem, variável de interesse:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_out_profinet.png" width="400">

Nesse tipo foram selecionados as variáveis que recebem dados boleanos (true ou false), o que muda entre eles é o endereço de memória:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `START` | `DB4.DBX0.0` | bool | Liga o inversor |
| `STOP` | `DB4.DBX0.1` | bool | Desliga o inversor |
| `RESET_INV` | `DB4,x0.2` | bool | Reseta as falhas no inversor de frequência |
| `HABILITA NODE RED` | `DB2,X10.1` | bool | Habilita o comando via node red |

Unico que recebe um valor diferente é o S7 out - SET_FREQ, que recebe um valor real para atualizar o valor de frequência do Inversor:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `ENTRADA_REF_FREQUENCIA` | `DB4,REAL2` | real | Seta frequência |

#### Tipo 3 - Nó Trigger

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_trigger.png" width="400">

Nesse tipo é configurado que caso esse nó receba um dado de ativação, em sua saída terá um pulso de saída "true" por 1 segundo, após esse tempo será um comando "false", foi configuraado dessa forma devido a lógica de funcionamento do CLP Siemens, como é possivel visualizar os nós trigger estão conectados com os nós que recebem dados boleanos, responsavel por ligar, desligar e resetar o inversor, caso esses nós recebessem continuamente os dados true ou false, isso afearia a lógica do CLP, não sendo possivel alterar os valores das variáveis por outros comandos.
Todos os nós trigger estão configurados da mesma forma.

#### Tipo 4 - Function 2 - Conversor

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_function_2.png" width="600">

Como explicado no tipo 1, o dado do S7 in - REF_INV retorna um dado Float de 32-bits, esse nó function possui um script para converter esse dado para msg.payload para ser exibido no dashboard.
```
msg.payload = Number(msg.payload.toFixed(2));
return msg;
```

#### Tipo 5 - Toogle

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_toogle.png" width="600">

Essa função tem como principio refinar o controle do node-rede e a IHM do CLP, ele é resposavel por alterar uma variável interna do CLP que altera a prioridade do comando entre o CLP e o Node red
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
Esse tipo, também presente nas demais colunas, é constituido de 2 nós o `Inicia cronometro` e `Para Cronômetro e Mede (ms)`. São dois nós dependentes, o nó `Inicia cronometro`,com uma saída, possui uma variável que ao ser acionada incia um contador de tempo, nessa caso, quando o valor de frequência é alterado pelo dashboard, o nó `Para Cronômetro e Mede (ms)` para o contador de tempo e o converte para ms, possuí duas saída, uma para o codigo seguir seu caminho para o nó dashboard de gauge e outra para o nó dashbaord de texto.

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

> ⚠️ **No código é possivel verificar a variável 't_start', como essas funções se repetem no código, é necessária alteração para não gerar conflito e falhar a sincronização dos contadores com seus respectivos inica e para, para isso foi diferenciado essa variável para 't_start_2' e 't_start_3', esse dado deve ser alterado tanto no `Inicia cronometro` quanto `Para Cronômetro e Mede (ms)` para ficar sincronizado. obs: essa variável repete duas vezes no `Para Cronômetro e Mede (ms)`.

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
