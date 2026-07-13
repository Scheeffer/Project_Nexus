# Fluxo PROFINET

Antes da explicação dos nós, é necessário compreender o funcionamento da comunicação do Node-RED com o CLP. Ao utilizar qualquer nó da biblioteca `plcindustry`, é possível selecionar e cadastrar o CLP de interesse:

Clique na opção "+":

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_como_cadastrar_disp_profinet.png" width="400">

Insira os dados correspondentes do CLP Siemens:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/st_config_disp.png" width="400">

Cadastre as variáveis de memória do dispositivo para uso no Node-RED:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_cadastro_variavel_profinet.png" width="400">

Com essa etapa concluída, segue a explicação do fluxo PROFINET.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_profinet.png" width="600">
<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/explica%C3%A7%C3%A3o_profinet_2.png" width="600">

Para detalhar este fluxo, cada tipo de nó aplicado será explicado individualmente:

#### Tipo 1 - Leitura de variável S7 - s7 in

Nó para leitura de variáveis do CLP, onde é realizada a seleção do dispositivo de origem, da variável de interesse e das opções de leitura:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_in_profinet.png" width="400">

`REV_INV`

No caso acima, trata-se do retorno do *feedback* da frequência do inversor, enviado pelo CLP como um dado *float* de 32 bits. Além do *feedback* da frequência, este nó emite o comando para interromper o contador do nó Tipo 6.

#### Tipo 2 - Escrita de variável S7 - s7 out

Nó para escrita de variáveis no CLP, onde é realizada a seleção do dispositivo de destino e da variável de interesse:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/s7_out_profinet.png" width="400">

Neste tipo, foram selecionadas as variáveis que recebem dados booleanos (`true` ou `false`). A diferença entre elas reside no endereço de memória:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `START` | `DB4,X0.0` | `bool` | Liga o inversor |
| `STOP` | `DB4,X0.1` | `bool` | Desliga o inversor |
| `RESET_INV` | `DB4,X0.2` | `bool` | Reseta as falhas no inversor de frequência |
| `HABILITA NODE RED` | `DB2,X10.1` | `bool` | Habilita o comando via Node-RED |

O único nó que recebe um valor diferente é o `s7 out` - `SET_FREQ`, que recebe um valor do tipo `real` para atualizar a frequência do inversor:

| Nome | Endereço | Tipo | Uso |
|------|----------|------|-----|
| `ENTRADA_REF_FREQUENCIA` | `DB4,REAL2` | `real` | Seta a frequência |

#### Tipo 3 - Nó Trigger

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_trigger.png" width="400">

Neste tipo, configura-se que, caso o nó receba um dado de ativação, sua saída emitirá um pulso `true` por 1 segundo; após esse tempo, o comando retornará para `false`. Essa configuração deve-se à lógica de funcionamento do CLP Siemens. Como é possível visualizar, os nós *trigger* estão conectados aos nós que recebem dados booleanos, responsáveis por ligar, desligar e resetar o inversor. Caso esses nós recebessem continuamente os dados `true` ou `false`, a lógica do CLP seria afetada, impossibilitando a alteração dos valores das variáveis por outros comandos. Todos os nós *trigger* estão configurados da mesma forma.

#### Tipo 4 - Function 2 - Conversor

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_function_2.png" width="600">

Conforme explicado no Tipo 1, o nó `s7 in` - `REF_INV` retorna um dado *float* de 32 bits. Este nó *function* possui um script para converter esse dado no `msg.payload` a ser exibido no dashboard.

```javascript
msg.payload = Number(msg.payload.toFixed(2));
return msg;
```

#### Tipo 5 - Toggle

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_n%C3%B3_toogle.png" width="600">

Esta função tem como princípio refinar o controle entre o Node-RED e a IHM do CLP. Ela é responsável por modificar uma variável interna do CLP que altera a prioridade de comando entre o CLP e o Node-RED.

```javascript
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

Este tipo, também presente nas demais colunas, é constituído por 2 nós: `Inicia cronometro` e `Para Cronômetro e Mede (ms)`. São nós dependentes. O nó `Inicia cronometro`, contendo uma saída, possui uma variável que, ao ser acionada (neste caso, quando o valor de frequência é alterado pelo dashboard), inicia um contador de tempo. O nó `Para Cronômetro e Mede (ms)` interrompe o contador de tempo, converte o valor para milissegundos (ms) e possui duas saídas: uma para dar seguimento ao fluxo em direção ao nó dashboard de *gauge*, e outra para o nó dashboard de texto.

Nó `Inicia cronometro`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_inicia.png" width="600">

```javascript
// Salva o momento exato em que o comando passou por aqui
flow.set('t_start', Date.now());

// Repassa a mensagem original sem alterações
return msg;
```

Nó `Para Cronômetro e Mede (ms)`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_para_mede.png" width="600">

```javascript
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

