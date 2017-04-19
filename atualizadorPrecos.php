<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atualizador</title>
    <!-- Custom styles for this template -->
    <link href="../css/bootstrap.css" rel="stylesheet">
</head>

<body>
<table class="table table-condensed table-hover table-responsive table-striped">
  <tbody>
    <tr>
      <th colspan="5">Processo de atualização executado</th>
    </tr>
    <tr>
      <th>Origem</th>
      <th>O</th>
      <th>ID</th>
      <th>Descrição</th>
      <th>Valor</th>
    </tr>
    
<?php

$hostMysql = "181.41.198.253";  //Endereço ip do banco de dados

$bdnameMysql = "emporioa_pres844";  //Nome do banco de dados

$userMysql = "emporioa"; //Usuário de acesso ao banco de dados

$passwordMysql = "Euatopng"; //Senha de acesso ao banco de dados


$conexaoMysql = mysqli_connect($hostMysql,$userMysql,$passwordMysql,$bdnameMysql); // Conectacom o banco

// Checa conexão
if (mysqli_connect_errno())
  {
  echo "<br><br><br>Failed to connect to MySQL: " . mysqli_connect_error() . "<br><br><br>";
  }

// Seleciona tabela
$sql = "SELECT * FROM psku_product";
$resultMysql = mysqli_query($conexaoMysql, $sql);

// Lista produto da tabela
if (mysqli_num_rows($resultMysql) > 0) {
    // output data of each row
    while($rowMysql = mysqli_fetch_assoc($resultMysql)) {
		
		// CONDIÇÃO PARA EXECUTAR A ATUALIZAÇÃO
		
		if($rowMysql["reference"]!=""){
		
			echo "
			  <tr>
				  <td>Site</td>
				  <td>&nbsp;</td>
				  <td>" . $rowMysql["id_product"]. 						"</td>
				  <td></td>
				  <td> R$" . number_format($rowMysql["price"],"2",",",".") . 	"</td>
			  </tr>
				";




				# AQUI COMEÇA A CONEXAO DO POSSTGRE PARA PEGAR O VALOR ATUALIZADO		
				$hostPostgre = "emporioautomacao.no-ip.info";  //Endereço ip do banco de dados

				$portPostgre = "5432";  //Porta de conexão com o banco de dados

				$bdnamePostgre = "control";  //Nome do banco de dados

				$userPostgre = "usuario"; //Usuário de acesso ao banco de dados

				$passwordPostgre = "123123"; //Senha de acesso ao banco de dados

				$conexaoPostgre = pg_connect("host=$hostPostgre port=$portPostgre dbname=$bdnamePostgre user=postgres");

				$resultPostgre = pg_query($conexaoPostgre, "SELECT * FROM produtos WHERE codigo=".$rowMysql["reference"].";");


				if (pg_num_rows($resultPostgre) == 0) {
				   echo "0 records";
				  }
				  else {

						$rowPostgre = pg_fetch_array($resultPostgre);
					  
						echo "
						  <tr>
							  <td>Base Arpa</td>
							  <td>&nbsp;</td>
							  <td>"		. $rowPostgre["codigo"] ,							"</td>
							  <td>"		. $rowPostgre["descricao"] .						"</td>
							  <td> R$"	. number_format($rowPostgre["precovenda"],"2",",",".") .	"</td>
						  </tr>
							";

				  }
				#FIM DA COLETA DE DADOS POSTGRE


		// Atualiza o preço no site.	
		 $updateMysql = mysqli_query($conexaoMysql, "UPDATE psku_product SET price=".$rowPostgre["precovenda"]." WHERE reference=". $rowPostgre["codigo"] .";");

		// Atualisa preço na tabela psku_product_shop que se refere a qual loja online é
		 $updateMysql = mysqli_query($conexaoMysql, "UPDATE psku_product_shop SET price=".$rowPostgre["precovenda"]." WHERE id_product=". $rowMysql["id_product"] .";");


		} // FIM DA CONDIÇÃO DE ATUALIZAÇÃO
	}
} else {
    echo "0 results";
}

mysqli_close($conexaoMysql);

?>

  </tbody>
</table>
