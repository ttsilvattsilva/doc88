

Requisitos 

PHP >= 7.2.5
MySql-Server 
apache2 (ative o a2enmod rewrite)


INSTALAÇÃO E CONFIGURAÇÃO DO PROJETO LARAVEL

1° - composer install
2° - php artisan key:generate 

     insera as credenciasis do mysql e do SMTP no arquivo ".env"
3° - Crie um Schema no banco de dados, com o nome de sua preferencia
4° - php artisan migrate
5° - sudo chmod 777 -R storage/ 

Relizei os tesde usando o apache2, url de teste;

base_url() .  'doc88/api/pastelaria'