> ⚠️ **Atenção:** No código, é possível observar a variável `t_start`. Como essas funções se repetem em outras instâncias do projeto, é necessária a alteração do nome da variável para evitar conflitos e falhas na sincronização dos contadores com seus respectivos nós de início e parada. Para isso, a variável foi diferenciada para `t_start_2` e `t_start_3`. Este dado deve ser alterado tanto no nó `Inicia cronometro` quanto no `Para Cronômetro e Mede (ms)` para manter a sincronização. Observação: esta variável se repete duas vezes no script do nó `Para Cronômetro e Mede (ms)`.

#### Tipo 7 - Exportar dados para o nexus-web

Este tipo trata da exportação de dados para o site nexus-web e possui 4 nós: um `http in` (`GET /api/state`), um `http response` (`http`) e dois nós *function* (`Return current state` e `set PROFINET state`). Os nós HTTP configuram o direcionamento no Node-RED para o site externo.

`GET /api/state`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_in_nexus_web.png" width="300">

`http`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_http_request_nexus_web.png" width="300">

O nó *function* `set PROFINET state` configura o parâmetro da frequência para exportação.

`set PROFINET state`

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_profinet_state_nexus_web.png" width="400">

```javascript
const protocolState = flow.get("protocolState") || {};

protocolState.PROFINET = {
    ...(protocolState.PROFINET || {}),
    online: true,
    frequencia: Number(msg.payload ?? 0)
};

flow.set("protocolState", protocolState);

return msg;
```

O nó *function* `Return current state` compila os dados extraídos das colunas PROFINET, CAN e MQTT, exportando-os para o *endpoint* do nexus-web.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_function_response_nexus_web.png" width="400">

```javascript
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
    "Access-Control-Allow-Origin": "[https://sylphina.com.br/alvaro](https://sylphina.com.br/alvaro)"
};

msg.payload = JSON.stringify(response);

return msg;
```

#### Tipo 8 - Nós de Dashboard

Antes de detalhar os nós de interface, cabe esclarecer a configuração geral do dashboard. Foi criado um dashboard dividido em 3 grupos, representando cada um dos tipos de comunicação e dispostos visualmente como as colunas PROFINET, CAN e MQTT, respectivamente.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_dashboard_groups.png" width="400">

A página do Dashboard é apresentada da seguinte forma:

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/Dashboard.png" width="800">

Retornando aos nós desta seção, são utilizados 7 nós da biblioteca dashboard: 4 *buttons* responsáveis pelo acionamento dos nós *trigger* (Tipo 3) e *toggle* (Tipo 5) para as entradas do CLP, 1 nó *numeric* para atualizar o valor de frequência do CLP, e 2 nós para exibição de dados.

##### Botões (*Button*)

Os nós do tipo botão são configurados de forma simples. No campo `Group`, é definida a coluna na qual o botão será posicionado (neste caso, `REDE PROFINET` do dashboard NEXUS). Em `Icon`, é configurado um indicador gráfico oriundo do repositório de ícones (FontAwesome). O campo `Label` define o nome exibido no dashboard. As opções adicionais não foram configuradas. O botão foi ajustado para, quando acionado, emitir um *payload* booleano `true`. Todos os botões desta seção foram configurados de forma idêntica, alterando-se apenas o nome na `Label`.

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_button_profinet.png" width="400">

Labels presentes na configuração:
| `Liga Inversor` | `Desliga Inversor` | `Reset Inversor` | `Acionamento PROFINET via Node` |

##### Numérico (*Numeric*)

No nó numérico, o campo `Group` também define o posicionamento (coluna `REDE PROFINET`). Foram configurados a `Label` como `Ajuste de frequência`, o formato do valor como `{{value}}`, e os parâmetros do componente, como o limite (*range*) de 0 a 60 e incremento (*step*) de 5. Quando alterado, este nó atualiza o valor da frequência do inversor e também serve como gatilho para o nó contador (Tipo 6).

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_slider_profinet.png" width="400">

##### Medidor (*Gauge*)

No nó *gauge*, o campo `Group` define o posicionamento (coluna `REDE PROFINET`). Foram configurados o tipo *Gauge*, a `Label` como `Frequência Inversor`, o formato do valor como `{{value}}`, a unidade em `Hz`, além dos parâmetros gráficos para o ponteiro e os limites (*range*).

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_gauge_profinet.png" width="400">

##### Texto (*Text*)

No nó de texto, o campo `Group` define o posicionamento (coluna `REDE PROFINET`). Foram configuradas a `Label` como `Delay retorno` e o formato do valor como `{{msg.payload}}`. Este nó recebe os dados do nó Tipo 6, que envia o valor do cronômetro em milissegundos (ms).

<img src="https://github.com/Scheeffer/Project_Nexus/blob/main/backbone/figs/config_text_profinet.png" width="400">
