

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

