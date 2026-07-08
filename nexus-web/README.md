# 🟥 Rede CAN — Célula 2 (Alexandre & Alvaro)

[![Protocolo](https://img.shields.io/badge/protocolo-CAN%202.0A-red.svg)](https://www.iso.org/standard/63648.html)
[![Controlador](https://img.shields.io/badge/Gateway-ESP32%20%2F%20MCP2515-orange.svg)](#)

---

## 1. Descrição do projeto

Para disponibilizar os resultados obtidos foi construído um site capaz de requisitar os dados para a porta de onde o Node-RED está trabalhando, salvá-los em um banco de dados e disponibilizá-los para qualquer indivíduo que acesse o domínio online, seja pelo computador ou pelo celular.

o website possui duas entradas, a geral e a local, a geral tem a função de disponibilizar os dados para o público geral, esses dados são requisitado em javascript em método post para um arquivo backend em php que rodará no servidor, onde está localizado o banco de dados,

<p align="center"> <img src="figs/Fluxograma.png" alt="diagrama" width="500"></p>
<p align="center"><b>Fluxograma do website</b></p>
<br><br>

O grande objetivo desta célula é ler de maneira contínua os dados de um sensor analógico (potenciômetro) mapeado sob o identificador exclusivo CAN `, processar os pacotes para o cálculo de velocidade real em km/h e comandar um painel atuador de indicadores (Painel E620) via ID CAN `0x4D2`. O Gateway ESP32 também atua como **ponte** para o backbone (Node-RED) por meio de requisições assíncronas **HTTP (POST/GET)** em formato de texto puro (`text/plain`) e JSON. A grande vantagem desse design é garantir a operação offline e robusta da rede de campo CAN, enquanto permite a convergência com o sistema supervisório centralizado.

### Variáveis Disponíveis ao Node-RED / Servidor HTTP

| Nome / Recurso | Rota / Endpoint | Método HTTP | Tipo no Node-RED | Uso / Formato de Origem |
| :--- | :--- | :---: | :--- | :--- |
| `g_valor_can_bruto` | `/data` | **GET** | `Number` (via JSON) | Valor decimal bruto consolidado do sistema (origem bytes [0] e [1] do frame `0x4D2`). |
| `g_velocidade` | `/data` | **GET** | `Number` (via JSON) | Velocidade calculada e escalonada em km/h (`g_valor_can_bruto / 10.0f`). |
| `g_pot_hardware_bruto` | `/data` / Envio automático | **GET** / **POST** | `Number` (JSON) | Enviado ativamente para o Node-RED na rota `/can` como `tensao` calculada (0.00 a 3.30V). |
| `g_slider_value` | `/set_slider` | **POST** | `String` (Texto Puro) | Posição modificada no Slider da página HTML local. O ESP32 repassa ao Node-RED em `/slider`. |
| `g_node_red_slider` | `/set_nodered_value` | **POST** | `String` (Texto Puro) | Setpoint enviado do Node-RED para a rede CAN. Injeta um frame com ID `0x100` via barramento. |
| `g_node_red_freq` | `/set_nodered_freq` | **POST** | `String` (Texto Puro) | Referência de frequência lida do CLP e atualizada no ESP32 via Node-RED. |
| `g_mqtt_temperatura` & `g_mqtt_status` | `/set_mqtt_sim` | **POST** | `Object` (JSON) | Rota em que o Node-RED simula dados para o Card MQTT. Formato: `{"temp": X, "status": "Y"}`. |
| `ligar` | `/ligar` | **POST** | `String` (Texto Puro) | Comando de partida disparado pela interface web. O ESP32 propaga `"true"` para a rota `/ligar` do Node-RED. |
| `desligar` | `/desligar` | **POST** | `String` (Texto Puro) | Comando de paragem disparado pela interface web. O ESP32 propaga `"true"` para a rota `/desligar` do Node-RED. |
| `mqtt_aquecer` | `/mqtt_aquecer` | **POST** | `Object` (JSON) | Evento do Card MQTT gerado via HTML. Repassa `{"status":true}` para o endpoint `/mqtt_aquecer` do Node-RED. |
| `mqtt_resfriar` | `/mqtt_resfriar` | **POST** | `Object` (JSON) | Evento do Card MQTT gerado via HTML. Repassa `{"status":true}` para o endpoint `/mqtt_resfriar` do Node-RED. |
| `mqtt_desligar` | `/mqtt_desligar` | **POST** | `Object` (JSON) | Evento do Card MQTT gerado via HTML. Repassa `{"status":false}` para o endpoint `/mqtt_desligar` do Node-RED. |

## 2. Estrutura de dados

Ao inserir o link do site sem indicar o arquivo, do mesmo modo que qualquer outro site, o servidor hospedado procura por um arquivo de nome index e quaisquer variações de configuração. Esse arquivo index.php é responsável por disponibilizar os dados do banco de dados do servidor hospedado ao usuário

<p align="center"> <img src="figs/fluxograma.png" alt="Fluxograma" width="500"></p>
<p align="center"><b>Fluxograma</b></p>
<br><br>

Para inserir os dados é necessário adicionar o diretório “/upload.php” a url do site de modo a ficar:  https://curricularium.infinityfreeapp.com/upload.php ou somente curricularium.infinityfreeapp.com/upload.php, pois o navegador completa a pesquisa na web. 
	No arquivo upload.php existe um código que faz uma requisição ao ip do próprio pc utilizando o ip de loopback 127.0.0.1, conhecido como localhost, é o endereço do próprio computador. Deste modo é possível fazer requisições as portas do próprio computador e acessar a porta 1800, porta padrão por onde o Node-RED estará rodando e disponibilizando os dados os quais serão requisitados: http://127.0.0.1:1880/api/state.




### Estrutura de envio de dados

Descrição geral das configurações das variaveis.

| Variavel | Descrição |
| :--- | :--- |
| `Velocidade` | BYTE0 e BYTE1 são responsáveis pelos velocímetro, com uma escala de 0,1 km por bit, chegando a no máximo 99 km |
| `Bateria` | Passamos todos os valores entre 0 e 255 e nada foi acionado no display. Ou o display está com problema ou a variável da bateria situa-se em outro id, o qual o manual de modelo que obtivemos não disponibilizam, nem mesmo os parametros **nope** ativaram alguma funcionalidade extra. |
| `Marcha` | BYTE 6 é responsável pela marcha, 0 para N, 1 para D, e 2 para R. Quaisquer outros valores irão fazer com que nenhum estado de marcha esteja ativo |
| `Erro` | BYTE7 é responsável pelo sinal de erro, qualquer número entre 1 e 255 irá fazer o display apitar e disponibilizar na tela o código de erro periodicamente. |


### Estrutura de envio de dados

A estrutura de dados reconhecida pelo display E620 é a de uma sequencia de 8 bytes, cada qual responsavel por uma variavel, ou parte dela. É possivel enviar menos que 8 bytes, o display ainda irá recebe-los, bytes não enviados serão setados com zero por padrão.

| ID | BYTE0 | BYTE1 | BYTE2 | BYTE3 | BYTE4 | BYTE5 | BYTE6 | BYTE7 |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| `ID: 0x4D2` | `velocidade LSB` | `velocidade MSB` | nope | nope | nope | `Bateria` | `Marcha` | `Erro` |

