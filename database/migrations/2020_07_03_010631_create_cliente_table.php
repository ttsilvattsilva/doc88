<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cliente', function (Blueprint $table) {

            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('endereco')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });

        DB::table('cliente')->insert(
                [
                    ['nome' => 'Thiago T Silva',
                        'email' => 'ttsilva.info@gmail.com',
                        'telefone' => '(11) 93012-3627',
                        'data_nascimento' => '1991-03-20',
                        'endereco' => 'Rua João da Silva',
                        'complemento' => 'Casa 2',
                        'bairro' => 'Jardim São Paulo',
                        'cep' => '04430-240'
                    ],
                    ['nome' => 'José Silva',
                        'email' => 'jose@gmail.com',
                        'telefone' => '(11) 93012-3629',
                        'data_nascimento' => '1995-03-20',
                        'endereco' => 'Rua João da Silva',
                        'complemento' => 'Casa 2',
                        'bairro' => 'Jardim São Paulo',
                        'cep' => '04430-240'
                    ],
                ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cliente');
    }

}
