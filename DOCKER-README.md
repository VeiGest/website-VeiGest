# Docker / Compose — VeiGest (resumo rápido)

Passos rápidos:

1. Copie o `.env.example` e ajuste senhas, se desejar:

```bash
cp .env.example .env
```

2. Suba os serviços (build e em background):

```bash
docker compose up -d --build
```

3. Verifique logs:

```bash
docker compose logs -f backend
```

4. Rodar migrations (dentro do container `backend`):

```bash
docker compose exec backend php /app/yii migrate --interactive=0
```

5. Acessos úteis:
- Frontend: http://localhost:20080
- Backend:  http://localhost:21080
- phpMyAdmin: http://localhost:8080  (usuário definido em `.env`)

Observações:
- Os serviços `frontend` e `backend` usam `./veigest` como volume de desenvolvimento.
- Se houver permissões de ficheiros no MySQL, ajuste UID/GID ou execute `chown` no volume `db_data`.
