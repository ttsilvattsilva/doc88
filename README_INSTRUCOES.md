Requisitos 

PHP >= 7.2.5
    BCMath PHP Extension
    Ctype PHP Extension
    Fileinfo PHP extension
    JSON PHP Extension
    Mbstring PHP Extension
    OpenSSL PHP Extension
    PDO PHP Extension
    Tokenizer PHP Extension
    XML PHP Extension

Banco de dados 
    MySql-Server 

--------------------------------------------------------------------------------
Passe as credenciasis de acesso do mysql e do servidor no arquivo ".env" 

INSTALAÇÃO CONFIGURAÇÃO DO PROJETO LARAVEL

composer install
php artisan key:generate 
php artisan migrate

nesse projeto configurei para rodar no apache2, defini um arquivo .htacces na raiz do projeto

Ative no apache2 o a2enmod rewrite