<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePastelTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('pastel', function (Blueprint $table) {

            $table->id();
            $table->string('nome');
            $table->string('url_foto');
            $table->float('preco', 8, 2);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
        
         DB::table('pastel')->insert(
                [
                    ['nome' => 'Frango',
                     'url_foto' => 'https://aws-codestar-us-east-2-532773768415-api-pipe.s3.us-east-2.amazonaws.com/pastel_frango.png',
                     'preco' => '5',                     
                    ],
                    ['nome' => 'Carne',
                     'url_foto' => 'https://aws-codestar-us-east-2-532773768415-api-pipe.s3.us-east-2.amazonaws.com/pastel_carne.png',
                     'preco' => '5',                     
                    ],
                    ['nome' => 'Carne com queijo',
                     'url_foto' => 'https://aws-codestar-us-east-2-532773768415-api-pipe.s3.us-east-2.amazonaws.com/pastel_carne_queijo.png',
                     'preco' => '6',                     
                    ],
                    ['nome' => 'Pizza',
                     'url_foto' => 'https://aws-codestar-us-east-2-532773768415-api-pipe.s3.us-east-2.amazonaws.com/pastel_pizza.png',
                     'preco' => '5',                     
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
        Schema::dropIfExists('pastel');
    }

}
