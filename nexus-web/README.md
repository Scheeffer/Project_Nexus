# 🟥 Website - online (Álvaro)

[![Protocolo](https://img.shields.io/badge/Website%202.0A-red.svg)](https://www.iso.org/standard/63648.html)
[![Controlador](https://img.shields.io/badge/Frontend-HTML/CSS/Javascript-orange.svg)](#)
[![Controlador](https://img.shields.io/badge/Backend-PHP/MySQL-purple.svg)](#)

---

## 1. Descrição do projeto

Para disponibilizar os resultados obtidos sem depender de um dashboard local foi construído um site capaz de requisitar os dados para a porta de onde o `Node-RED` central está trabalhando, salvá-los em um banco de dados e disponibilizá-los para qualquer indivíduo que acesse o domínio online, seja pelo computador ou pelo celular.

o website possui duas entradas, a geral e a local, a geral tem a função de disponibilizar os dados para o público geral, esses dados são requisitado em javascript em método post para um arquivo backend em php que rodará no servidor, onde está localizado o banco de dados,

<p align="center"> <img src="figs/Fluxograma.png" alt="diagrama" width="500"></p>
<p align="center"><b>Fluxograma do website</b></p>
<br><br>

O grande objetivo desta célula é ler de maneira contínua os dados de um sensor analógico (potenciômetro) mapeado sob o identificador exclusivo CAN `, processar os pacotes para o cálculo de velocidade real em km/h e comandar um painel atuador de indicadores (Painel E620) via ID CAN `0x4D2`. O Gateway ESP32 também atua como **ponte** para o backbone (Node-RED) por meio de requisições assíncronas **HTTP (POST/GET)** em formato de texto puro (`text/plain`) e JSON. A grande vantagem desse design é garantir a operação offline e robusta da rede de campo CAN, enquanto permite a convergência com o sistema supervisório centralizado.

### Variáveis Disponíveis ao Node-RED / Servidor HTTP
.

## 2. Estrutura de dados

Ao inserir o link do site sem indicar o arquivo, do mesmo modo que qualquer outro site, o servidor hospedado procura automaticamente por um arquivo de nome index e quaisquer variações de extensão. Esse arquivo index.php é responsável por disponibilizar os dados do banco de dados do servidor hospedado ao usuári oem contraste ao upload.php que é responsavel pelo upload dos dados ao banco de dados do servidor.

Deste modo sempre que um usuario entrar com a url do site ele será direcionado

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

