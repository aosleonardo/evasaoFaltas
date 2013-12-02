<?php

include_once './conexao/conexao.php';
include_once './query/query.php';
include_once './query/webServer.php';

conectar("CORPORERM");

//Informar o percentual
$per = 10;
$percentual = 0;
$breakPoint = 0;

$listaAlunos = buscaAlunos();


if (count($listaAlunos) != 0) {
    foreach ($listaAlunos as $aluno) {
        //Contadores de controle
        $faltas = 0;
        $breakPoint = 0;

        $Listadisciplinas = buscaDisciplinas($aluno);
        //Busca grade de horarios
        foreach ($Listadisciplinas as $disciplina) {
            $listaChamada = buscaChamada($aluno, $disciplina);
            //Verificar 
            foreach ($listaChamada as $chamada) {
                $dados = explode("%", $chamada);
                //Calculando percentula
                $percentual = $dados[2] * $per / 100;

                //Popula disciplina
                $listaHora[$disciplina][] = $chamada;

                if ($dados[0] == "N") {
                    //Incrementar faltas
                    $faltas++;
                    //Verificar se as faltas chegaram ao limite
                    if ($faltas == $percentual) {
                        $breakPoint++;
                    }
                } else {
                    $faltas = 0;
                }
//                echo 'Faltas na disciplina ' . $disciplina . ' =' . $faltas . "<br>";
            }//Fim Grade
            //Apaga disciplinas desnecessarias
            if ($faltas == 0) {
                unset($listaHora[$disciplina]);
            }
        }//FimDisciplina
//        echo 'breakpoint = ' . $breakPoint . '<br>';

        if ($breakPoint == 3) {
            //INSERIR ALUNO NA BASE HISTORICA E GERAR PROCESSO
//            die($aluno);
            insereAluno($aluno);

            $infAluno = dadosAlu($aluno);
            $xml = geraXml($infAluno);

            //Gera SOAP e grava Processo evasao faltas
            $token = retToken();
            echo $processo = registraProcesso($token, $xml);
            if ($processo != 0) {
                //Grava processo
                insereProcessoEvasaoFaltas($processo);
                //Grava lista de alunos
                insereDisciplinaEvasaoFaltas($listaHora);
                //Gravar sucesso
                //insertTabelaSucesso($aluno,$processo);
                
            } else {
                gravaProcessoErro($aluno);
            }
        }//Exclui o cara pra que nao entre novamente na estatistica
        echo $aluno;
        echo '<br>';
        
        excluir($aluno);
    }
} else {
    echo 'Ocorrencias = 0';
}


echo 'fim';