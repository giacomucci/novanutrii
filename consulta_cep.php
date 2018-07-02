<?php

// Recebe o parâmetro cep via GET do Magento
$cep = substr($_GET['cep'], 0, 5)."-".substr($_GET['cep'], 5, 7);

// Dados do Banco de Dados com os CEPs
$servername = "nutrii.cjpzokiaj1oh.us-east-1.rds.amazonaws.com";
$username = "root";
$password = "HvyIVmAbCljV1Nx7ZZqV";
$dbname = "production";

// Connects to the Database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Run a query in the Database to find the CEP
$sql  = "SELECT * FROM `enderecos` WHERE `cep` LIKE '".$cep."'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // List the CEP information
    while($row = $result->fetch_assoc()) { ?>
      var resultadoCEP = { 'uf' : '<?php echo utf8_encode($row["uf"]); ?>', 'cidade' : '<?php echo utf8_encode($row["cidade"]); ?>', 'bairro' : '<?php echo utf8_encode($row["bairro"]); ?>', 'tipo_logradouro' : '', 'logradouro' : '<?php echo utf8_encode($row["logradouro"]); ?>', 'resultado' : '1', 'resultado_txt' : 'sucesso - cep completo' }
    <?php
    }

}

$conn->close(); 

?>