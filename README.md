 # laravel-aux
  Pacote que tem como intuito auxiliar na padronização e criação de um CRUD de uma API (**Laravel**) que segue o padrão 
  (**View** -> **Controller** -> **Service** -> **Repository** -> **Model**)
  
  - **View**: Responsável por mostrar as informações aos usuários e captar os eventos/ações.
  - **Controller**: Responsável apenas por receber uma solicitação/ação do usuário e requisitar para uma Service.
  - **Service**: Responsável por conter as regras de negócio no geral, porém nunca realizando as queries e sim, passando essa     responsabilidade para as repositories.
  - **Repository**: Responsável pela realização das queries através da utilização da model. Pode ser definida como uma camada     de segurança.
  - **Model**: Responsável por buscar/atualizar/remover e/ou modificar os dados no banco de dados através do consumo do ORM do   Laravel (**Eloquent**).