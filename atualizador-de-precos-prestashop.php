<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atualizador</title>
    <!-- Custom styles for this template -->
    <link href="../console/css/bootstrap.css" rel="stylesheet">
</head>

<body>
<table class="table-condensed table-hover table-responsive">
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
error_reporting(E_ALL);
ini_set('display_errors', 'On');

/**
 * Bugs/Alterações
 * 01 - Criar uma rotina para verificar se o preço mínimo não é menor que o prçeo de compra + ALGUM % (percentual)
 */

/**
 * Atualizador de preços Prestashop
 * Nesta versão foi adicionada a atualização de preços para os cliente de Revenda utilizando a tabela
 * 'psku_specific_price' que dentro do Pretashop é encontrado na aba 'Produtos>Precos>Preços Específicos' e no Sistema
 * Control 'Produtos>Incluir>Preço Mínimo'
 * @version 1.0.0.1
 * @author James Flávio Nunes da Cruz
 */

/**
 * Dsdos para conexão ao banco de dados Prestashop Mysql
 * @var string $hostMysql       Endereço ip do banco de dados
 * @var string $dbnameMysql     Nome do banco de dados
 * @var string $userMysql       Usuário de acesso ao banco de dados
 * @var string $passwordMysql   Senha de acesso ao banco de dados
  */

$hostMysql      = "181.41.198.253";
$bdnameMysql    = "emporioa_pres844";
$userMysql      = "emporioa";
$passwordMysql  = "Euatopng";

/**
 * @var string $conexaoMysql    Executa conexão com o banco
 */

$conexaoMysql = mysqli_connect($hostMysql,$userMysql,$passwordMysql,$bdnameMysql);

/**
 * Checa conexão
 */

if (mysqli_connect_errno())
  {
  echo "<br><br><br>Failed to connect to MySQL: " . mysqli_connect_error() . "<br><br><br>";
  }
/**
 * @var string $sql         Seleciona tabela 'psku_product', onde será feitas as inserções dos valores para o site no varejo
 * @var string $resultMysql Executa o SELECT na tabela
 */

$sql = "SELECT * FROM psku_product";
$resultMysql = mysqli_query($conexaoMysql, $sql);

/**
 * Lista produto da tabela se o resultado da consulta '$resultMysql' for maior que 0
 */
if (mysqli_num_rows($resultMysql) > 0) {

    /**
     * Faz o while dos resultados
     */

    while($rowMysql = mysqli_fetch_assoc($resultMysql)) {

        /**
         * Exibe o item se o mesmo tiver o Código de Referência no banco com o nome de 'reference' vinculado a algum
         * produto no Sistema Control indentificado como 'codigo" no banco
         */

		if($rowMysql["reference"]!=""){
		
			echo "
			  <tr class='active'>
				  <td>Site</td>
				  <td>&nbsp;</td>
				  <td>" . $rowMysql["id_product"]. 						"</td>
				  <td></td>
				  <td> R$" . number_format($rowMysql["price"],"2",",",".") . 	"</td>
			  </tr>
			";


            # AQUI COMEÇA A CONEXAO DO POSSTGRE PARA PEGAR O VALOR ATUALIZADO
			#$hostPostgre    = "emporioautomacao.no-ip.info";  //Endereço ip do banco de dados utilizando o no-ip
			$hostPostgre     = "192.168.1.200";  //Endereço ip do banco de dados com endereço do servidor local
			$portPostgre     = "5432";  //Porta de conexão com o banco de dados
			$bdnamePostgre   = "control";  //Nome do banco de dados
			$userPostgre     = "usuario"; //Usuário de acesso ao banco de dados
			$passwordPostgre = "123123"; //Senha de acesso ao banco de dados

            /**
             * @var string $conexaoPostgre Executa conexão com o banco do sistema Control PostgreSql
             */
			$conexaoPostgre  = pg_connect("host=$hostPostgre port=$portPostgre dbname=$bdnamePostgre user=postgres");

            /**
             * @var string $resultPostgre
             */

			$resultPostgre   = pg_query($conexaoPostgre, "SELECT * FROM produtos WHERE codigo=".$rowMysql["reference"].";");

			if (pg_num_rows($resultPostgre) == 0) {

			   echo "0 Resultados encontrados no banco do Sistema Arpa Control.";

			} else {

				$rowPostgre = pg_fetch_array($resultPostgre);

				echo "
				  <tr>
					  <td>Base Arpa - Varejo</td>
					  <td>&nbsp;</td>
					  <td>"		. $rowPostgre["codigo"] ,							"</td>
					  <td>"		. $rowPostgre["descricao"] .						"</td>
					  <td> R$"	. number_format($rowPostgre["precovenda"],"2",",",".") .	"</td>
				  </tr>
				  <tr>
					  <td>Base Arpa - Atacado</td>
					  <td>&nbsp;</td>
					  <td>"		. $rowPostgre["codigo"] ,							"</td>
					  <td>"		. $rowPostgre["descricao"] .						"</td>
					  <td> R$"	. number_format($rowPostgre["preco_minimo"],"2",",",".") .	"</td>
				  </tr>
				";

            }
            #FIM DA COLETA DE DADOS POSTGRE

            /**
             * Atualiza o preço no site.
             */

            $updateMysql = mysqli_query($conexaoMysql, "UPDATE psku_product SET price=".$rowPostgre["precovenda"]." WHERE reference=". $rowPostgre["codigo"] .";");

            /**
             * Atualisa preço na tabela psku_product_shop que se refere a qual loja online é
             */

            $updateMysql = mysqli_query($conexaoMysql, "UPDATE psku_product_shop SET price=".$rowPostgre["precovenda"]." WHERE id_product=". $rowMysql["id_product"] .";");

            /**
             * @var string $psku_specific_price_verificaSeExiste Verifica se existe o item na tabela 'psku_specific_price' e se o valor de Revenda não está zerado, se existir ele atualiza, pelo contrário ele inclui.
             */

            $psku_specific_price_verificaSeExiste = mysqli_num_rows(mysqli_query($conexaoMysql,"SELECT * FROM psku_specific_price WHERE id_product = '".$rowMysql['id_product']."';"));

            if($psku_specific_price_verificaSeExiste>0){
                /**
                 * Executa atualização do valor para Revenda
                 */

                #echo "Sim SELECT * FROM psku_specific_price WHERE id_product = '".$rowMysql['id_product']."';' <br> updateMysql = mysqli_query(conexaoMysql, 'UPDATE psku_specific_price SET price=".$rowPostgre['preco_minimo']." WHERE id_product=". $rowMysql['id_product'] .";');<br>";

                $updateMysql = mysqli_query($conexaoMysql, "UPDATE psku_specific_price SET price=".$rowPostgre["preco_minimo"]." WHERE id_product=". $rowMysql["id_product"] .";");

            } else {

                /**
                 * @var string $insertMysql Executa a inclusão do valor para Revenda
                 */

                $insertMysql = mysqli_query($conexaoMysql, "INSERT INTO psku_specific_price (id_product,id_group,price) VALUES ('". $rowMysql["id_product"] ."','4','".$rowPostgre["preco_minimo"]."');");

            }

            #$updateMysql = mysqli_query($conexaoMysql, "UPDATE psku_product_shop SET price=".$rowPostgre["precovenda"]." WHERE id_product=". $rowMysql["id_product"] .";");

        }

	}

} else {

    echo "0 results";

}

/**
 * Fecha a conexão com MySql e PgSql
 */

mysqli_close($conexaoMysql);
pg_close($conexaoPostgre);

?>

  </tbody>
</table>