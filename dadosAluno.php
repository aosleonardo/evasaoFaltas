<?php
include_once './conexao/conexao.php';
include_once './funcoes/funcoes.php';
conectar("CORPORERM");

$codUsuario = $_GET['mat'];

$query = "SELECT  DISTINCT E.NOME AS ALUNO, UT.CODTUR,m.CODMAT, M.MATERIA ,ISNULL(PA.ABERTAS,0) [PARC_ABERTAS],
               ISNULL(UT.A0,0.00) AS NOTA_TOTAL,
               ISNULL(UT.A1,0.00) AS [1ª ETAPA],
               ISNULL(UT.A2,0.00) AS [2ª ETAPA],
               ISNULL(UT.A3,0.00) AS [3ª ETAPA],
               ISNULL(UT.A4,0.00) AS [EXAME ESP.],
               ISNULL(UT.AD0,0) AS TOTAL_AULAS,
               ISNULL(F0,0) AS FALTAS,
               ISNULL(CAST(( (((AD0)- ISNULL(F0,0))/CAST((AD0) AS FLOAT)))AS DECIMAL (5,2)),0.00) AS PERCENT_FREQ
               FROM EALUNOS E 
               INNER JOIN UMATALUN UT ON
               E.MATRICULA = UT.MATALUNO AND 
               E.CODCOLIGADA = UT.CODCOLIGADA
               INNER JOIN UMATERIAS M ON
               M.CODCOLIGADA = UT.CODCOLIGADA AND
               M.CODMAT = UT.CODMAT
               LEFT JOIN PARCELAS_ABERTAS PA ON
               PA.CODCOLIGADA = UT.CODCOLIGADA AND
               PA.MATALUNO = UT.MATALUNO AND
               PA.CODCUR = UT.CODCUR AND
               PA.PERLETIVO = UT.PERLETIVO
               WHERE  E.CODUSUARIO = '$codUsuario'
               AND UT.PERLETIVO = CONVERT(VARCHAR(5),DATEPART(YEAR,GETDATE()))+CASE WHEN DATEPART(MONTH,GETDATE()) <=6 THEN '/1' ELSE '/2' END
               AND UT.CODTIPOMATRICULA = 01 AND UT.STATUS = 2";


$result = mssql_query($query);

//Pegar array matricula

$query2 = "select disciplina,periodo from AUXORQUESTRA..disciplinaEvasaoFaltas where idProcessoEvasao = (select id from AUXORQUESTRA..processosEvasaoFaltas where processo = {$_GET['codFlow']})";

$res = mssql_query($query2);

while ($rows = mssql_fetch_array($res, MSSQL_ASSOC)) {

    $hor[$rows['disciplina']] = $rows['periodo'];
}
?>

<style>
    .destaque{
        background: #DADADA;
    }
</style>

<table border="1" cellpadding="1" cellspacing="1" class="form-custom" mult="N" style="width: 99%;">
    <tbody>
        <tr class="header">
            <td>Turma</td>
            <td>Materia</td>
            <td>Par. Abertas</td>
            <td>1&ordf; Etapa</td>
            <td>2&ordf; Etapa</td>
            <td>3&ordf; Etapa</td>
            <td>Total</td>
            <td>Total de Aulas</td>
            <td>Faltas</td>
            <td>Per&iacute;odo da aus&ecirc;ncia&nbsp;</td>
            <td>% Presença</td>
        </tr>
        <?php
        while ($row = mssql_fetch_array($result, MSSQL_ASSOC)) {
            ?>
            <tr <?php destaque($hor, $row['CODMAT']) ?> >
                <td><?php echo $row['CODTUR'] ?></td>
                <td><?php echo utf8_encode($row['MATERIA']) ?></td>
                <td><?php echo $row['PARC_ABERTAS'] ?></td>
                <td><?php echo $row['1ª ETAPA'] ?></td>
                <td><?php echo $row['2ª ETAPA'] ?></td>
                <td><?php echo $row['3ª ETAPA'] ?></td>
                <td><?php echo $row['NOTA_TOTAL'] ?></td>
                <td><?php echo $row['TOTAL_AULAS'] ?></td>
                <td><?php echo $row['FALTAS'] ?></td>
                <td><?php echo periodo($hor, $row['CODMAT'])  ?></td>
                <td><?php echo $row['PERCENT_FREQ'] * 100 ?> %</td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php

function destaque($array, $disc) {
    foreach ($array as $key => $value) {
        if( $key == $disc){
            echo 'class="destaque"';
        }
    }
}

function periodo($array, $disc) {
    foreach ($array as $key => $value) {
        if( $key == $disc){
            echo $value;
        }
    }
}
