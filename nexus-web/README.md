# 🟥 Website - online (Álvaro)

[![Protocolo](https://img.shields.io/badge/Website-infinityfree-green.svg)](https://www.iso.org/standard/63648.html)
[![Controlador](https://img.shields.io/badge/Frontend-HTML/CSS/Javascript-orange.svg)](#)
[![Controlador](https://img.shields.io/badge/Backend-PHP/MySQL-purple.svg)](#)

---

## 1	Descrição do projeto

Para disponibilizar os resultados obtidos do Node central sem depender de um dashboard local foi construído um site capaz de requisitar os dados localmente para a porta de onde o `Node-RED` central está rodando, salvá-los em um banco de dados e disponibilizá-los para qualquer indivíduo que acesse o domínio online, seja pelo computador ou pelo celular.

## 2	Funcionamento

o website possui duas entradas, a geral e a local. A entrada geral é aquela que é acessado como qualquer site, a partir de sua url pura, por exemplo exemplo: www.google.com. o website funciona de modo em que as instruções da sua configuração `.htacess` do servidor retornem para o usuario, a partil da URL dada, qualquer arquivo que se encaixe no padrão de nome configurado, sendo os mais comum `index.xxx` como na figura abaixo. Deste modo o dashboard online para o publico geral será sempre disponibilizada se não houver especificação do arquivo na URL.

<p align="center"> <img src="figs/htaccess.png" alt="diagrama" width="500"></p>
<p align="center"><b>Configuração .htacess do servidor PHP local Apache</b></p>
<br><br>

A segunda entrada é a de upload de dados. Através de um sistema de armazernamento similar ao de diretorios é possivel acessar diferentes arquivos dentro da pasta `htdocs` (Hyper Text Documents), pasta que contem todos os arquivos de seu website. Portanto selecionando a URL + `/arquivo.xxx`, podemos entrar na pagina de upload de dados ao banco de dados do servidor. O arquivo `/upload.php` realiza requisições http ao IP de loopback `127.0.0.1`, conhecido como localhost, é o endereço do próprio computador. Deste modo é possível fazer requisições as portas do próprio computador e acessar a porta 1800, porta padrão por onde o Node-RED estará rodando e disponibilizando os dados em tempo real . o IP `127.0.0.1`, porta `1880` e diretorio `/api/state` podem vir a ser mudados a depender da circunstancia. 

<p align="center"> <img src="figs/url_fetch.png" alt="diagrama" width="100%"></p>
<p align="center"><b>Endereço da requisição http</b></p>

<p align="center"> <img src="figs/Fluxograma.png" alt="diagrama" width="500"></p>
<p align="center"><b>Fluxograma do website</b></p>
<br><br>

## 3	Estrutura de dados

Ao inserir o link do site sem indicar o arquivo, do mesmo modo que qualquer outro site, o servidor hospedado procura automaticamente por um arquivo de nome index e quaisquer variações de extensão. Esse arquivo index.php é responsável por disponibilizar os dados do banco de dados do servidor hospedado ao usuári oem contraste ao upload.php que é responsavel pelo upload dos dados ao banco de dados do servidor.

Para inserir os dados é necessário adicionar o diretório “/upload.php” a url do site de modo a ficar:  https://`dominio`.infinityfreeapp.com/upload.php ou somente `dominio`.infinityfreeapp.com/upload.php, pois o navegador completa a pesquisa na web. No arquivo upload.php existe um código que faz uma requisição ao ip do próprio pc utilizando o ip de loopback 127.0.0.1, conhecido como localhost, é o endereço do próprio computador. Deste modo é possível fazer requisições as portas do próprio computador e acessar a porta 1800, porta padrão por onde o Node-RED estará rodando e disponibilizando os dados os quais serão requisitados: http://127.0.0.1:1880/api/state. o IP `127.0.0.1`, porta `1880` e diretorio `/api/state` podem vir a ser mudados a depender da circunstancia. No caso deste projeto, esse endereço significa que o website irá procurar o diretorio na porta default do Node-RED no proprio computador.

### Estrutura de arquivos do Website.

### Descrição geral dos arquivos
Resumo geral dos arquivos e suas funcionalidades.

| Arquivo | Descrição |
| :---: | :--- |
| `index.php` | Disponibiliza um dashboard online através de requisições periodicas ao banco de dados do host. |
| `upload.php` | Quando setado corretamente, fará o upload dos dados ao banco de dados do host. |
| `controller.php` | Arquivo responsavel por gerenciar as requisições ao servidor local.  |
| `backend.php` | Arquivo que contem classes responsaveis por conectar-se e realizar queries ao banco de dados. |
| `estilo.css` | Arquivo responsavel por formatar a configuração estética HTML do website. |
| `/imagens` | Pasta que contem as imagens, icones, gifs, audio, etc, utilizados pelo website. |


### Definições das Linguagens e arquivos utilizadas.

`HTML` : Responsavel por criar a estrutura do website, quando o navegador recebe o código HTML ele cria uma arvore de documentos/objetos).<br>
`CSS` : Formata a configuração padrão de objetos HTML.<br>
`javascript` : Linguagem de alto nivel responsavel pelo <br>
`PHP` : linguagem de backend, utilizadas nos servidores. Suporta HTML e Javascript em seu arquivo. Todo código php fica invisivel ao usuario, diferente do html<br>
`MySQL` : Estrura semantica lida por banco de dados para realizar diversas ações no mesmo.<br>


### 4	configurações para futuros semestres

Como o website está situado em um servidor de uma conta privada de um aluno, a existencia futura do website e do dominio é incerta. Para utilizar e dar continuidade a está raiz do projeto será necessario criar um novo website. para isto você poderá utilizar o mesmo host de website utilizado neste projeto, o InfinityFree. Após criar uma nova conta e escolher um nome para o dominio você terá um novo website e pderá acessar a pagina de configuração.

<p align="center"> <img src="figs/config_page.png" alt="Fluxograma" width="100%"></p>
<p align="center"><b>Pagina de configuração</b></p>

### Descrição geral dos arquivos
Resumo geral dos arquivos e suas funcionalidades.

| opção | Descrição |
| :---: | :--- |
| `Overview` | Dados gerais do website. |
| `file manager` | local para armazenar e atualizar os arquivos do website dentro da pasta `htdocs`. |
| `MySQL Databases` | Banco de dados MySQL onde seus dados serão criados/atualizados. |
| `resource Usage` | Aba de uso de recursos do website. Cuidado para não ultrapassar os 50000 hits, que significa requisitar os **arquivos** ao host, Atualizar o website não requisitar os arquivos novamente mas CTRL + F5 e abri-lo em uma nova aba requisitam.  |
| `Control Panel` | Configurações gerais do website. |

### Banco de dados
Será necessario criar e acessar o banco de dados em seu novo website. No arquivo `backend.php` existe a classe `Connection` responsavel por realisar a conexão ao banco de dados, ele utiliza os parametros host, dbname, user e password que são auto explicativos.

<p align="center"> <img src="figs/connection.png" alt="Fluxograma" width="100%"></p>
<p align="center"><b>Fluxograma</b></p>
<br><br>

Esses dados são obtidos na aba `MySQL Databases` discutida na tabela logo acima.

<p align="center"> <img src="figs/database_details.png" alt="Fluxograma" width="100%"></p>
<p align="center"><b>Fluxograma</b></p>
<br><br>

Com Esses parametros configurados você podera acessar o banco de dados local a partir do website.

https://sylphina.com.br/alvaro
https://sylphina.com.br/alvaro/upload.php







