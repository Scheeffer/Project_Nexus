# 🟥 Website - online (Álvaro)

[![Protocolo](https://img.shields.io/badge/Website-infinityfree-green.svg)](https://www.iso.org/standard/63648.html)
[![Controlador](https://img.shields.io/badge/Frontend-HTML/CSS/Javascript-orange.svg)](#)
[![Controlador](https://img.shields.io/badge/Backend-PHP/MySQL-purple.svg)](#)

---

## 1	Descrição do projeto

Para disponibilizar os resultados obtidos do Node central sem depender de um dashboard local foi construído um site capaz de requisitar os dados localmente para a porta de onde o `Node-RED` central está rodando, salvá-los em um banco de dados e disponibilizá-los para qualquer indivíduo que acesse o domínio online, seja pelo computador ou pelo celular.
## 2. Funcionamento

o website possui duas entradas, a geral e a local. A entrada geral é aquela que é acessado como qualquer site, a partir de sua url, por exemplo exemplo: www.google.com. Possui a função de disponibilizar os dados para o público geral, esses dados são requisitado periodicamente ao banco de dados, caso haja alguma alteração no mesmo, o site o receberá em alguns momentos.

A segunda entrada é a de upload de dados. Através de um sistema de armazernamento similar ao de diretorios é possivel acessar diferentes arquivos dentro da pasta htdocs, pasta a qual irá retornar
em javascript em método post para um arquivo backend em php que rodará no servidor, onde está localizado o banco de dados,

<p align="center"> <img src="figs/Fluxograma.png" alt="diagrama" width="500"></p>
<p align="center"><b>Fluxograma do website</b></p>
<br><br>

## 2	Estrutura de dados

Ao inserir o link do site sem indicar o arquivo, do mesmo modo que qualquer outro site, o servidor hospedado procura automaticamente por um arquivo de nome index e quaisquer variações de extensão. Esse arquivo index.php é responsável por disponibilizar os dados do banco de dados do servidor hospedado ao usuári oem contraste ao upload.php que é responsavel pelo upload dos dados ao banco de dados do servidor.

Deste modo sempre que um usuario entrar com a url do site ele será direcionado

<p align="center"> <img src="figs/fluxograma.png" alt="Fluxograma" width="500"></p>
<p align="center"><b>Fluxograma</b></p>
<br><br>

Para inserir os dados é necessário adicionar o diretório “/upload.php” a url do site de modo a ficar:  https://curricularium.infinityfreeapp.com/upload.php ou somente curricularium.infinityfreeapp.com/upload.php, pois o navegador completa a pesquisa na web. 
	No arquivo upload.php existe um código que faz uma requisição ao ip do próprio pc utilizando o ip de loopback 127.0.0.1, conhecido como localhost, é o endereço do próprio computador. Deste modo é possível fazer requisições as portas do próprio computador e acessar a porta 1800, porta padrão por onde o Node-RED estará rodando e disponibilizando os dados os quais serão requisitados: http://127.0.0.1:1880/api/state.




### Estrutura de arquivos do Website.

Arquivos e suas funções:

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
`PHP` : linguagem de backend.<br>
`MySQL` : Estrura semantica lida por banco de dados para realizar diversas ações.<br>


### 4	configurações para futuros semestres

Como o website está situado em um servidor de uma conta privada de um aluno, a existencia do website e do dominio é incerta. Para utilizar e dar continuidade a está raiz do projeto será necessario criar um novo website. para isto você poderá utilizar o mesmo host de website utilizado neste projeto, o InfinityFree. Após criar uma nova conta e escolher um nome para o dominio você terá um website 

<p align="center"> <img src="figs/config_page.png" alt="Fluxograma" width="500"></p>
<p align="center"><b>Pagina de configuração</b></p>



https://sylphina.com.br/alvaro
https://sylphina.com.br/alvaro/upload.php







