<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    </head>
    <body>

        <h1>{{$cliente['nome']}}</h1>

        Detalhes do Pedido:<br>
        <table class="table" border="1" style="width: 400px;">
            <thead>
                <tr>
                    <th>Imagem</th>  
                    <th>Descricao</th> 
                    <th>Qtd</th>
                    <th>Preço</th>

                </tr>
            </thead>
            <tbody>

                @foreach($itens as $item)   

                <tr>
                    <td>
                        <img width="70" height="50" src="{{$item['url_foto']}}"></img>
                    </td>
                    <td style="text-align: center">{{$item['nome']}}</td>
                    <td style="text-align: center">{{$item['qtd']}}</td>  
                    <td style="text-align: center">{{$item['preco']}}</td>                   
                </tr>   

                @endforeach

                <tr>
                    <td colspan="3"></td>
                    <td style="text-align: center">{{$preco_total}}</td>
                </tr>
            </tbody>
        </table>


    </body>
</html>


