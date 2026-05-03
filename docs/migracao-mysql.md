# MySQL Como Banco Padrao

Este projeto usa MySQL como banco padrao da aplicacao. SQLite fica restrito aos testes automatizados em memoria, configurados no `phpunit.xml`.

## Checklist

1. Se houver dados antigos em SQLite, criar backup antes de migrar:
   `cp database/database.sqlite database/database.backup.sqlite`

2. Criar o banco MySQL com charset recomendado:
   `utf8mb4` e collation `utf8mb4_unicode_ci`.

3. Atualizar o `.env` do ambiente alvo:
   `DB_CONNECTION=mysql`
   `DB_HOST=127.0.0.1`
   `DB_PORT=3306`
   `DB_DATABASE=wms`
   `DB_USERNAME=wms_app`
   `DB_PASSWORD=wms_app_local`

4. Criar o banco e o usuario da aplicacao no MySQL:
   `sudo mysql`

   Dentro do prompt do MySQL, executar:

   ```sql
   CREATE DATABASE IF NOT EXISTS wms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER IF NOT EXISTS 'wms_app'@'localhost' IDENTIFIED BY 'wms_app_local';
   CREATE USER IF NOT EXISTS 'wms_app'@'127.0.0.1' IDENTIFIED BY 'wms_app_local';
   GRANT ALL PRIVILEGES ON wms.* TO 'wms_app'@'localhost';
   GRANT ALL PRIVILEGES ON wms.* TO 'wms_app'@'127.0.0.1';
   FLUSH PRIVILEGES;
   ```

5. Limpar cache de configuracao:
   `php artisan config:clear`

6. Rodar migrations no MySQL:
   `php artisan migrate`

7. Se necessario, migrar dados do SQLite para MySQL com ferramenta apropriada ao ambiente.

8. Validar rotas, login e APIs principais:
   `php artisan route:list`
   `php artisan test`

## Pontos De Atencao

- Nao alterar migrations ja usadas em producao sem criar uma nova migration corretiva.
- Nao usar `root` como usuario da aplicacao; criar um usuario dedicado com permissao somente no banco `wms`.
- Conferir campos com tipos sensiveis a banco, como `json`, `text`, datas e indices compostos.
- Validar chaves estrangeiras depois da importacao, pois MySQL aplica restricoes de forma mais rigida.
- Para testes automatizados, manter `DB_CONNECTION=sqlite` e `DB_DATABASE=:memory:` somente no `phpunit.xml`.
