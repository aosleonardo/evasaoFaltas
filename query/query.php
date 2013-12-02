<?php

function buscaAlunos() {


    $query = "SELECT 
                    distinct top 50 MATALUNO,
                                     CODCOLIGADA 
                FROM 
                    auxorquestra..evasaoFaltasLista 
            ORDER BY 
                    mataluno";
    $result = mssql_query($query);

    while ($row = mssql_fetch_array($result, MSSQL_ASSOC)) {
        $rows[] = $row['CODCOLIGADA'] . '*' . $row['MATALUNO'];
    }
    return $rows;
}

function buscaDisciplinas($codUsuario) {
    $ano = date("Y");
    $semestre = date("Y") > 7 ? 2 : 1;

    $codUsuario = explode("*", $codUsuario);

    $query = "SELECT 
                    DISTINCT (codmat) 
                FROM 
                    umatalun 
               WHERE
                    mataluno = '$codUsuario[1]'
                    and codcoligada = $codUsuario[0]
                    and perletivo = '$ano/$semestre'";

    $result = mssql_query($query);
    while ($row = mssql_fetch_array($result, MSSQL_ASSOC)) {
        $rows[] = $row['codmat'];
    }
    return $rows;
}

function buscaChamada($matricula, $disciplina) {
    $ano = date("Y");
    $semestre = date("Y") > 7 ? 2 : 1;

    $matricula = explode('*', $matricula);
    $query = "SELECT 
                    cargahoraria,
                    convert(varchar,dataaula,103) dataaula,
                    status 
                FROM 
                    auxorquestra..evasaoFaltasLista 
               WHERE 
                    mataluno = '$matricula[1]' 
                    and codcoligada = $matricula[0]
                    and perletivo = '$ano/$semestre'
                    and codmat = '$disciplina'
            ORDER BY 
                    idaula";



    $result = mssql_query($query);
    while ($row = mssql_fetch_array($result, MSSQL_ASSOC)) {
        $rows[] = $row['status'] . '%' . $row['dataaula'] . '%' . $row['cargahoraria'];
    }
    return $rows;
}

function excluir($matricula) {
    $matricula = explode('*', $matricula);
    $query = "DELETE FROM auxorquestra..evasaoFaltasLista where mataluno = '$matricula[1]' and codcoligada = $matricula[0]";
    mssql_query($query);
}

function insereAluno($dados) {
    $dados = explode("*", $dados);
    $query = "INSERT INTO AUXORQUESTRA..evasaoFaltas (codColigada,matricula,status,data_entrada) VALUES($dados[0],'$dados[1]',1,getdate())";
    mssql_query($query);
}

function dadosAlu($dados) {
    $dados = explode("*", $dados);

    $query = "select cur.codcoligada,alu.CODUSUARIO,alu.matricula,alu.nome,ncur.nome as NOMECUR,alu.email,alu.telaluno,alu.celaluno
            from EALUNOS alu
            inner join ualucurso cur on alu.matricula = cur.MATALUNO and cur.codcoligada = alu.codcoligada
            inner join ucursos ncur on cur.codcur = ncur.codcur and ncur.codcoligada = cur.codcoligada
            where cur.codcoligada= $dados[0]  and alu.codusuario = '$dados[1]' and cur.status = 2";

    $res = mssql_query($query);
    return mssql_fetch_array($res, MSSQL_ASSOC);
}

function geraXml($value) {


    $pro = processos($value['codcoligada'], $value['matricula']);

    $xml = "<form>
                  <codigousuario>" . utf8_encode($value['CODUSUARIO']) . "</codigousuario>
                  <curso>" . utf8_encode($value['NOMECUR']) . "</curso>
                  <processosnateriores>" . utf8_encode($pro) . "</processosnateriores>
                  <nome>" . utf8_encode($value['nome']) . "</nome>
                  <telefone>" . utf8_encode($value['telaluno']) . "</telefone>
                  <celular>" . utf8_encode($value['celaluno']) . "</celular>
                  <email>" . utf8_encode($value['email']) . "</email>
              </form>";

    return $xml;
}

function processos($coligada, $matricula) {

    $query = "select processo from AUXORQUESTRA..processosEvasaoFaltas where idEvasao = (select id from AUXORQUESTRA..evasaoFaltas where codColigada = $coligada and matricula = $matricula)";
    $resultado = mssql_query($query);

    while ($res = mssql_fetch_array($resultado, MSSQL_ASSOC)) {
        $proc[] = $res['processo'];
    }
    return implode(',', $proc);
}

function insereProcessoEvasaoFaltas($processo) {
    $query = "DECLARE @id int
              select @id=MAX(id) from AUXORQUESTRA..evasaoFaltas
              insert into AUXORQUESTRA..processosEvasaoFaltas (idEvasao,processo,data) VALUES (@id,'$processo',getdate())";
    mssql_query($query);
}

function insereDisciplinaEvasaoFaltas($disciplinas) {


    foreach ($disciplinas as $disciplina => $data) {

        $dtP1 = explode('%', $data[0]);
        $dtP1 = $dtP1[1];

        $a = count($data) - 1;
        $dtP2 = explode('%', $data[$a]);
        $dtP2 = $dtP2[1];

        $periodo = $dtP1 . ' a ' . $dtP2;
        $query = " DECLARE @id int
               select @id=MAX(id) from AUXORQUESTRA..processosEvasaoFaltas
               insert into AUXORQUESTRA..disciplinaEvasaoFaltas (idProcessoEvasao,disciplina,periodo) VALUES (@id,'$disciplina','$periodo')";
        mssql_query($query);
    }
}

function gravaProcessoErro($aluno) {
    $query = "INSERT INTO AUXORQUESTRA..erroEvasaoFaltas (aluno,data) VALUES ('$aluno',getdate())";
    mssql_query($query);
}

//function insertTabelaSucesso($aluno, $processo) {
//    $query = "INSERT INTO AUXORQUESTRA..sucessoEvasaoFaltas (processo,aluno,tipo,data) VALUES ('$processo','$aluno',10,getdate())";
//    mssql_query($query);
//}