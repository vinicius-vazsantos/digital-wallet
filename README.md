# digital-wallet

**Descrição:**
Plataforma de conta digital que permite saques PIX rápidos e seguros, com gestão de saldo, notificações e tecnologia PHP Hyperf, MySQL e Docker.


## Regras de Negócio

- [x] A operação do saque deve ser registrada no banco de dados, usando as tabelas **account_withdraw** e **account_withdraw_pix**.  
- [x] O saque **sem agendamento** deve realizar o saque de imediato.  
- [x] O saque **com agendamento** deve ser processado somente via **cron**.  
- [x] O saque deve deduzir o saldo da conta na tabela **account**.  
- [x] Atualmente só existe a opção de saque via **PIX** com chaves do tipo **email**.  
- [x] A implementação deve permitir expansão para **outros tipos de saque** no futuro.  
- [x] Não é permitido sacar um valor maior que o disponível no saldo da conta digital.  
- [x] O saldo da conta não pode ficar negativo.  
- [x] Para **saque agendado**, não é permitido agendar para um momento no passado.  
- [x] Para **saque agendado**, não é permitido agendar para uma data maior que **7 dias no futuro**.  

---

## Configuração do ambiente de desenvolvimento Hyperf com PHP 8.1 e Swoole

### Requisitos

* Ubuntu 22.04 ou similar
* PHP 8.1
* Composer
* Git
* Extensões básicas de desenvolvimento (`build-essential`, `autoconf`, `pkg-config`)

---

## Passo 1: Instalar PHP 8.1 e dependências

```bash
sudo apt update
sudo apt install -y php8.1 php8.1-cli php8.1-dev php8.1-zip php8.1-mbstring php8.1-sockets php8.1-mysql php-pear git curl build-essential autoconf pkg-config
```

---

## Passo 2: Clonar o repositório do Swoole

```bash
git clone https://github.com/swoole/swoole-src.git
cd swoole-src
```

---

## Passo 3: Compilar e instalar o Swoole

```bash
make clean
sudo phpize8.1
./configure --with-php-config=/usr/bin/php-config8.1 --enable-openssl --enable-sockets --enable-mysqlnd
make
make test
sudo make install
```

OBS.: Cuidado com pastas com espaço, pode gerar erro na compilação

---

## Passo 4: Habilitar a extensão Swoole no PHP

```bash
echo "extension=swoole.so" | sudo tee /etc/php/8.1/mods-available/swoole.ini
sudo phpenmod swoole
sudo systemctl restart php8.1-fpm
php -m | grep swoole
```

## Passo 5: Instalar o Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```